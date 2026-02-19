<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFaqRequest;
use App\Http\Requests\UpdateFooterLinksRequest;
use App\Http\Requests\UpdateGeneralSettingsRequest;
use App\Http\Requests\UpdateLegalRequest;
use App\Http\Requests\UpdatePaymentMethodsRequest;
use App\Http\Requests\UpdateSocialLinksRequest;
use App\Http\Resources\FaqResource;
use App\Http\Resources\FooterLinkResource;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\SettingResource;
use App\Http\Resources\SocialLinkResource;
use App\Models\Faq;
use App\Models\FooterLinkCategory;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $setting = Setting::firstOrFail();
        $socials = SocialLink::all();
        $faqs = Faq::orderBy('order_index')->get();
        // $faqs = Faq::orderBy('order_index')->get(); // Using order_index as per migration
        $footerCategories = FooterLinkCategory::with(['links' => function ($q) {
            $q->orderBy('order');
        }])->get();
        $paymentMethods = PaymentMethod::all();

        $footerLinks = [];
        foreach ($footerCategories as $category) {
            $footerLinks[$category->name] = FooterLinkResource::collection($category->links);
        }

        return response()->json([
            'general' => new SettingResource($setting),
            'socials' => SocialLinkResource::collection($socials),
            'faqs' => FaqResource::collection($faqs),
            'footerLinks' => $footerLinks,
            'paymentMethods' => PaymentMethodResource::collection($paymentMethods),
            'termsAndConditions' => $setting->terms_and_conditions,
            'privacyPolicy' => $setting->privacy_policy,
        ]);
    }

    public function updateGeneral(UpdateGeneralSettingsRequest $request)
    {
        $setting = Setting::firstOrFail();
        $setting->update($request->validated());
        return new SettingResource($setting);
    }

    public function updateSocials(UpdateSocialLinksRequest $request)
    {
        // Strategy: Delete all and recreate? Or update?
        // User request validation will probably send an array.
        // For simplicity and full sync, strict sync is often easier but let's see.
        // Assuming the request sends the full list of social links desirable.

        // Actually, usually these endpoints receive the full array to sync state.
        DB::transaction(function () use ($request) {
            SocialLink::truncate();
            foreach ($request->socials as $socialData) {
                SocialLink::create($socialData);
            }
        });

        return SocialLinkResource::collection(SocialLink::all());
    }

    public function updateFaqs(UpdateFaqRequest $request)
    {
        // Sync FAQs
        DB::transaction(function () use ($request) {
            Faq::truncate();
            foreach ($request->faqs as $index => $faqData) {
                // Handle order if not provided
                $faqData['order_index'] = $index + 1;
                Faq::create($faqData);
            }
        });

        return FaqResource::collection(Faq::orderBy('order_index')->get());
    }

    public function updateFooterLinks(UpdateFooterLinksRequest $request)
    {
        // Expecting object with categories
        DB::transaction(function () use ($request) {
            // We shouldn't truncate categories, just links?
            // Or maybe we assume categories are fixed (services, company, support, legal).
            // Let's iterate provided categories.

            foreach ($request->footerLinks as $categoryName => $links) {
                $category = FooterLinkCategory::firstOrCreate(['name' => $categoryName]);
                $category->links()->delete();

                foreach ($links as $index => $linkData) {
                    $category->links()->create([
                        'name' => $linkData['name'],
                        'href' => $linkData['href'],
                        'order' => $index + 1
                    ]);
                }
            }
        });

        $footerCategories = FooterLinkCategory::with(['links' => function ($q) {
            $q->orderBy('order');
        }])->get();

        $footerLinks = [];
        foreach ($footerCategories as $category) {
            $footerLinks[$category->name] = FooterLinkResource::collection($category->links);
        }

        return response()->json($footerLinks);
    }

    public function updatePaymentMethods(UpdatePaymentMethodsRequest $request)
    {
        DB::transaction(function () use ($request) {
            PaymentMethod::truncate();
            foreach ($request->paymentMethods as $methodData) {
                PaymentMethod::create($methodData);
            }
        });

        return PaymentMethodResource::collection(PaymentMethod::all());
    }

    public function updateLegal(UpdateLegalRequest $request)
    {
        $setting = Setting::firstOrFail();
        $setting->update($request->validated());

        return response()->json([
            'termsAndConditions' => $setting->terms_and_conditions,
            'privacyPolicy' => $setting->privacy_policy,
        ]);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048', // 2MB Max
            'favicon' => 'nullable|file|mimes:ico,png|max:1024', // 1MB Max, allow ico and png
            'type' => 'required|in:logo,favicon'
        ]);

        $setting = Setting::firstOrFail();
        $type = $request->type;

        if ($request->hasFile($type)) {
            $file = $request->file($type);

            // Delete old file if exists (Local only effectively)
            $oldUrl = $setting->$type;
            if ($oldUrl) {
                // We don't have file_id for ImageKit, so passing null. 
                // Only works for local storage as per plan.
                \App\Http\Controllers\Files\FileStorage::delete($oldUrl, null);
            }

            // Determine format
            $extension = $file->getClientOriginalExtension();
            $format = 'webp'; // Default

            if ($type === 'favicon') {
                // For favicon, prefer ico or png, do not convert to webp if it is ico
                if (in_array(strtolower($extension), ['ico', 'svg'])) {
                    $format = strtolower($extension);
                } else {
                    // If it's a png favicon, we can keep it as png or convert. 
                    // User asked "subir logo en formato png y ico sin cambiar de extension".
                    // So we keep extension if valid image.
                    $format = strtolower($extension);
                }
            } else { // logo
                // User asked "subir logo en formato png... sin cambiar de extension".
                // So we use original extension if it is an image.
                $format = strtolower($extension);
                if ($format == 'jpeg') $format = 'jpg';
            }

            // Convert to Base64 for FileStorage
            $base64 = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file));

            // Define folder path
            $folderPath = 'kolmox/settings';

            // Upload with specific format
            $response = \App\Http\Controllers\Files\FileStorage::upload($base64, $folderPath, $format);

            $url = $response;
            // Handle ImageKit response "fileId,url"
            if (strpos($response, ',') !== false && env('DIR_PATH_FILE') === 'imagekit') {
                $parts = explode(',', $response);
                $url = $parts[1];
            }

            // Check for error
            if (strpos($url, 'Error') === 0) {
                return response()->json(['message' => $url], 400);
            }

            $setting->update([$type => $url]);
        }

        return new SettingResource($setting);
    }
}
