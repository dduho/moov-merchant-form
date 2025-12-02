<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value.
     */
    public static function setValue(string $key, $value, string $type = 'string', ?string $description = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    /**
     * Cast value to its proper type.
     */
    protected static function castValue(string $value, string $type)
    {
        return match ($type) {
            'integer' => (int) $value,
            'float' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Get the proximity alert distance in meters.
     */
    public static function getProximityAlertDistance(): int
    {
        return static::getValue('proximity_alert_distance', 300);
    }

    /**
     * Get the required GPS accuracy in meters.
     */
    public static function getRequiredGpsAccuracy(): int
    {
        return static::getValue('required_gps_accuracy', 30);
    }

    /**
     * Check if low accuracy should be auto-rejected.
     */
    public static function shouldAutoRejectLowAccuracy(): bool
    {
        return static::getValue('auto_reject_low_accuracy', false);
    }
}
