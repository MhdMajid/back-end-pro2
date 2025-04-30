<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyPurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'buyer_id',
        'seller_id',
        'down_payment_amount',
        'total_amount',
        'payment_method',
        'status', // pending, approved, in_progress, completed, rejected, cancelled
        'admin_notes',
        'buyer_notes',
        'payment_proof',
        'completion_date',
        'legal_documents',
    ];

    protected $casts = [
        'down_payment_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'completion_date' => 'datetime',
        'legal_documents' => 'array',
    ];

    // العلاقة مع العقار
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // العلاقة مع المشتري
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // العلاقة مع البائع
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // العلاقة مع سجل الدفعات
    public function payments()
    {
        return $this->hasMany(PurchasePayment::class, 'purchase_request_id');
    }

    // العلاقة مع سجل الإجراءات
    public function statusLogs()
    {
        return $this->hasMany(PurchaseStatusLog::class, 'purchase_request_id');
    }
}