<?php

namespace App\Models;

use NovaFlow\Core\Model;
use NovaFlow\Core\DB;

/**
 * SettingModel
 * Handles dynamic site configuration
 */
class SettingModel extends Model
{
    protected static string $settingsTable = 'settings';

    /**
     * Get all settings as key-value pair
     */
    public static function getAll(): array
    {
        $settings = DB::fetchAll("SELECT setting_key, setting_value FROM " . self::$settingsTable);
        $result = [];
        foreach ($settings as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        return $result;
    }

    /**
     * Get single setting value
     */
    public static function get(string $key, $default = null)
    {
        $value = DB::fetchValue("SELECT setting_value FROM " . self::$settingsTable . " WHERE setting_key = ?", [$key]);
        return $value !== false ? $value : $default;
    }

    /**
     * Update or Insert setting
     */
    public static function set(string $key, $value): bool
    {
        $exists = DB::fetchValue("SELECT COUNT(*) FROM " . self::$settingsTable . " WHERE setting_key = ?", [$key]);
        
        if ($exists) {
            return DB::query("UPDATE " . self::$settingsTable . " SET setting_value = ? WHERE setting_key = ?", [$value, $key]);
        } else {
            return DB::query("INSERT INTO " . self::$settingsTable . " (setting_key, setting_value) VALUES (?, ?)", [$key, $value]);
        }
    }
}
