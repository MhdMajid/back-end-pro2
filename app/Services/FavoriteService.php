<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FavoriteService
{
    /**
     * الحصول على قائمة العقارات المفضلة للمستخدم
     */
    public function getUserFavorites(Request $request)
    {
        $user = $request->user();
        
        // استرجاع العقارات المفضلة مع الصور
        return $user->favoriteProperties()
            ->with('images')
            ->orderBy('favorites.created_at', 'desc')
            ->paginate(10);
    }

    /**
     * إضافة عقار إلى المفضلة
     */
    public function addToFavorites(Request $request, $propertyId)
    {
        $user = $request->user();
        $property = Property::findOrFail($propertyId);
        
        // التحقق مما إذا كان العقار مضافًا بالفعل إلى المفضلة
        $existingFavorite = Favorite::where('user_id', $user->id)
            ->where('property_id', $propertyId)
            ->first();
        
        if ($existingFavorite) {
            throw new \Exception('العقار مضاف بالفعل إلى المفضلة', 422);
        }
        
        // إضافة العقار إلى المفضلة
        $favorite = new Favorite([
            'user_id' => $user->id,
            'property_id' => $propertyId
        ]);
        
        $favorite->save();
        
        return $favorite;
    }

    /**
     * حذف عقار من المفضلة
     */
    public function removeFromFavorites(Request $request, $propertyId)
    {
        $user = $request->user();
        
        // البحث عن العقار في المفضلة
        $favorite = Favorite::where('user_id', $user->id)
            ->where('property_id', $propertyId)
            ->first();
        
        if (!$favorite) {
            throw new \Exception('العقار غير موجود في المفضلة', 404);
        }
        
        // حذف العقار من المفضلة
        $favorite->delete();
        
        return true;
    }

    /**
     * التحقق مما إذا كان العقار مضافًا إلى المفضلة
     */
    public function checkIsFavorite(Request $request, $propertyId)
    {
        $user = $request->user();
        
        // البحث عن العقار في المفضلة
        return Favorite::where('user_id', $user->id)
            ->where('property_id', $propertyId)
            ->exists();
    }
    
    /**
     * إضافة معلومات المفضلة للعقارات
     */
    public function addFavoriteInfoToProperties($properties, $userId)
    {
        if (!$userId) {
            return $properties;
        }
        
        // الحصول على قائمة العقارات المفضلة للمستخدم
        $favoritePropertyIds = Favorite::where('user_id', $userId)
            ->pluck('property_id')
            ->toArray();
        
        // إضافة حقل is_favorite لكل عقار
        $properties->through(function ($property) use ($favoritePropertyIds) {
            $property->is_favorite = in_array($property->id, $favoritePropertyIds);
            return $property;
        });
        
        return $properties;
    }
}