<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'type',
        'location',
        'address',
        'floor_number',
        'rooms',
        'status',
        'is_available_by_admin',
        'bathrooms',
        'area',
        'additional_conditions',
        'is_available'
    ];

    protected $casts = [
        'additional_conditions' => 'array',
        'is_available' => 'boolean',
        'price' => 'float',
        'area' => 'float',
    ];

    // علاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة مع صور العقار
    public function images()
    {
        return $this->hasMany(PropertyImage::class);
    }

    // الحصول على الصورة الرئيسية
    public function mainImage()
    {
        return $this->hasOne(PropertyImage::class)->where('is_main', true);
    }
}
