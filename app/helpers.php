<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

if (!function_exists('site_name')) {
    function site_name(): string
    {
        return (string) Setting::getValue('site_name', 'Outfitt');
    }
}

if (!function_exists('site_description')) {
    function site_description(): string
    {
        return (string) Setting::getValue('site_description', '');
    }
}

if (!function_exists('site_logo')) {
    function site_logo(): string
    {
        $logo = (string) Setting::getValue('site_logo', '');
        return $logo ? Storage::url($logo) : '/logo.webp';
    }
}

if (!function_exists('primary_color')) {
    function primary_color(): string
    {
        return (string) Setting::getValue('primary_color', '#7c3aed');
    }
}

if (!function_exists('primary_color_dark')) {
    function primary_color_dark(): string
    {
        $hex = ltrim(primary_color(), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return '#6d28d9';
        }

        $factor = 0.85;
        $r = (int) round(hexdec(substr($hex, 0, 2)) * $factor);
        $g = (int) round(hexdec(substr($hex, 2, 2)) * $factor);
        $b = (int) round(hexdec(substr($hex, 4, 2)) * $factor);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
