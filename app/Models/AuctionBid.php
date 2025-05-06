<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuctionBid extends Model
{
    use HasFactory;

    protected $fillable = [
        'auction_id',
        'user_id',
        'amount',
        'status', // active, outbid, winning, cancelled
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // العلاقة مع المزاد
    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }

    // العلاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // التحقق مما إذا كان هذا العرض هو الأعلى
    public function isHighestBid()
    {
        return $this->auction->highestBid()->id === $this->id;
    }
}