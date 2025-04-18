<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    protected $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    /**
     * عرض قائمة العقارات
     */
    public function index(Request $request)
    {
        $properties = $this->propertyService->getFilteredProperties($request);
        return response()->json($properties);
    }

    /**
     * تخزين عقار جديد
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:sale,rent',
            'location' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'floor_number' => 'nullable|integer|min:0',
            'rooms' => 'required|integer|min:1',
            'bathrooms' => 'required|integer|min:1',
            'area' => 'required|numeric|min:1',
            'additional_conditions' => 'nullable|array',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $property = $this->propertyService->createProperty($request);

        return response()->json([
            'message' => 'تم إنشاء العقار بنجاح',
            'property' => $property
        ], 201);
    }

    /**
     * عرض عقار محدد
     */
    public function show($id)
    {
        $property = Property::with('images', 'user')->findOrFail($id);
        return response()->json($property);
    }

    /**
     * تحديث عقار
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'price' => 'sometimes|required|numeric|min:0',
                'type' => 'sometimes|required|in:sale,rent',
                'location' => 'sometimes|required|string|max:255',
                'address' => 'sometimes|required|string|max:255',
                'floor_number' => 'nullable|integer|min:0',
                'rooms' => 'sometimes|required|integer|min:1',
                'bathrooms' => 'sometimes|required|integer|min:1',
                'area' => 'sometimes|required|numeric|min:1',
                'additional_conditions' => 'nullable',
                'new_images' => 'nullable|array',
                'new_images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
                'delete_images' => 'nullable|array',
                'delete_images.*' => 'exists:property_images,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $property = $this->propertyService->updateProperty($request, $id);

            return response()->json([
                'message' => 'تم تحديث العقار بنجاح',
                'property' => $property
            ]);
            
        } catch (\Exception $e) {
            // التحقق من رمز الخطأ للاستجابة المناسبة
            $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ], $statusCode);
        }
    }

    /**
     * حذف عقار
     */
    public function destroy($id)
    {
        try {
            $this->propertyService->deleteProperty($id);
            return response()->json(['message' => 'تم حذف العقار بنجاح']);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ], $statusCode);
        }
    }
}
