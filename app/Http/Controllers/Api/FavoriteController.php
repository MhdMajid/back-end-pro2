<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Property;
use App\Services\FavoriteService;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    protected $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    /**
     * عرض قائمة العقارات المفضلة للمستخدم الحالي.
     */
    public function index(Request $request)
    {
        try {
            $favorites = $this->favoriteService->getUserFavorites($request);
            
            return response()->json([
                'favorites' => $favorites,
                'message' => 'تم استرجاع العقارات المفضلة بنجاح'
            ]);
            
        } catch (\Exception $e) {
            $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ], $statusCode);
        }
    }

    /**
     * إضافة عقار إلى المفضلة.
     */
    public function store(Request $request, $propertyId)
    {
        try {
            $favorite = $this->favoriteService->addToFavorites($request, $propertyId);
            
            return response()->json([
                'message' => 'تمت إضافة العقار إلى المفضلة بنجاح',
                'favorite' => $favorite
            ], 201);
            
        } catch (\Exception $e) {
            $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ], $statusCode);
        }
    }

    /**
     * حذف عقار من المفضلة.
     */
    public function destroy(Request $request, $propertyId)
    {
        try {
            $this->favoriteService->removeFromFavorites($request, $propertyId);
            
            return response()->json([
                'message' => 'تم حذف العقار من المفضلة بنجاح'
            ]);
            
        } catch (\Exception $e) {
            $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ], $statusCode);
        }
    }

    /**
     * التحقق مما إذا كان العقار مضافًا إلى المفضلة.
     */
    public function check(Request $request, $propertyId)
    {
        try {
            $isFavorite = $this->favoriteService->checkIsFavorite($request, $propertyId);
            
            return response()->json([
                'is_favorite' => $isFavorite
            ]);
            
        } catch (\Exception $e) {
            $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ], $statusCode);
        }
    }
}
