<?php

namespace App\Helpers;

use App\Models\GeneralSettings;

class ThemeHelper
{
    public const DEFAULT_PRIMARY = '#308e87';

    public const DEFAULT_HOVER = '#256e69';

    public const DEFAULT_LIGHT = '#eaf4f3';

    /**
     * Cache theme colors for the request lifecycle.
     */
    public static function getColors(): array
    {
        static $colors = null;
        if ($colors !== null) {
            return $colors;
        }

        $settings = GeneralSettings::where('category', 'general')
            ->whereIn('key_name', [
                'primary_color',
                'primary_hover_color',
                'link_color',
                'toast_success_color',
                'primary_light_color',
            ])
            ->pluck('value', 'key_name')
            ->toArray();

        $primary = self::normalizeHex($settings['primary_color'] ?? null) ?? self::DEFAULT_PRIMARY;
        $hover = self::normalizeHex($settings['primary_hover_color'] ?? null) ?? self::DEFAULT_HOVER;
        $light = self::normalizeHex($settings['primary_light_color'] ?? null) ?? self::DEFAULT_LIGHT;
        $link = self::normalizeHex($settings['link_color'] ?? null) ?? $primary;
        $toast = self::normalizeHex($settings['toast_success_color'] ?? null) ?? $primary;

        $colors = [
            'primary' => $primary,
            'hover' => $hover,
            'light' => $light,
            'link' => $link,
            'toast_success' => $toast,
            'rgb' => self::hexToRgb($primary),
        ];

        return $colors;
    }

    public static function getPrimaryColor(): string
    {
        return self::getColors()['primary'];
    }

    public static function getHoverColor(): string
    {
        return self::getColors()['hover'];
    }

    public static function getLightColor(): string
    {
        return self::getColors()['light'];
    }

    public static function getLinkColor(): string
    {
        return self::getColors()['link'];
    }

    public static function getToastSuccessColor(): string
    {
        return self::getColors()['toast_success'];
    }

    /**
     * Return "r, g, b" string for use in rgba().
     */
    public static function getRgb(?string $hex = null): string
    {
        if ($hex === null) {
            return self::getColors()['rgb'];
        }

        $normalized = self::normalizeHex($hex) ?? self::DEFAULT_PRIMARY;

        return self::hexToRgb($normalized);
    }

    /**
     * Primary color without leading # (e.g. for ui-avatars URLs).
     */
    public static function getPrimaryColorWithoutHash(): string
    {
        return ltrim(self::getPrimaryColor(), '#');
    }

    /**
     * Darken a hex color by a percentage (0–100).
     */
    public static function darken(string $hex, float $percent = 15): string
    {
        $hex = self::normalizeHex($hex) ?? self::DEFAULT_PRIMARY;
        $rgb = array_map('intval', explode(', ', self::hexToRgb($hex)));

        $factor = max(0, min(1, 1 - ($percent / 100)));

        $r = (int) round($rgb[0] * $factor);
        $g = (int) round($rgb[1] * $factor);
        $b = (int) round($rgb[2] * $factor);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    public static function normalizeHex(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim($value);
        if (! str_starts_with($value, '#')) {
            $value = '#'.$value;
        }

        if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
            return null;
        }

        return strtolower($value);
    }

    protected static function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "{$r}, {$g}, {$b}";
    }
}
