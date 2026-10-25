<?php

namespace App\Support;

/**
 * Turns a User-Agent string into the few things worth showing: browser,
 * operating system, device type and (on Android, when the browser still
 * sends it) the device model. Deliberately small — the raw string is kept
 * alongside for anything this doesn't recognise.
 */
class UserAgent
{
    public static function parse(?string $userAgent): array
    {
        $ua = (string) $userAgent;

        return [
            'browser' => self::browser($ua),
            'platform' => self::platform($ua),
            'device_type' => self::deviceType($ua),
            'device_model' => self::deviceModel($ua),
        ];
    }

    private static function browser(string $ua): ?string
    {
        $patterns = [
            'Edge' => '/Edg(?:e|A|iOS)?\/([\d.]+)/',
            'Samsung Internet' => '/SamsungBrowser\/([\d.]+)/',
            'Opera' => '/(?:OPR|Opera)\/([\d.]+)/',
            'Firefox' => '/(?:Firefox|FxiOS)\/([\d.]+)/',
            'Chrome' => '/(?:Chrome|CriOS)\/([\d.]+)/',
            'Safari' => '/Version\/([\d.]+).*Safari\//',
        ];

        foreach ($patterns as $name => $pattern) {
            if (preg_match($pattern, $ua, $match)) {
                // Android WebViews (e.g. the packaged app) say "; wv)".
                if ($name === 'Chrome' && str_contains($ua, '; wv)')) {
                    $name = 'Android WebView';
                }

                return $name . ' ' . explode('.', $match[1])[0];
            }
        }

        return $ua === '' ? null : 'Other';
    }

    private static function platform(string $ua): ?string
    {
        if (preg_match('/Windows NT ([\d.]+)/', $ua, $m)) {
            // 10.0 covers both Windows 10 and 11; browsers no longer tell them apart here.
            return ['10.0' => 'Windows 10/11', '6.3' => 'Windows 8.1', '6.2' => 'Windows 8', '6.1' => 'Windows 7'][$m[1]] ?? 'Windows';
        }
        if (preg_match('/Android ([\d.]+)/', $ua, $m)) {
            return 'Android ' . $m[1];
        }
        if (preg_match('/(?:iPhone|iPod).*? OS ([\d_]+)/', $ua, $m)) {
            return 'iOS ' . str_replace('_', '.', $m[1]);
        }
        if (preg_match('/iPad.*? OS ([\d_]+)/', $ua, $m)) {
            return 'iPadOS ' . str_replace('_', '.', $m[1]);
        }
        if (preg_match('/Mac OS X ([\d_]+)/', $ua, $m)) {
            return 'macOS ' . str_replace('_', '.', $m[1]);
        }
        if (str_contains($ua, 'CrOS')) {
            return 'ChromeOS';
        }
        if (str_contains($ua, 'Linux')) {
            return 'Linux';
        }

        return $ua === '' ? null : 'Other';
    }

    private static function deviceType(string $ua): ?string
    {
        if ($ua === '') {
            return null;
        }
        if (preg_match('/iPad|Tablet|PlayBook|Silk|(Android(?!.*Mobile))/i', $ua)) {
            return 'tablet';
        }
        if (preg_match('/Mobi|iPhone|iPod|Android.*Mobile|Windows Phone/i', $ua)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private static function deviceModel(string $ua): ?string
    {
        if (str_contains($ua, 'iPad')) {
            return 'iPad';
        }
        if (str_contains($ua, 'iPhone')) {
            return 'iPhone';
        }
        if (str_contains($ua, 'Macintosh')) {
            return 'Mac';
        }
        // "Linux; Android 14; SM-X200 Build/…)" — newer Chrome sends just "K" instead of the model.
        if (preg_match('/Android [\d.]+; ([^;)]+?)(?: Build\/[^;)]*)?[;)]/', $ua, $m)) {
            $model = trim($m[1]);

            return in_array($model, ['K', 'wv'], true) ? null : $model;
        }

        return null;
    }
}
