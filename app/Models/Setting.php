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

    /**
     * Get a setting value by key
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value by key
     * 
     * @param string $key
     * @param mixed $value
     * @return Setting
     */
    public static function set($key, $value, $type = 'text', $group = 'general', $description = null)
    {
        $setting = self::firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->type = $type;
        $setting->group = $group;
        if ($description) {
            $setting->description = $description;
        }
        $setting->save();
        
        Cache::forget("setting.{$key}");
        
        return $setting;
    }

    /**
     * Get all shop settings
     * 
     * @return array
     */
    public static function getShopSettings()
    {
        return Cache::remember('shop_settings', 3600, function () {
            $shopSettings = self::where('group', 'shop')->pluck('value', 'key')->toArray();
            $adSettings = self::where('group', 'advertisement')->pluck('value', 'key')->toArray();
            
            return [
                'shop_name' => $shopSettings['shop_name'] ?? 'POS SYSTEM',
                'shop_address' => $shopSettings['shop_address'] ?? '',
                'shop_phone' => $shopSettings['shop_phone'] ?? '',
                'shop_email' => $shopSettings['shop_email'] ?? '',
                'shop_logo' => $shopSettings['shop_logo'] ?? '',
                'footer_message' => $shopSettings['footer_message'] ?? 'Thank you for your business!',
                'software_company_name' => $adSettings['software_company_name'] ?? '',
                'software_company_website' => $adSettings['software_company_website'] ?? '',
                'software_company_tagline' => $adSettings['software_company_tagline'] ?? '',
                'show_advertisement' => $adSettings['show_advertisement'] ?? '0',
            ];
        });
    }

    /**
     * Clear settings cache
     */
    public static function clearCache()
    {
        Cache::forget('shop_settings');
        $settings = self::pluck('key');
        foreach ($settings as $key) {
            Cache::forget("setting.{$key}");
        }
    }
}
