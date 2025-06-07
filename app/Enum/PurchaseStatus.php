<?php

namespace App\Enum;

enum PurchaseStatus : string
{
    case PENDING  = 'pending';
    case APPROVED = 'approved';
    case InProgress = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case ACTIVE = 'active';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canBePurchased(): bool
    {
        return $this === self::ACTIVE;
    }

    public function getMessage(): string
    {
        return match($this) {
            self::ACTIVE => 'العقار متاح للبيع',
            self::PENDING => 'العقار قيد الانتظار',
            self::APPROVED => 'تمت الموافقة على العقار',
            self::InProgress => 'العقار قيد التنفيذ',
            self::COMPLETED => 'تم اكتمال عملية البيع',
            self::CANCELLED => 'تم إلغاء عملية البيع',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}


