<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    /**
     * الخصائص التي يمكن تعبئتها بشكل جماعي.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'property_id',
    ];

    /**
     * الحصول على المستخدم المالك للمفضلة.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * الحصول على العقار المضاف للمفضلة.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
