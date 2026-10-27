<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null): ?string
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public static function getWhatsAppConfig(): array
    {
        return [
            'api_url' => static::get('evolution_api_url'),
            'api_key' => static::get('evolution_api_key'),
            'instance_name' => static::get('evolution_instance_name'),
        ];
    }

    public static function isWhatsAppConfigured(): bool
    {
        $config = static::getWhatsAppConfig();

        return ! empty($config['api_url'])
            && ! empty($config['api_key'])
            && ! empty($config['instance_name']);
    }
}
