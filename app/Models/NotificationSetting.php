<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = ['type', 'label', 'admin_email', 'user_email'];

    protected $casts = [
        'admin_email' => 'boolean',
        'user_email' => 'boolean',
    ];

    /**
     * Check whether email should be sent for the given notification type to the given role.
     * $role: 'admin' or 'user'
     */
    public static function isEmailEnabled(string $type, string $role): bool
    {
        $setting = static::where('type', $type)->first();
        if (!$setting) {
            return false;
        }
        return $role === 'admin' ? (bool) $setting->admin_email : (bool) $setting->user_email;
    }
}
