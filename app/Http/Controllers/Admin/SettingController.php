<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function api()
    {
        $apiEnabled = Setting::get('api_enabled', '0') === '1';
        $apiToken = Setting::get('api_token');
        
        if (!$apiToken) {
            $apiToken = Str::random(32);
            Setting::set('api_token', $apiToken);
        }

        return view('admin.settings.api', compact('apiEnabled', 'apiToken'));
    }

    public function updateApi(Request $request)
    {
        $enabled = $request->has('api_enabled') ? '1' : '0';
        Setting::set('api_enabled', $enabled);

        if ($request->has('generate_token')) {
            Setting::set('api_token', Str::random(32));
            return back()->with('success', 'API settings updated and new token generated successfully.');
        }

        return back()->with('success', 'API settings updated successfully.');
    }
}
