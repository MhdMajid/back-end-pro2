<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PropertyService
{
    /**
     * الحصول على قائمة العقارات مع التصفية
     */
    public function getFilteredProperties(Request $request)
    {
        $query = Property::with('images');
        
        // تصفية حسب النوع (بيع/إيجار)
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // تصفية حسب السعر
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // تصفية حسب الموقع
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }
        
        // تصفية حسب المستخدم (العقارات الخاصة بالمستخدم)
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // تصفية العقارات الخاصة بالمستخدم الحالي
        if ($request->has('my_properties') && $request->my_properties && auth('sanctum')->check()) {
            $query->where('user_id', auth('sanctum')->id());
        }
        
        $properties = $query->latest()->paginate(10);
        // التحقق مما إذا كان المستخدم مسجل الدخول
        if (auth('sanctum')->user()) {
            $userId = auth('sanctum')->user()->id;
            
            // الحصول على قائمة العقارات المفضلة للمستخدم
            $favoritePropertyIds = Favorite::where('user_id', $userId)
                ->pluck('property_id')
                ->toArray();
            // إضافة حقل is_favorite لكل عقار
            $properties->through(function ($property) use ($favoritePropertyIds) {
                $property->is_favorite = in_array($property->id, $favoritePropertyIds);
                return $property;
            });
        }
        return $properties;
    }

    /**
     * إنشاء عقار جديد
     */
    public function createProperty(Request $request)
    {
        // إنشاء العقار
        $property = Property::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'type' => $request->type,
            'location' => $request->location,
            'address' => $request->address,
            'floor_number' => $request->floor_number,
            'rooms' => $request->rooms,
            'bathrooms' => $request->bathrooms,
            'area' => $request->area,
            'additional_conditions' => $request->additional_conditions,
        ]);

        // معالجة الصور
        if ($request->hasFile('images')) {
            $this->savePropertyImages($property, $request->file('images'));
        }

        return $property->load('images');
    }

    /**
     * تحديث عقار موجود
     */
    public function updateProperty(Request $request, $id)
    {
        $property = Property::findOrFail($id);
        
        // التحقق من الصلاحية
        if (auth()->id() !== $property->user_id) {
            throw new \Exception('غير مصرح لك بتعديل هذا العقار', 403);
        }

        // تجميع البيانات المراد تحديثها
        $dataToUpdate = $request->only([
            'title', 'description', 'price', 'type', 'location', 
            'address', 'floor_number', 'rooms', 'bathrooms', 
            'area', 'is_available' , 'status'
        ]);
        
        // معالجة خاصة لحقل additional_conditions
        if ($request->has('additional_conditions')) {
            $dataToUpdate['additional_conditions'] = $this->processAdditionalConditions($request->input('additional_conditions'));
        }
        
        // تحديث العقار
        $property->fill($dataToUpdate);
        $property->save();
        
        // حذف الصور المحددة
        if ($request->has('delete_images')) {
            $this->deletePropertyImages($property, $request->delete_images);
        }

        // إضافة صور جديدة
        if ($request->hasFile('new_images')) {
            $this->saveNewPropertyImages($property, $request->file('new_images'));
        }

        return $property->fresh()->load('images');
    }

    /**
     * حذف عقار
     */
    public function deleteProperty($id)
    {
        $property = Property::findOrFail($id);
        
        // التحقق من الصلاحية
        if (auth()->id() !== $property->user_id) {
            throw new \Exception('غير مصرح لك بحذف هذا العقار', 403);
        }

        // حذف الصور من التخزين
        foreach ($property->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $property->delete();
        
        return true;
    }

    /**
     * معالجة الشروط الإضافية
     */
    private function processAdditionalConditions($conditions)
    {
        if (!is_array($conditions)) {
            // محاولة تحويل النص JSON إلى مصفوفة إذا كان ذلك ممكنًا
            if (is_string($conditions) && strpos($conditions, '[') === 0) {
                $conditions = json_decode($conditions, true) ?: [$conditions];
            } else {
                $conditions = [$conditions];
            }
        }
        
        return $conditions;
    }

    /**
     * حفظ صور العقار
     */
    private function savePropertyImages($property, $images)
    {
        foreach ($images as $index => $image) {
            $path = $image->store('properties', 'public');
            
            PropertyImage::create([
                'property_id' => $property->id,
                'image_path' => $path,
                'is_main' => $index === 0 // الصورة الأولى هي الرئيسية
            ]);
        }
    }

    /**
     * حفظ صور جديدة للعقار
     */
    private function saveNewPropertyImages($property, $images)
    {
        $hasMainImage = $property->images()->where('is_main', true)->exists();
        
        foreach ($images as $index => $image) {
            $path = $image->store('properties', 'public');
            
            PropertyImage::create([
                'property_id' => $property->id,
                'image_path' => $path,
                'is_main' => !$hasMainImage && $index === 0 // جعلها رئيسية إذا لم تكن هناك صورة رئيسية
            ]);
        }
    }

    /**
     * حذف صور العقار
     */
    private function deletePropertyImages($property, $imageIds)
    {
        $imagesToDelete = PropertyImage::where('property_id', $property->id)
            ->whereIn('id', $imageIds)
            ->get();

        foreach ($imagesToDelete as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }
    }
}