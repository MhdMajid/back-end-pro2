# توثيق واجهة برمجة التطبيقات (API) لنظام المزادات

هذا المستند يوفر توثيقًا شاملاً لنقاط نهاية API المتعلقة بنظام المزادات في التطبيق. يتضمن معلومات حول كيفية استخدام كل نقطة نهاية، والمتطلبات، والاستجابات المتوقعة.

## جدول المحتويات

1. [المصادقة](#المصادقة)
2. [المزادات العامة](#المزادات-العامة)
   - [عرض المزادات النشطة](#عرض-المزادات-النشطة)
   - [عرض تفاصيل مزاد محدد](#عرض-تفاصيل-مزاد-محدد)
3. [مزادات المستخدم](#مزادات-المستخدم)
   - [عرض مزادات المستخدم الحالي](#عرض-مزادات-المستخدم-الحالي)
4. [إدارة المزادات](#إدارة-المزادات)
   - [إنشاء مزاد جديد](#إنشاء-مزاد-جديد)
   - [تحديث حالة المزاد](#تحديث-حالة-المزاد)
5. [العروض](#العروض)
   - [تقديم عرض في المزاد](#تقديم-عرض-في-المزاد)

## المصادقة

جميع نقاط النهاية التي تتطلب مصادقة تستخدم Laravel Sanctum. يجب إرسال رمز المصادقة (token) في ترويسة الطلب كالتالي:

```
Authorization: Bearer {your-token}
```

يمكن الحصول على الرمز من خلال تسجيل الدخول باستخدام نقطة النهاية `/api/login`.

## المزادات العامة

### عرض المزادات النشطة

يعرض قائمة بجميع المزادات النشطة حاليًا.

- **URL**: `/api/auctions`
- **Method**: `GET`
- **المصادقة**: غير مطلوبة
- **المعلمات**: لا يوجد

#### استجابة ناجحة (200 OK)

```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "property_id": 5,
        "title": "فيلا فاخرة في شمال الرياض",
        "description": "فيلا حديثة بتصميم عصري وإطلالة رائعة",
        "start_price": "500000.00",
        "min_increment": "5000.00",
        "start_date": "2023-06-01T00:00:00.000000Z",
        "end_date": "2023-06-30T00:00:00.000000Z",
        "status": "active",
        "winner_id": null,
        "winning_bid_amount": null,
        "admin_notes": null,
        "created_at": "2023-05-15T10:30:00.000000Z",
        "updated_at": "2023-05-15T10:30:00.000000Z",
        "property": {
          "id": 5,
          "title": "فيلا فاخرة في شمال الرياض",
          "description": "فيلا حديثة بتصميم عصري وإطلالة رائعة",
          "status": "in_auction",
          "images": [
            {
              "id": 15,
              "property_id": 5,
              "url": "properties/villa1.jpg",
              "is_primary": true
            }
          ]
        },
        "bids": [
          {
            "id": 3,
            "auction_id": 1,
            "user_id": 7,
            "amount": "520000.00",
            "status": "active",
            "notes": null,
            "created_at": "2023-06-05T14:20:00.000000Z",
            "updated_at": "2023-06-05T14:20:00.000000Z"
          }
        ]
      }
    ],
    "first_page_url": "http://example.com/api/auctions?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://example.com/api/auctions?page=1",
    "links": [...],
    "next_page_url": null,
    "path": "http://example.com/api/auctions",
    "per_page": 10,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

### عرض تفاصيل مزاد محدد

يعرض تفاصيل مزاد محدد بناءً على معرفه.

- **URL**: `/api/auctions/{id}`
- **Method**: `GET`
- **المصادقة**: غير مطلوبة (ولكن إذا كان المستخدم مسجل الدخول، سيتم إضافة معلومات إضافية عن عروضه)
- **المعلمات المسار**: 
  - `id`: معرف المزاد

#### استجابة ناجحة (200 OK)

```json
{
  "status": "success",
  "data": {
    "id": 1,
    "property_id": 5,
    "title": "فيلا فاخرة في شمال الرياض",
    "description": "فيلا حديثة بتصميم عصري وإطلالة رائعة",
    "start_price": "500000.00",
    "min_increment": "5000.00",
    "start_date": "2023-06-01T00:00:00.000000Z",
    "end_date": "2023-06-30T00:00:00.000000Z",
    "status": "active",
    "winner_id": null,
    "winning_bid_amount": null,
    "admin_notes": null,
    "created_at": "2023-05-15T10:30:00.000000Z",
    "updated_at": "2023-05-15T10:30:00.000000Z",
    "property": {
      "id": 5,
      "title": "فيلا فاخرة في شمال الرياض",
      "description": "فيلا حديثة بتصميم عصري وإطلالة رائعة",
      "status": "in_auction",
      "images": [...]
    },
    "bids": [...],
    "status_logs": [...],
    "highest_bid": {
      "id": 3,
      "auction_id": 1,
      "user_id": 7,
      "amount": "520000.00",
      "status": "active",
      "notes": null,
      "created_at": "2023-06-05T14:20:00.000000Z",
      "updated_at": "2023-06-05T14:20:00.000000Z",
      "user": {
        "id": 7,
        "name": "أحمد محمد"
      }
    },
    "total_bids": 3,
    "is_ended": false,
    "user_bids": [...],
    "user_highest_bid": {...}
  }
}
```

## مزادات المستخدم

### عرض مزادات المستخدم الحالي

يعرض قائمة بالمزادات المرتبطة بالمستخدم الحالي (كمالك أو كمشارك).

- **URL**: `/api/user/auctions`
- **Method**: `GET`
- **المصادقة**: مطلوبة
- **المعلمات**: لا يوجد

#### استجابة ناجحة (200 OK)

```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "property_id": 5,
        "title": "فيلا فاخرة في شمال الرياض",
        "description": "فيلا حديثة بتصميم عصري وإطلالة رائعة",
        "start_price": "500000.00",
        "min_increment": "5000.00",
        "start_date": "2023-06-01T00:00:00.000000Z",
        "end_date": "2023-06-30T00:00:00.000000Z",
        "status": "active",
        "winner_id": null,
        "winning_bid_amount": null,
        "admin_notes": null,
        "created_at": "2023-05-15T10:30:00.000000Z",
        "updated_at": "2023-05-15T10:30:00.000000Z",
        "property": {...},
        "bids": [...]
      }
    ],
    "first_page_url": "http://example.com/api/user/auctions?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://example.com/api/user/auctions?page=1",
    "links": [...],
    "next_page_url": null,
    "path": "http://example.com/api/user/auctions",
    "per_page": 10,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

## إدارة المزادات

### إنشاء مزاد جديد

ينشئ مزادًا جديدًا لعقار محدد.

- **URL**: `/api/auctions/{propertyId}`
- **Method**: `POST`
- **المصادقة**: مطلوبة
- **المعلمات المسار**: 
  - `propertyId`: معرف العقار
- **معلمات الطلب**:
  - `title`: عنوان المزاد (مطلوب، نص، الحد الأقصى 255 حرف)
  - `description`: وصف المزاد (مطلوب، نص)
  - `start_price`: سعر البداية (مطلوب، رقم، الحد الأدنى 0)
  - `min_increment`: الحد الأدنى للزيادة في العروض (مطلوب، رقم، الحد الأدنى 1)
  - `start_date`: تاريخ بدء المزاد (مطلوب، تاريخ، يجب أن يكون اليوم أو بعده)
  - `end_date`: تاريخ انتهاء المزاد (مطلوب، تاريخ، يجب أن يكون بعد تاريخ البدء)
  - `admin_notes`: ملاحظات المشرف (اختياري، نص)

#### استجابة ناجحة (201 Created)

```json
{
  "status": "success",
  "data": {
    "id": 1,
    "property_id": 5,
    "title": "فيلا فاخرة في شمال الرياض",
    "description": "فيلا حديثة بتصميم عصري وإطلالة رائعة",
    "start_price": "500000.00",
    "min_increment": "5000.00",
    "start_date": "2023-06-01T00:00:00.000000Z",
    "end_date": "2023-06-30T00:00:00.000000Z",
    "status": "draft",
    "winner_id": null,
    "winning_bid_amount": null,
    "admin_notes": null,
    "created_at": "2023-05-15T10:30:00.000000Z",
    "updated_at": "2023-05-15T10:30:00.000000Z"
  }
}
```

#### استجابة الخطأ (422 Unprocessable Entity)

```json
{
  "status": "error",
  "errors": {
    "title": [
      "حقل العنوان مطلوب."
    ],
    "start_price": [
      "حقل سعر البداية يجب أن يكون رقمًا."
    ]
  }
}
```

#### استجابة الخطأ (403 Forbidden)

```json
{
  "status": "error",
  "message": "غير مصرح لك بإنشاء مزاد لهذا العقار"
}
```

### تحديث حالة المزاد

يحدث حالة مزاد محدد.

- **URL**: `/api/auctions/status/{id}`
- **Method**: `POST`
- **المصادقة**: مطلوبة
- **المعلمات المسار**: 
  - `id`: معرف المزاد
- **معلمات الطلب**:
  - `status`: الحالة الجديدة (مطلوب، نص، يجب أن تكون واحدة من: draft, active, ended, cancelled)
  - `notes`: ملاحظات حول تغيير الحالة (اختياري، نص)
  - `admin_notes`: ملاحظات المشرف (اختياري، نص)

#### استجابة ناجحة (200 OK)

```json
{
  "status": "success",
  "data": {
    "id": 1,
    "property_id": 5,
    "title": "فيلا فاخرة في شمال الرياض",
    "description": "فيلا حديثة بتصميم عصري وإطلالة رائعة",
    "start_price": "500000.00",
    "min_increment": "5000.00",
    "start_date": "2023-06-01T00:00:00.000000Z",
    "end_date": "2023-06-30T00:00:00.000000Z",
    "status": "active",
    "winner_id": null,
    "winning_bid_amount": null,
    "admin_notes": null,
    "created_at": "2023-05-15T10:30:00.000000Z",
    "updated_at": "2023-05-15T10:30:00.000000Z"
  }
}
```

#### استجابة الخطأ (422 Unprocessable Entity)

```json
{
  "status": "error",
  "errors": {
    "status": [
      "حقل الحالة يجب أن يكون واحدًا من: draft, active, ended, cancelled."
    ]
  }
}
```

#### استجابة الخطأ (403 Forbidden)

```json
{
  "status": "error",
  "message": "غير مصرح لك بتحديث هذا المزاد"
}
```

## العروض

### تقديم عرض في المزاد

يقدم عرضًا جديدًا في مزاد محدد.

- **URL**: `/api/auctions/{id}/bids`
- **Method**: `POST`
- **المصادقة**: مطلوبة
- **المعلمات المسار**: 
  - `id`: معرف المزاد
- **معلمات الطلب**:
  - `amount`: قيمة العرض (مطلوب، رقم، يجب أن يكون أكبر من أعلى عرض حالي + الحد الأدنى للزيادة)
  - `notes`: ملاحظات حول العرض (اختياري، نص)

#### استجابة ناجحة (201 Created)

```json
{
  "status": "success",
  "data": {
    "id": 4,
    "auction_id": 1,
    "user_id": 8,
    "amount": "525000.00",
    "status": "active",
    "notes": null,
    "created_at": "2023-06-06T09:15:00.000000Z",
    "updated_at": "2023-06-06T09:15:00.000000Z"
  }
}
```

#### استجابة الخطأ (422 Unprocessable Entity)

```json
{
  "status": "error",
  "message": "يجب أن يكون العرض أكبر من 525000"
}
```

#### استجابة الخطأ (422 Unprocessable Entity)

```json
{
  "status": "error",
  "message": "المزاد غير نشط حاليًا"
}
```

#### استجابة الخطأ (422 Unprocessable Entity)

```json
{
  "status": "error",
  "message": "لا يمكنك تقديم عرض على مزاد لعقار تملكه"
}
```

## حالات المزاد

- **draft**: مسودة، المزاد تم إنشاؤه ولكن لم يتم تنشيطه بعد
- **active**: نشط، المزاد متاح للمشاركة وتقديم العروض
- **ended**: منتهي، المزاد انتهى وتم تحديد الفائز (إن وجد)
- **cancelled**: ملغي، تم إلغاء المزاد

## حالات العروض

- **active**: نشط، العرض هو الأعلى حاليًا
- **outbid**: تم تجاوزه، تم تقديم عرض أعلى
- **winning**: فائز، هذا العرض هو الفائز في المزاد المنتهي
- **cancelled**: ملغي، تم إلغاء العرض

## ملاحظات هامة

1. لا يمكن للمستخدم تقديم عرض على مزاد لعقار يملكه.
2. يجب أن تكون قيمة العرض أكبر من أعلى عرض حالي + الحد الأدنى للزيادة.
3. عند تنشيط المزاد، يتم تحديث حالة العقار إلى "in_auction".
4. عند إنهاء المزاد، يتم تحديث حالة العقار إلى "sold" إذا كان هناك فائز، أو "active" إذا لم يكن هناك فائز.
5. عند إلغاء المزاد، يتم تحديث حالة العقار إلى "active".
6. يمكن فقط لمالك العقار أو المشرف تحديث حالة المزاد.
7. يتم تسجيل جميع تغييرات حالة المزاد في سجل الحالة.