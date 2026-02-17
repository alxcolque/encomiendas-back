<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return Setting::all()->pluck('value', 'key');
    }

    public function store(Request $request)
    {
        // Upsert logic for bulk settings update
        foreach ($request->all() as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : $value]
            );
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }

    public function show($key)
    {
        return Setting::findOrFail($key);
    }
}
