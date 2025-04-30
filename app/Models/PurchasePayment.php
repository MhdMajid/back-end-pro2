<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'amount',
        'payment_method',
        'payment_date',
        'payment_proof',
        'status', // pending, verified, rejected
        'notes',
        'verified_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    // العلاقة مع طلب الشراء
    public function purchaseRequest()
    {
        return $this->belongsTo(PropertyPurchaseRequest::class, 'purchase_request_id');
    }

    // العلاقة مع المشرف الذي تحقق من الدفعة
    public function verifiedByAdmin()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}