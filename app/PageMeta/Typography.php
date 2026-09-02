<?php

namespace App\PageMeta;

final class Typography
{
    /**
     * @return array<string, string>
     */
    public static function sizes(): array
    {
        return [
            '12px' => '12px',
            '14px' => '14px',
            '16px' => '16px',
            '18px' => '18px',
            '20px' => '20px',
            '22px' => '22px',
            '24px' => '24px',
            '28px' => '28px',
            '32px' => '32px',
            '36px' => '36px',
            '42px' => '42px',
            '48px' => '48px',
            '56px' => '56px',
            '64px' => '64px',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function alignments(): array
    {
        return [
            'left' => 'Left',
            'center' => 'Center',
            'right' => 'Right',
        ];
    }

    public static function size(?string $value): ?string
    {
        $value = trim((string) $value);

        return array_key_exists($value, self::sizes()) ? $value : null;
    }

    public static function align(?string $value): ?string
    {
        $value = trim((string) $value);

        return array_key_exists($value, self::alignments()) ? $value : null;
    }
}
