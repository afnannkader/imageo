<?php

namespace App\Models;

use App\Methods\UnicodeModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Settings extends UnicodeModel
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'value' => 'object',
    ];

    public static function selectSettings($key)
    {
        $setting = Settings::where('key', $key)->first();
        if ($setting) {
            return $setting->value;
        }
        return false;
    }

    public static function updateSettings($key, $data)
    {
        $setting = Settings::where('key', $key)->first();
        if ($setting) {
            $settings = (array) $setting->value;
            foreach ($data as $dataKey => $dataValue) {
                if (array_key_exists($dataKey, $settings)) {
                    $settings[$dataKey] = $dataValue;
                }
            }
            if (count((array) $setting->value) == count($settings)) {
                $setting->value = $settings;
                return $setting->save();
            }
        }
        return false;
    }

    public const WATERMARK_POSITIONS = [
        'top-left' => 'Top Left',
        'top' => 'Top',
        'top-right' => 'Top Right',
        'left' => 'Left',
        'center' => 'Center',
        'right' => 'Right',
        'bottom-left' => 'Bottom Left',
        'bottom' => 'Bottom',
        'bottom-right' => 'Bottom Right',
    ];
}