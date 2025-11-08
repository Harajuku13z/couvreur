<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
                try {
                    $setting = self::where('key', $key)->first();
                    if (!$setting) {
                        return $default;
                    }
                    return self::castValue($setting->value, $setting->type);
                } catch (\Exception $e) {
                    // Si la base de données n'est pas accessible, retourner la valeur par défaut
                    \Log::warning("Impossible d'accéder au setting '{$key}': " . $e->getMessage());
                    return $default;
                }
            });
        } catch (\Exception $e) {
            // Si le cache n'est pas accessible (ex: cache DB), retourner la valeur par défaut
            \Log::warning("Impossible d'accéder au cache pour le setting '{$key}': " . $e->getMessage());
            return $default;
        }
    }

    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general', ?string $description = null): void
    {
        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );
        Cache::forget("setting_{$key}");
    }

    public static function getGroup(string $group): array
    {
        try {
            return Cache::remember("settings_group_{$group}", 3600, function () use ($group) {
                try {
                    $settings = self::where('group', $group)->get();
                    $result = [];
                    foreach ($settings as $setting) {
                        $result[$setting->key] = self::castValue($setting->value, $setting->type);
                    }
                    return $result;
                } catch (\Exception $e) {
                    \Log::warning("Impossible d'accéder aux settings du groupe '{$group}': " . $e->getMessage());
                    return [];
                }
            });
        } catch (\Exception $e) {
            \Log::warning("Impossible d'accéder au cache pour le groupe '{$group}': " . $e->getMessage());
            return [];
        }
    }

    public static function getAll(): array
    {
        try {
            return Cache::remember('all_settings', 3600, function () {
                try {
                    $settings = self::all();
                    $result = [];
                    foreach ($settings as $setting) {
                        $result[$setting->key] = self::castValue($setting->value, $setting->type);
                    }
                    return $result;
                } catch (\Exception $e) {
                    \Log::warning("Impossible d'accéder à tous les settings: " . $e->getMessage());
                    return [];
                }
            });
        } catch (\Exception $e) {
            \Log::warning("Impossible d'accéder au cache pour tous les settings: " . $e->getMessage());
            return [];
        }
    }

    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json', 'array' => self::safeJsonDecode($value),
            default => $value,
        };
    }

    /**
     * Safely decode JSON with error handling
     */
    protected static function safeJsonDecode($value): mixed
    {
        if (is_array($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        
        return [];
    }

    public static function clearCache(): void
    {
        Cache::flush();
    }

    public static function isSetupCompleted(): bool
    {
        return self::get('setup_completed', false);
    }

    public static function markSetupCompleted(): void
    {
        self::set('setup_completed', true, 'boolean', 'general', 'Initial setup completed');
    }
}


