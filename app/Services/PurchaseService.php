<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyPurchaseRequest;
use App\Models\PurchasePayment;
use App\Models\PurchaseStatusLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PurchaseService
{
    /**
     * إنشاء طلب شراء جديد
     */
    public function createPurchaseRequest(Request $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);
        $buyer = $request->user();
        
        // التحقق من أن العقار متاح للبيع
        if ($property->status !== 'active' ) {
            throw new \Exception('هذا العقار غير متاح للبيع حاليًا', 422);
        }
        
        // التحقق من أن المشتري ليس هو البائع
        if ($buyer->id === $property->user_id) {
            throw new \Exception('لا يمكنك شراء عقار تملكه', 422);
        }
        
        // التحقق من عدم وجود طلب شراء سابق للعقار من نفس المشتري
        $existingRequest = PropertyPurchaseRequest::where('property_id', $propertyId)
            ->where('buyer_id', $buyer->id)
            ->whereIn('status', ['pending', 'approved', 'in_progress'])
            ->first();
            
        if ($existingRequest) {
            throw new \Exception('لديك طلب شراء قائم بالفعل لهذا العقار', 422);
        }
        
        // حساب الدفعة المقدمة (مثلاً 10% من سعر العقار)
        $downPaymentPercentage = 10; // يمكن تعديلها حسب سياسة الموقع
        $downPaymentAmount = ($property->price * $downPaymentPercentage) / 100;
        
        DB::beginTransaction();
        
        try {
            // إنشاء طلب الشراء
            $purchaseRequest = new PropertyPurchaseRequest([
                'property_id' => $propertyId,
                'buyer_id' => $buyer->id,
                'seller_id' => $property->user_id,
                'down_payment_amount' => $downPaymentAmount,
                'total_amount' => $property->price,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'buyer_notes' => $request->notes,
            ]);
            
            $purchaseRequest->save();
            
            // معالجة إثبات الدفع إذا تم تقديمه
            if ($request->hasFile('payment_proof')) {
                $path = $request->file('payment_proof')->store('purchase_payments', 'public');
                $purchaseRequest->payment_proof = $path;
                $purchaseRequest->save();
                
                // إنشاء سجل دفعة
                $payment = new PurchasePayment([
                    'purchase_request_id' => $purchaseRequest->id,
                    'amount' => $downPaymentAmount,
                    'payment_method' => $request->payment_method,
                    'payment_date' => now(),
                    'payment_proof' => $path,
                    'status' => 'pending',
                    'notes' => 'دفعة مقدمة',
                ]);
                
                $payment->save();
            }
            
            // إنشاء سجل الحالة
            $statusLog = new PurchaseStatusLog([
                'purchase_request_id' => $purchaseRequest->id,
                'status' => 'pending',
                'notes' => 'تم إنشاء طلب الشراء',
                'changed_by' => $buyer->id,
            ]);
            
            $statusLog->save();
            
            // تحديث حالة العقار إلى معلق
            $property->status = 'pending';
            $property->save();
            
            DB::commit();
            
            return $purchaseRequest;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * تحديث حالة طلب الشراء
     */
    public function updatePurchaseStatus(Request $request, $purchaseId)
    {
        $user = $request->user();
        $purchaseRequest = PropertyPurchaseRequest::findOrFail($purchaseId);
        
        // التحقق من الصلاحيات
        if ($user->role !== 'admin' && $user->id !== $purchaseRequest->seller_id && $user->id !== $purchaseRequest->buyer_id) {
            throw new \Exception('غير مصرح لك بتحديث هذا الطلب', 403);
        }
        
        // التحقق من أن الحالة الجديدة صالحة
        $validStatuses = ['pending', 'approved', 'in_progress', 'completed', 'rejected', 'cancelled'];
        if (!in_array($request->status, $validStatuses)) {
            throw new \Exception('حالة غير صالحة', 422);
        }
        
        // قيود إضافية على تغيير الحالة
        if ($user->role !== 'admin') {
            // المشتري يمكنه فقط إلغاء الطلب إذا كان معلقًا
            if ($user->id === $purchaseRequest->buyer_id && $request->status !== 'cancelled') {
                throw new \Exception('يمكنك فقط إلغاء الطلب', 403);
            }
            
            // البائع يمكنه فقط قبول أو رفض الطلب إذا كان معلقًا
            if ($user->id === $purchaseRequest->seller_id && 
                !in_array($request->status, ['approved', 'rejected']) && 
                $purchaseRequest->status === 'pending') {
                throw new \Exception('يمكنك فقط قبول أو رفض الطلب', 403);
            }
        }
        
        DB::beginTransaction();
        
        try {
            // تحديث حالة الطلب
            $oldStatus = $purchaseRequest->status;
            $purchaseRequest->status = $request->status;
            
            if ($request->has('admin_notes')) {
                $purchaseRequest->admin_notes = $request->admin_notes;
            }
            
            if ($request->status === 'completed') {
                $purchaseRequest->completion_date = now();
            }
            
            $purchaseRequest->save();
            
            // معالجة المستندات القانونية إذا تم تقديمها
            if ($request->hasFile('legal_documents')) {
                $documents = [];
                
                foreach ($request->file('legal_documents') as $file) {
                    $path = $file->store('legal_documents', 'public');
                    $documents[] = $path;
                }
                
                $purchaseRequest->legal_documents = $documents;
                $purchaseRequest->save();
            }
            
            // إنشاء سجل الحالة
            $statusLog = new PurchaseStatusLog([
                'purchase_request_id' => $purchaseRequest->id,
                'status' => $request->status,
                'notes' => $request->notes ?? 'تم تحديث حالة الطلب من ' . $oldStatus . ' إلى ' . $request->status,
                'changed_by' => $user->id,
            ]);
            
            if ($request->hasFile('documents')) {
                $documents = [];
                
                foreach ($request->file('documents') as $file) {
                    $path = $file->store('status_documents', 'public');
                    $documents[] = $path;
                }
                
                $statusLog->documents = $documents;
            }
            
            $statusLog->save();
            
            // تحديث حالة العقار إذا لزم الأمر
            $property = $purchaseRequest->property;
            
            if ($request->status === 'completed') {
                $property->status = 'sold';
            } else if ($request->status === 'cancelled' || $request->status === 'rejected') {
                $property->status = 'active';
            }
            
            $property->save();
            
            DB::commit();
            
            return $purchaseRequest;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * إضافة دفعة جديدة
     */
    public function addPayment(Request $request, $purchaseId)
    {
        $user = $request->user();
        $purchaseRequest = PropertyPurchaseRequest::findOrFail($purchaseId);
        
        // التحقق من أن المستخدم هو المشتري
        if ($user->id !== $purchaseRequest->buyer_id) {
            throw new \Exception('غير مصرح لك بإضافة دفعة لهذا الطلب', 403);
        }
        
        // التحقق من أن الطلب في حالة تسمح بإضافة دفعات
        if (!in_array($purchaseRequest->status, ['approved', 'in_progress'])) {
            throw new \Exception('لا يمكن إضافة دفعة لطلب في هذه الحالة', 422);
        }
        
        DB::beginTransaction();
        
        try {
            // معالجة إثبات الدفع
            $path = null;
            if ($request->hasFile('payment_proof')) {
                $path = $request->file('payment_proof')->store('purchase_payments', 'public');
            }
            
            // إنشاء سجل دفعة
            $payment = new PurchasePayment([
                'purchase_request_id' => $purchaseId,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_date' => now(),
                'payment_proof' => $path,
                'status' => 'pending',
                'notes' => $request->notes ?? 'دفعة إضافية',
            ]);
            
            $payment->save();
            
            // إنشاء سجل الحالة
            $statusLog = new PurchaseStatusLog([
                'purchase_request_id' => $purchaseId,
                'status' => $purchaseRequest->status,
                'notes' => 'تم إضافة دفعة جديدة بقيمة ' . $request->amount,
                'changed_by' => $user->id,
            ]);
            
            $statusLog->save();
            
            DB::commit();
            
            return $payment;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * التحقق من دفعة
     */
    public function verifyPayment(Request $request, $paymentId)
    {
        $user = $request->user();
        
        // التحقق من أن المستخدم هو مشرف
        if ($user->role !== 'admin') {
            throw new \Exception('غير مصرح لك بالتحقق من الدفعات', 403);
        }
        
        $payment = PurchasePayment::findOrFail($paymentId);
        $purchaseRequest = $payment->purchaseRequest;
        
        DB::beginTransaction();
        
        try {
            // تحديث حالة الدفعة
            $payment->status = $request->status; // verified or rejected
            $payment->notes = $request->notes ?? $payment->notes;
            $payment->verified_by = $user->id;
            $payment->save();
            
            // إنشاء سجل الحالة
            $statusLog = new PurchaseStatusLog([
                'purchase_request_id' => $purchaseRequest->id,
                'status' => $purchaseRequest->status,
                'notes' => 'تم ' . ($request->status === 'verified' ? 'التحقق من' : 'رفض') . ' الدفعة بقيمة ' . $payment->amount,
                'changed_by' => $user->id,
            ]);
            
            $statusLog->save();
            
            // تحديث حالة الطلب إذا تم التحقق من جميع الدفعات
            if ($request->status === 'verified') {
                $totalVerifiedPayments = PurchasePayment::where('purchase_request_id', $purchaseRequest->id)
                    ->where('status', 'verified')
                    ->sum('amount');
                
                // إذا تم دفع المبلغ بالكامل، يتم تحديث حالة الطلب إلى مكتمل
                if ($totalVerifiedPayments >= $purchaseRequest->total_amount) {
                    $purchaseRequest->status = 'completed';
                    $purchaseRequest->completion_date = now();
                    $purchaseRequest->save();
                    
                    // تحديث حالة العقار
                    $property = $purchaseRequest->property;
                    $property->status = 'sold';
                    $property->save();
                    
                    // إنشاء سجل الحالة
                    $completionLog = new PurchaseStatusLog([
                        'purchase_request_id' => $purchaseRequest->id,
                        'status' => 'completed',
                        'notes' => 'تم اكتمال الدفع وإتمام عملية الشراء',
                        'changed_by' => $user->id,
                    ]);
                    
                    $completionLog->save();
                }
            }
            
            DB::commit();
            
            return $payment;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * الحصول على طلبات الشراء للمستخدم
     */
    public function getUserPurchaseRequests(Request $request)
    {
        $user = $request->user();
        
        // البحث عن طلبات الشراء حسب دور المستخدم
        if ($user->role === 'admin') {
            // المشرف يرى جميع الطلبات
            return PropertyPurchaseRequest::with(['property', 'buyer', 'seller', 'payments', 'statusLogs'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            // المستخدم العادي يرى طلباته كمشتري أو كبائع
            return PropertyPurchaseRequest::with(['property', 'buyer', 'seller', 'payments', 'statusLogs'])
                ->where(function ($query) use ($user) {
                    $query->where('buyer_id', $user->id)
                        ->orWhere('seller_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }
    }
    
    /**
     * الحصول على تفاصيل طلب شراء
     */
    public function getPurchaseRequestDetails($purchaseId, Request $request)
    {
        $user = $request->user();
        $purchaseRequest = PropertyPurchaseRequest::with(['property', 'buyer', 'seller', 'payments', 'statusLogs.changedByUser'])
            ->findOrFail($purchaseId);
        
        // التحقق من الصلاحيات
        if ($user->role !== 'admin' && $user->id !== $purchaseRequest->buyer_id && $user->id !== $purchaseRequest->seller_id) {
            throw new \Exception('غير مصرح لك بعرض تفاصيل هذا الطلب', 403);
        }
        
        return $purchaseRequest;
    }
}