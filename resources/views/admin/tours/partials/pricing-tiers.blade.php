{{--
    Pricing tiers for a tour.

    Each tier carries four independent levers: the adult rate, the child age
    policy, the cabin surcharge, and its own discount. A tier with a blank
    price is treated as not configured and is not offered to customers.

    Shared by the create and edit forms. On create there are no tiers yet, so
    $tiersByType is empty and the markup renders blank rows.
--}}

@php
    $existing = $tiersByType ?? [];
    $prefix = $prefix ?? 'tiers';
@endphp

<div class="mb-6">
    <h2 class="text-lg font-extrabold">বুকিং ধরন ও মূল্য</h2>
    <p class="text-xs text-slate-500 mt-1">
        প্রতিটি ধরনের জন্য প্রাপ্তবয়স্কদের হার, শিশুর বয়স-নিয়ম, অতিরিক্ত কেবিনের খরচ ও ছাড় আলাদা করে ঠিক করুন।
        মূল্য খালি রাখলে সেই ধরনটি গ্রাহককে দেখানো হবে না।
    </p>
</div>

@foreach (\App\Models\TourPricingTier::TYPES as $sortOrder => $type)
    @php
        $tier = $existing[$type] ?? null;
        $name = $prefix.'['.$type.']';
    @endphp

    <div class="border border-slate-200 dark:border-slate-800 rounded-2xl p-5 mb-4">
        <div class="flex items-center justify-between mb-4">
            <label class="flex items-center gap-2 font-bold text-sm">
                <input type="checkbox" name="{{ $name }}[enabled]" value="1"
                    @checked($tier && $tier->is_active)
                    class="accent-teal-700 w-4 h-4">
                {{ ucfirst($type) }}
            </label>
            @if ($tier)
                <span class="text-xs text-slate-500">
                    বুকড কেবিন {{ $tier->cabins_booked }}{{ $tier->cabins_total !== null ? ' / '.$tier->cabins_total : '' }}
                </span>
            @endif
        </div>

        <div class="grid md:grid-cols-4 gap-4">
            <div>
                <label class="label">প্রাপ্তবয়স্কদের হার (৳)</label>
                <input type="number" name="{{ $name }}[price_per_adult]" min="0" step="0.01" class="input"
                    value="{{ old($name.'[price_per_adult]', $tier->price_per_adult ?? '') }}"
                    placeholder="যেমন: 4500">
            </div>
            <div>
                <label class="label">সর্বনিম্ন প্রাপ্তবয়স্ক</label>
                <input type="number" name="{{ $name }}[min_adults]" min="1" max="100" class="input"
                    value="{{ old($name.'[min_adults]', $tier->min_adults ?? 1) }}">
            </div>
            <div>
                <label class="label">সর্বোচ্চ প্রাপ্তবয়স্ক</label>
                <input type="number" name="{{ $name }}[max_adults]" min="1" max="100" class="input"
                    value="{{ old($name.'[max_adults]', $tier->max_adults ?? 1) }}">
            </div>
            <div>
                <label class="label">লেবেল</label>
                <input type="text" name="{{ $name }}[label]" maxlength="60" class="input"
                    value="{{ old($name.'[label]', $tier->label ?? '') }}"
                    placeholder="যেমন: পরিবারের জন্য">
            </div>
        </div>

        <p class="text-xs font-semibold text-slate-500 mt-5 mb-2">শিশুর বয়স-নিয়ম</p>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="label">এই বয়সের কম বিনামূল্যে (বছর)</label>
                <input type="number" name="{{ $name }}[infant_age_max]" min="0" max="18" class="input"
                    value="{{ old($name.'[infant_age_max]', $tier->infant_age_max ?? 3) }}">
            </div>
            <div>
                <label class="label">এই বয়সের কম শিশুর হার (%)</label>
                <input type="number" name="{{ $name }}[child_price_percent]" min="0" max="100" step="0.01" class="input"
                    value="{{ old($name.'[child_price_percent]', $tier->child_price_percent ?? 50) }}">
            </div>
            <div>
                <label class="label">শিশু হওয়ার সর্বোচ্চ বয়স (বছর)</label>
                <input type="number" name="{{ $name }}[child_age_max]" min="1" max="18" class="input"
                    value="{{ old($name.'[child_age_max]', $tier->child_age_max ?? 8) }}">
            </div>
        </div>

        <p class="text-xs font-semibold text-slate-500 mt-5 mb-2">কেবিন</p>
        <div class="grid md:grid-cols-4 gap-4">
            <div>
                <label class="label">প্রতি কেবিনে জন</label>
                <input type="number" name="{{ $name }}[capacity_per_cabin]" min="1" max="50" class="input"
                    value="{{ old($name.'[capacity_per_cabin]', $tier->capacity_per_cabin ?? 4) }}">
            </div>
            <div>
                <label class="label">অন্তর্ভুক্ত কেবিন</label>
                <input type="number" name="{{ $name }}[included_cabin_count]" min="0" max="50" class="input"
                    value="{{ old($name.'[included_cabin_count]', $tier->included_cabin_count ?? 1) }}">
            </div>
            <div>
                <label class="label">অতিরিক্ত কেবিনের খরচ (৳)</label>
                <input type="number" name="{{ $name }}[extra_cabin_fee]" min="0" step="0.01" class="input"
                    value="{{ old($name.'[extra_cabin_fee]', $tier->extra_cabin_fee ?? 0) }}">
            </div>
            <div>
                <label class="label">মোট কেবিন</label>
                <input type="number" name="{{ $name }}[cabins_total]" min="0" max="999" class="input"
                    value="{{ old($name.'[cabins_total]', $tier->cabins_total ?? '') }}"
                    placeholder="ফাঁকা রাখলে ট্র্যাক হবে না">
            </div>
        </div>

        <p class="text-xs font-semibold text-slate-500 mt-5 mb-2">ছাড়</p>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="label">ছাড়ের ধরন</label>
                <select name="{{ $name }}[discount_type]" class="input">
                    @foreach (['none' => 'নেই', 'percent' => 'শতকরা হার (%)', 'fixed' => 'নির্দিষ্ট টাকা (৳)'] as $value => $label)
                        <option value="{{ $value }}"
                            @selected(old($name.'[discount_type]', $tier->discount_type ?? 'none') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">ছাড়ের পরিমাণ</label>
                <input type="number" name="{{ $name }}[discount_value]" min="0" step="0.01" class="input"
                    value="{{ old($name.'[discount_value]', $tier->discount_value ?? 0) }}">
            </div>
        </div>

        <p class="text-xs text-slate-500 mt-3">
            ছাড় শুধু যাত্রীর খরচের উপর প্রযোজ্য, অতিরিক্ত কেবিনের খরচের উপর নয়।
        </p>
    </div>
@endforeach
