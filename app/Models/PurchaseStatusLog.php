<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'status',
        'notes',
        'changed_by',
        'documents',
    ];

    protected $casts = [
        'documents' => 'array',
    ];

    // العلاقة مع طلب الشراء
    public function purchaseRequest()
    {
        return $this->belongsTo(PropertyPurchaseRequest::class, 'purchase_request_id');
    }

    // العلاقة مع المستخدم الذي قام بتغيير الحالة
    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}