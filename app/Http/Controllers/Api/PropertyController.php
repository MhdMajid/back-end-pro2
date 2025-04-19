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
            if($request->user()->id !== $property->user_id && $request->user()->role !== 'admin') {
                return response()->json([
                    'message' => 'غير مصرح لك بتعديل هذا العقار'
                ], 403);
            }
            
            
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
    public function destroy(Request $request, $id)
    {
        try {
            // Get the property first
            $property = Property::findOrFail($id);
            
            // Check permissions before deleting
            if($request->user()->id !== $property->user_id && $request->user()->role !== 'admin') {
                return response()->json([
                    'message' => 'غير مصرح لك بحذف هذا العقار'
                ], 403);
            }
            
            // Delete the property if authorized
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

    /**
     * تعديل حالة العقار (تفعيل/تعطيل)
     */
    public function updateAvailability(Request $request, $id)
    {
        try {
            $property = Property::findOrFail($id);
            
            // التحقق من الصلاحيات: يجب أن يكون المستخدم مالك العقار أو مشرف
            if($request->user()->id !== $property->user_id && $request->user()->role !== 'admin') {
                return response()->json([
                    'message' => 'غير مصرح لك بتعديل حالة هذا العقار'
                ], 403);
            }
            
            // $validator = Validator::make($request->all(), [
            //     'is_active' => 'required|boolean',
            // ]);
            
            // if ($validator->fails()) {
            //     return response()->json(['errors' => $validator->errors()], 422);
            // }
            
            // تحديث حالة العقار
            if ($request->user()->role === 'admin') {
                $property->is_available_by_admin = !$property->is_available_by_admin;
            } else {
                $property->is_available = !$property->is_available;
            }
            
            $property->save();
            
            return response()->json([
                'message' => 'تم تحديث حالة العقار بنجاح',
                'property' => $property
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
     * تعديل حالة توفر العقار
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $property = Property::findOrFail($id);
            
            // التحقق من الصلاحيات: يجب أن يكون المستخدم مالك العقار أو مشرف
            if($request->user()->id !== $property->user_id && $request->user()->role !== 'admin') {
                return response()->json([
                    'message' => 'غير مصرح لك بتعديل حالة توفر هذا العقار'
                ], 403);
            }
            
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:active,pending,sold,rented',
            ]);
            
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            // تحديث حالة توفر العقار
            $property->status = $request->status;
            $property->save();
            
            return response()->json([
                'message' => 'تم تحديث حالة توفر العقار بنجاح',
                'property' => $property
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
     * استرجاع العقارات الخاصة بالمستخدم الحالي
     */
    public function getUserProperties(Request $request)
    {
        try {
            // الحصول على المستخدم الحالي
            $user = $request->user();
            
            // استرجاع العقارات الخاصة بالمستخدم مع الصور
            $properties = Property::with('images')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            
            return response()->json([
                'properties' => $properties,
                'message' => 'تم استرجاع العقارات بنجاح'
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
