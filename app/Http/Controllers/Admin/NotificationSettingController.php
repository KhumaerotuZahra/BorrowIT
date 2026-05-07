<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use Illuminate\Http\Request;

class NotificationSettingController extends Controller
{
    public function index()
    {
        $settings = NotificationSetting::orderBy('id')->get();
        return view('admin.notification-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $admin = $request->input('admin', []);
        $user  = $request->input('user', []);

        $settings = NotificationSetting::all();
        foreach ($settings as $setting) {
            $setting->update([
                'admin_email' => array_key_exists($setting->id, $admin),
                'user_email'  => array_key_exists($setting->id, $user),
            ]);
        }

        return back()->with('success', 'Notification settings updated.');
    }
}
