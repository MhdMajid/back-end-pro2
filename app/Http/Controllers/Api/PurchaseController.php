<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    protected $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    /**
     * عرض قائمة طلبات الشراء للمستخدم الحالي
     */
    public function index(Request $request)
    {
        try {
            $purchaseRequests = $this->purchaseService->getUserPurchaseRequests($request);
            
            return response()->json([
                'purchase_requests' => $purchaseRequests,
                'message' => 'تم استرجاع طلبات الشراء بنجاح'
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
     * إنشاء طلب شراء جديد
     */
    public function store(Request $request, $propertyId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'payment_method' => 'required|string|in:bank_transfer,cash,credit_card',
                'notes' => 'nullable|string',
                'payment_proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            ]);
            
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            $purchaseRequest = $this->purchaseService->createPurchaseRequest($request, $propertyId);
            
            return response()->json([
                'message' => 'تم إنشاء طلب الشراء بنجاح',
                'purchase_request' => $purchaseRequest
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
     * عرض تفاصيل طلب شراء
     */
    public function show(Request $request, $id)
    {
        try {
            $purchaseRequest = $this->purchaseService->getPurchaseRequestDetails($id, $request);
            
            return response()->json([
                'purchase_request' => $purchaseRequest,
                'message' => 'تم استرجاع تفاصيل طلب الشراء بنجاح'
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
     * تحديث حالة طلب الشراء
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:pending,approved,in_progress,completed,rejected,cancelled',
                'notes' => 'nullable|string',
                'admin_notes' => 'nullable|string',
                'legal_documents.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
                'documents.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            ]);
            
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            $purchaseRequest = $this->purchaseService->updatePurchaseStatus($request, $id);
            
            return response()->json([
                'message' => 'تم تحديث حالة طلب الشراء بنجاح',
                'purchase_request' => $purchaseRequest
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
     * إضافة دفعة جديدة لطلب الشراء
     */
    public function addPayment(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:1',
                'payment_method' => 'required|string|in:bank_transfer,cash,credit_card',
                'notes' => 'nullable|string',
                'payment_proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            ]);
            
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            $payment = $this->purchaseService->addPayment($request, $id);
            
            return response()->json([
                'message' => 'تم إضافة الدفعة بنجاح',
                'payment' => $payment
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
     * التحقق من دفعة (للمشرفين فقط)
     */
    public function verifyPayment(Request $request, $paymentId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:verified,rejected',
                'notes' => 'nullable|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            $payment = $this->purchaseService->verifyPayment($request, $paymentId);
            
            return response()->json([
                'message' => 'تم ' . ($request->status === 'verified' ? 'التحقق من' : 'رفض') . ' الدفعة بنجاح',
                'payment' => $payment
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
     * عرض قائمة المدفوعات للمستخدم الحالي
     */
    public function payments(Request $request)
    {
        try {
            $payments = $this->purchaseService->getAllPayments($request);
            
            return response()->json([
                'payments' => $payments,
                'message' => 'تم استرجاع المدفوعات بنجاح'
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