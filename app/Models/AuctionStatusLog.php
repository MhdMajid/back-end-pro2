<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuctionStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'auction_id',
        'status',
        'notes',
        'changed_by',
        'documents',
    ];

    protected $casts = [
        'documents' => 'array',
    ];

    // العلاقة مع المزاد
    public function auction()
    {
        return $this->belongsTo(Auction::class, 'auction_id');
    }

    // العلاقة مع المستخدم الذي قام بتغيير الحالة
    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}