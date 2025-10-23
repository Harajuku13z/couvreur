<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Get a setting value by key, with optional default.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (!function_exists('settings')) {
    /**
     * Get settings by group, or all settings when group is null.
     * Returns an associative array.
     */
    function settings(?string $group = null): array
    {
        if ($group !== null) {
            return Setting::getGroup($group);
        }
        return Setting::getAll();
    }
}

if (!function_exists('company')) {
    /**
     * Convenience accessor for company_* settings.
     * When $key is null, returns the whole company group as array.
     */
    function company(?string $key = null, mixed $default = null): mixed
    {
        if ($key !== null) {
            return Setting::get('company_' . $key, $default);
        }
        return Setting::getGroup('company');
    }
}








