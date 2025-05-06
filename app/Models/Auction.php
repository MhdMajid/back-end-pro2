<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'title',
        'description',
        'start_price',
        'min_increment',
        'start_date',
        'end_date',
        'status', // draft, active, ended, cancelled
        'winner_id',
        'winning_bid_amount',
        'admin_notes',
    ];

    protected $casts = [
        'start_price' => 'decimal:2',
        'min_increment' => 'decimal:2',
        'winning_bid_amount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // العلاقة مع العقار
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // العلاقة مع الفائز بالمزاد
    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    // العلاقة مع العروض المقدمة
    public function bids()
    {
        return $this->hasMany(AuctionBid::class);
    }

    // العلاقة مع سجلات حالة المزاد
    public function statusLogs()
    {
        return $this->hasMany(AuctionStatusLog::class);
    }

    // الحصول على أعلى عرض
    public function highestBid()
    {
        return $this->bids()->orderBy('amount', 'desc')->first();
    }

    // التحقق من انتهاء المزاد
    public function isEnded()
    {
        return $this->status === 'ended' || now()->gt($this->end_date);
    }

    // التحقق من نشاط المزاد
    public function isActive()
    {
        return $this->status === 'active' && now()->between($this->start_date, $this->end_date);
    }
}