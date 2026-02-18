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
}
