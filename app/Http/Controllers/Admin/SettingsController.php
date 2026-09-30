<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Text settings the admin may edit, grouped for the form.
     */
    private const TEXT_FIELDS = [
        'site_name', 'site_tagline', 'site_intro', 'footer_note',
        'meta_title', 'meta_description', 'meta_keywords',
        'og_title', 'og_description',
        'contact_address', 'contact_phone', 'contact_email',
        'social_facebook', 'social_instagram', 'social_youtube',
        'social_twitter', 'social_linkedin',
    ];

    /**
     * Image settings, mapped to the upload field they come from.
     */
    private const IMAGE_FIELDS = [
        'logo' => 'logo',
        'favicon' => 'favicon',
        'og_image' => 'og_image',
    ];

    public function __construct(private readonly SettingImageService $images) {}

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => Setting::map(),
            'logoUrl' => Setting::image('logo'),
            'faviconUrl' => Setting::image('favicon'),
            'ogImageUrl' => Setting::image('og_image'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'site_tagline' => ['nullable', 'string', 'max:180'],
            'site_intro' => ['nullable', 'string', 'max:1000'],
            'footer_note' => ['nullable', 'string', 'max:180'],

            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:400'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:180'],
            'og_description' => ['nullable', 'string', 'max:400'],

            'contact_address' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'contact_email' => ['nullable', 'email', 'max:120'],

            'social_facebook' => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'social_youtube' => ['nullable', 'url', 'max:255'],
            'social_twitter' => ['nullable', 'url', 'max:255'],
            'social_linkedin' => ['nullable', 'url', 'max:255'],
        ] + $this->imageRules() + [
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
            'remove_og_image' => ['nullable', 'boolean'],
        ] + [
            'site_name.required' => 'সাইটের নাম লিখুন।',
            'contact_email.email' => 'সঠিক ইমেইল ঠিকানা দিন।',
            'social_facebook.url' => 'সঠিক ফেসবুক লিংক দিন।',
            'social_instagram.url' => 'সঠিক ইনস্টাগ্রাম লিংক দিন।',
            'social_youtube.url' => 'সঠিক ইউটিউব লিংক দিন।',
            'social_twitter.url' => 'সঠিক টুইটার লিংক দিন।',
            'social_linkedin.url' => 'সঠিক লিংকডইন লিংক দিন।',
        ]);

        // A blank social field should clear the link, not fall back to a default.
        $values = [];

        foreach (self::TEXT_FIELDS as $key) {
            $values[$key] = trim((string) ($validated[$key] ?? '')) ?: null;
        }

        // The site name is required, so restore it if the trim emptied it.
        $values['site_name'] = trim((string) $validated['site_name']);

        $orphans = [];

        foreach (self::IMAGE_FIELDS as $key => $field) {
            $previous = Setting::imagePath($key);

            if ($request->boolean('remove_'.$key)) {
                $values[$key] = null;

                if ($previous) {
                    $orphans[] = $previous;
                }

                continue;
            }

            if ($request->hasFile($field)) {
                $values[$key] = $this->images->store($request->file($field), $key);

                if ($previous) {
                    $orphans[] = $previous;
                }
            }
        }

        Setting::putMany($values);

        foreach (array_unique(array_filter($orphans)) as $path) {
            $this->images->delete($path);
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'সেটিংস সফলভাবে সংরক্ষণ হয়েছে।');
    }

    /**
     * Validation rules for the three branding uploads, built from the service
     * so the accepted formats and size limit stay in one place.
     *
     * @return array<string, array<int, string>>
     */
    private function imageRules(): array
    {
        $rules = [];

        foreach (array_keys(self::IMAGE_FIELDS) as $key) {
            $rules[$key] = $this->images->rules();
        }

        return $rules;
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', Rule::notIn(['password123', '12345678', 'admin123'])],
        ], [
            'current_password.required' => 'বর্তমান পাসওয়ার্ড দিন।',
            'password.required' => 'নতুন পাসওয়ার্ড দিন।',
            'password.min' => 'নতুন পাসওয়ার্ড অন্তত ৮ অক্ষরের হতে হবে।',
            'password.confirmed' => 'নতুন পাসওয়ার্ড দুইবার একইভাবে দিন।',
            'password.not_in' => 'এই পাসওয়ার্ডটি ব্যবহার করা যাবে না — আরও নিরাপদ একটি বেছে নিন।',
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return redirect()
                ->route('admin.settings.edit')
                ->withErrors(['current_password' => 'বর্তমান পাসওয়ার্ড সঠিক নয়।'])
                ->with('tab', 'security');
        }

        $user->password = $validated['password'];
        $user->save();

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'অ্যাডমিন পাসওয়ার্ড পরিবর্তন করা হয়েছে।');
    }
}
