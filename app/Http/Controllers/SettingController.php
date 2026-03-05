<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return new \App\Http\Resources\Setting\SettingCollection(Setting::all());
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
        $setting = Setting::findOrFail($key);
        return new \App\Http\Resources\Setting\SettingResource($setting);
    }

    public function publicSettings()
    {
        $setting = Setting::first();
        if (!$setting) {
            return response()->json(['message' => 'Settings not found'], 404);
        }

        $socials = \App\Models\SocialLink::all();
        $faqs = \App\Models\Faq::where('active', true)->orderBy('order_index')->get();
        $footerCategories = \App\Models\FooterLinkCategory::with(['links' => function ($q) {
            $q->orderBy('order');
        }])->get();

        $footerLinks = [];
        foreach ($footerCategories as $category) {
            $footerLinks[$category->name] = \App\Http\Resources\FooterLinkResource::collection($category->links);
        }

        return response()->json([
            'data' => [
                'general' => new \App\Http\Resources\SettingResource($setting),
                'socials' => \App\Http\Resources\SocialLinkResource::collection($socials),
                'faqs' => \App\Http\Resources\FaqResource::collection($faqs),
                'footerLinks' => $footerLinks,
            ]
        ]);
    }
}
