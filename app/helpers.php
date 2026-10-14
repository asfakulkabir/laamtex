<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

if (!function_exists('site_name')) {
    function site_name(): string
    {
        return (string) Setting::getValue('site_name', 'laamtex');
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

if (!function_exists('contact_phone')) {
    function contact_phone(): string
    {
        return (string) Setting::getValue('contact_phone', '');
    }
}

if (!function_exists('contact_phone_digits')) {
    function contact_phone_digits(): string
    {
        return (string) preg_replace('/[^0-9]/', '', contact_phone());
    }
}

if (!function_exists('whatsapp_number')) {
    function whatsapp_number(): string
    {
        return (string) Setting::getValue('whatsapp_number', '');
    }
}

if (!function_exists('whatsapp_number_digits')) {
    function whatsapp_number_digits(): string
    {
        return (string) preg_replace('/[^0-9]/', '', whatsapp_number());
    }
}

if (!function_exists('whatsapp_link')) {
    function whatsapp_link(): string
    {
        $digits = whatsapp_number_digits();

        return $digits !== '' ? 'https://wa.me/' . $digits : '#';
    }
}

if (!function_exists('mask_address')) {
    /**
     * Mask an address for public order tracking: only the tail stays readable.
     * At most 5 characters are revealed, and never more than half of the
     * address, so a short address cannot leak in full.
     */
    function mask_address(?string $address, int $visible = 5): string
    {
        $address = trim((string) $address);

        if ($address === '') {
            return '';
        }

        $length = mb_strlen($address);
        $keep = min($visible, max(1, intdiv($length, 2)));

        if ($length <= 1) {
            return $address;
        }

        return str_repeat('*', $length - $keep) . mb_substr($address, -$keep);
    }
}

if (!function_exists('normalize_bd_phone')) {
    /**
     * Turn any Bangladeshi phone format into the last 10 digits (the
     * subscriber number), so 01712345678, +8801712345678 and 8801712345678
     * all resolve to the same key for lookups.
     */
    function normalize_bd_phone(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        return strlen($digits) > 10 ? substr($digits, -10) : $digits;
    }
}

if (!function_exists('contact_email')) {
    function contact_email(): string
    {
        return (string) Setting::getValue('contact_email', 'laamtexoffice@gmail.com');
    }
}

if (!function_exists('office_address')) {
    function office_address(): string
    {
        return (string) Setting::getValue('office_address', 'House 12, Road 5, Dhanmondi, Dhaka 1205, Bangladesh');
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

if (!function_exists('accent_color')) {
    function accent_color(): string
    {
        $accent = (string) Setting::getValue('accent_color', '');
        return $accent !== '' ? $accent : primary_color();
    }
}

if (!function_exists('accent_color_dark')) {
    function accent_color_dark(): string
    {
        $hex = ltrim(accent_color(), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return primary_color_dark();
        }

        $factor = 0.85;
        $r = (int) round(hexdec(substr($hex, 0, 2)) * $factor);
        $g = (int) round(hexdec(substr($hex, 2, 2)) * $factor);
        $b = (int) round(hexdec(substr($hex, 4, 2)) * $factor);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
