# توثيق واجهة برمجة التطبيقات (API) لنظام الدفع

هذا المستند يوفر توثيقًا شاملاً لنقاط نهاية API المتعلقة بنظام الدفع في التطبيق. يتضمن معلومات حول كيفية استخدام كل نقطة نهاية، والمتطلبات، والاستجابات المتوقعة.

## جدول المحتويات

1. [المصادقة](#المصادقة)
2. [عمليات الدفع](#عمليات-الدفع)
   - [إضافة عملية دفع جديدة](#إضافة-عملية-دفع-جديدة)
   - [عرض تفاصيل عملية دفع محددة](#عرض-تفاصيل-عملية-دفع-محددة)
   - [عرض سجل المدفوعات](#عرض-سجل-المدفوعات)
3. [التحقق من الدفع](#التحقق-من-الدفع)
   - [التحقق من حالة الدفع](#التحقق-من-حالة-الدفع)
   - [تأكيد عملية الدفع](#تأكيد-عملية-الدفع)

## المصادقة

جميع نقاط النهاية في نظام الدفع تتطلب مصادقة. يجب إرفاق رمز الوصول (Bearer Token) في ترويسة الطلب.

```
Authorization: Bearer {access_token}
```

## عمليات الدفع

### إضافة عملية دفع جديدة

**طلب HTTP:**
```http
POST /api/payments/create
```

**المعلمات المطلوبة:**
```json
{
    "amount": "number",       // المبلغ المراد دفعه
    "currency": "string",    // عملة الدفع (مثال: USD, SAR)
    "payment_method": "string", // طريقة الدفع
    "description": "string"   // وصف عملية الدفع
}
```

**الاستجابة الناجحة:**
```json
{
    "status": "success",
    "data": {
        "payment_id": "string",
        "amount": "number",
        "currency": "string",
        "status": "pending",
        "created_at": "datetime"
    }
}
```

### عرض تفاصيل عملية دفع محددة

**طلب HTTP:**
```http
GET /api/payments/{payment_id}
```

**الاستجابة الناجحة:**
```json
{
    "status": "success",
    "data": {
        "payment_id": "string",
        "amount": "number",
        "currency": "string",
        "status": "string",
        "payment_method": "string",
        "description": "string",
        "created_at": "datetime",
        "updated_at": "datetime"
    }
}
```

### عرض سجل المدفوعات

**طلب HTTP:**
```http
GET /api/payments/history
```

**معلمات الاستعلام الاختيارية:**
```
page: number          // رقم الصفحة
per_page: number      // عدد العناصر في الصفحة
status: string        // حالة الدفع (pending, completed, failed)
start_date: date      // تاريخ البداية
end_date: date        // تاريخ النهاية
```

**الاستجابة الناجحة:**
```json
{
    "status": "success",
    "data": {
        "payments": [
            {
                "payment_id": "string",
                "amount": "number",
                "currency": "string",
                "status": "string",
                "created_at": "datetime"
            }
        ],
        "pagination": {
            "current_page": "number",
            "total_pages": "number",
            "total_items": "number"
        }
    }
}
```

## التحقق من الدفع

### التحقق من حالة الدفع

**طلب HTTP:**
```http
GET /api/payments/{payment_id}/status
```

**الاستجابة الناجحة:**
```json
{
    "status": "success",
    "data": {
        "payment_id": "string",
        "status": "string",
        "last_checked_at": "datetime"
    }
}
```

### تأكيد عملية الدفع

**طلب HTTP:**
```http
POST /api/payments/{payment_id}/confirm
```

**المعلمات المطلوبة:**
```json
{
    "confirmation_code": "string"  // رمز التأكيد (إذا كان مطلوباً)
}
```

**الاستجابة الناجحة:**
```json
{
    "status": "success",
    "data": {
        "payment_id": "string",
        "status": "completed",
        "confirmed_at": "datetime"
    }
}
```

## رموز الأخطاء

| رمز الخطأ | الوصف |
|-----------|--------|
| 400 | طلب غير صالح - تحقق من المعلمات المرسلة |
| 401 | غير مصرح - تحقق من رمز المصادقة |
| 404 | لم يتم العثور على عملية الدفع |
| 422 | خطأ في معالجة الطلب - تحقق من صحة البيانات |
| 500 | خطأ في الخادم - حاول مرة أخرى لاحقاً |