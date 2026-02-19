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
            'logo' => 'nullable|image|max:2048', // 2MB Max
            'favicon' => 'nullable|image|max:1024', // 1MB Max
            'type' => 'required|in:logo,favicon'
        ]);

        $setting = Setting::firstOrFail();
        $type = $request->type;

        if ($request->hasFile($type)) {
            $file = $request->file($type);

            // Convert to Base64 for FileStorage
            $base64 = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file));

            // Define folder path
            $folderPath = 'kolmox/settings';

            // Use FileStorage to upload/replace
            // Note: Since we don't store the file key/ID in settings table for now (only URL), 
            // we can't effectively use the delete feature of ImageKit through FileStorage::delete 
            // if we don't have the ID. 
            // However, FileStorage::replace calls delete then upload. 
            // If we strictly follow User pattern, we'd need a key. 
            // For now, we'll just upload and update the URL.
            // If local, FileStorage returns URL. If ImageKit, it returns fileId,url.

            $response = \App\Http\Controllers\Files\FileStorage::upload($base64, $folderPath);

            $url = $response;
            // Handle ImageKit response "fileId,url"
            if (strpos($response, ',') !== false && env('DIR_PATH_FILE') === 'imagekit') {
                $parts = explode(',', $response);
                $url = $parts[1];
                // construct to save ID? For now just URL as per schema.
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
