# توثيق واجهة برمجة التطبيقات (API) للوحة التحكم الرئيسية

هذا المستند يوفر توثيقًا شاملاً لنقاط نهاية API المتعلقة بلوحة التحكم الرئيسية في التطبيق. يتضمن معلومات حول كيفية استخدام كل نقطة نهاية، والمتطلبات، والاستجابات المتوقعة.

## جدول المحتويات

1. [المصادقة](#المصادقة)
2. [إحصائيات عامة](#إحصائيات-عامة)
3. [إحصائيات العقارات](#إحصائيات-العقارات)
4. [إحصائيات المزادات](#إحصائيات-المزادات)
5. [إحصائيات المستخدمين](#إحصائيات-المستخدمين)
6. [إحصائيات المدفوعات](#إحصائيات-المدفوعات)

## المصادقة

جميع نقاط النهاية في لوحة التحكم تتطلب مصادقة. يجب إرفاق رمز الوصول (Bearer Token) في ترويسة الطلب.

```
Authorization: Bearer {access_token}
```

## إحصائيات عامة

### الحصول على الإحصائيات العامة

**طلب HTTP:**
```http
GET /api/dashboard/stats
```

**الاستجابة الناجحة:**
```json
{
    "status": "success",
    "data": {
        "total_users": "number",
        "total_properties": "number",
        "active_auctions": "number",
        "total_transactions": "number",
        "total_revenue": "number",
        "recent_activities": [
            {
                "type": "string",
                "description": "string",
                "created_at": "datetime"
            }
        ]
    }
}
```

## إحصائيات العقارات

### الحصول على إحصائيات العقارات

**طلب HTTP:**
```http
GET /api/dashboard/properties/stats
```

**معلمات الاستعلام الاختيارية:**
```
period: string       // الفترة الزمنية (daily, weekly, monthly, yearly)
start_date: date     // تاريخ البداية
end_date: date       // تاريخ النهاية
```

**الاستجابة الناجحة:**
```json
{
    "status": "success",
    "data": {
        "total_properties": "number",
        "properties_by_type": {
            "residential": "number",
            "commercial": "number",
            "land": "number"
        },
        "properties_by_status": {
            "active": "number",
            "sold": "number",
            "pending": "number"
        },
        "recent_properties": [
            {
                "id": "string",
                "title": "string",
                "type": "string",
                "status": "string",
                "price": "number",
                "created_at": "datetime"
            }
        ]
    }
}
```

## إحصائيات المزادات

### الحصول على إحصائيات المزادات

**طلب HTTP:**
```http
GET /api/dashboard/auctions/stats
```

**معلمات الاستعلام الاختيارية:**
```
period: string       // الفترة الزمنية (daily, weekly, monthly, yearly)
status: string       // حالة المزاد (active, completed, cancelled)
```

**الاستجابة الناجحة:**
```json
{
    "status": "success",
    "data": {
        "total_auctions": "number",
        "active_auctions": "number",
        "completed_auctions": "number",
        "total_bids": "number",
        "average_bid_amount": "number",
        "auctions_by_status": {
            "active": "number",
            "completed": "number",
            "cancelled": "number"
        },
        "recent_auctions": [
            {
                "id": "string",
                "property_id": "string",
                "start_price": "number",
                "current_price": "number",
                "total_bids": "number",
                "status": "string",
                "end_date": "datetime"
            }
        ]
    }
}
```

## إحصائيات المستخدمين

### الحصول على إحصائيات المستخدمين

**طلب HTTP:**
```http
GET /api/dashboard/users/stats
```

**معلمات الاستعلام الاختيارية:**
```
role: string         // دور المستخدم (buyer, seller, admin)
status: string       // حالة المستخدم (active, inactive)
```

**الاستجابة الناجحة:**
```json
{
    "status": "success",
    "data": {
        "total_users": "number",
        "active_users": "number",
        "users_by_role": {
            "buyers": "number",
            "sellers": "number",
            "admins": "number"
        },
        "new_users_today": "number",
        "new_users_this_week": "number",
        "recent_users": [
            {
                "id": "string",
                "name": "string",
                "email": "string",
                "role": "string",
                "status": "string",
                "created_at": "datetime"
            }
        ]
    }
}
```

## إحصائيات المدفوعات

### الحصول على إحصائيات المدفوعات

**طلب HTTP:**
```http
GET /api/dashboard/payments/stats
```

**معلمات الاستعلام الاختيارية:**
```
period: string       // الفترة الزمنية (daily, weekly, monthly, yearly)
status: string       // حالة الدفع (completed, pending, failed)
```

**الاستجابة الناجحة:**
```json
{
    "status": "success",
    "data": {
        "total_transactions": "number",
        "total_revenue": "number",
        "transactions_by_status": {
            "completed": "number",
            "pending": "number",
            "failed": "number"
        },
        "revenue_by_period": [
            {
                "period": "string",
                "amount": "number"
            }
        ],
        "recent_transactions": [
            {
                "id": "string",
                "amount": "number",
                "status": "string",
                "payment_method": "string",
                "created_at": "datetime"
            }
        ]
    }
}
```

## رموز الأخطاء

| رمز الخطأ | الوصف |
|-----------|--------|
| 400 | طلب غير صالح - تحقق من المعلمات المرسلة |
| 401 | غير مصرح - تحقق من رمز المصادقة |
| 403 | ممنوع - ليس لديك صلاحية الوصول |
| 404 | لم يتم العثور على البيانات المطلوبة |
| 422 | خطأ في معالجة الطلب - تحقق من صحة البيانات |
| 500 | خطأ في الخادم - حاول مرة أخرى لاحقاً |