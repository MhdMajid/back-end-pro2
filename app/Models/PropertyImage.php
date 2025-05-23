<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'image_path',
        'is_main'
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
