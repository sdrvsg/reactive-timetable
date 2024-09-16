<?php

namespace App\Enums;

enum PairType: string
{
    case LECTURE = 'lecture';
    case PRACTICE = 'practice';
    case LAB = 'lab';
    case OTHER = 'other';

    public static function all(): array
    {
        return array_map(fn($value) => $value->value, self::cases());
    }

    public function verbose(): string
    {
        return match ($this) {
            PairType::LECTURE => 'Лекция',
            PairType::PRACTICE => 'Практика',
            PairType::LAB => 'Лаба',
            PairType::OTHER => 'Другое',
        };
    }
}
