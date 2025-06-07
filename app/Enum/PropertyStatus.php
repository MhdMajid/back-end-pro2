<?php

namespace App\Enum;

enum PropertyStatus : string
{
    
    case ACTIVE = 'active'; //  نشط
    case INACTIVE = 'inactive'; // غير نشط
    case PENDING = 'pending'; // انتظار
    case SOLD = 'sold'; // مباع
    case RENTED = 'rented'; // مستأجر

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

}


