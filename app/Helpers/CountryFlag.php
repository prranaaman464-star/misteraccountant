<?php

namespace App\Helpers;

class CountryFlag
{
    private const FLAGS = [
        'India' => '🇮🇳',
        'USA' => '🇺🇸',
        'UK' => '🇬🇧',
        'Canada' => '🇨🇦',
        'Australia' => '🇦🇺',
        'Germany' => '🇩🇪',
        'France' => '🇫🇷',
        'Japan' => '🇯🇵',
        'China' => '🇨🇳',
        'Singapore' => '🇸🇬',
        'UAE' => '🇦🇪',
        'Argentina' => '🇦🇷',
        'Brazil' => '🇧🇷',
        'Mexico' => '🇲🇽',
    ];

    public static function get(?string $country): string
    {
        if (! $country) {
            return '🌐';
        }

        return self::FLAGS[$country] ?? '🌐';
    }
}
