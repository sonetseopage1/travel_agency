@php
    $selectedTours = old('tour_ids', isset($promoCode) ? $promoCode->tours->pluck('id')->all() : []);
@endphp

@if (session('success'))
    <div class="mb-6 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm px-4 py-3">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm px-4 py-3">
        <ul class="list-disc ps-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <h3 class="font-bold text-lg mb-5">Basic Information</h3>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="label" for="code">Code <span class="text-red-500">*</span></label>
                    <input id="code" name="code" value="{{ old('code', $promoCode->code) }}" required
                        placeholder="E.g. EID2026" class="input font-mono uppercase"
                        pattern="[A-Za-z0-9_-]+">
                    <p class="mt-1.5 text-xs text-slate-500">Only letters, numbers, dash and underscore.</p>
                    @error('code')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label" for="description">Description</label>
                    <input id="description" name="description" value="{{ old('description', $promoCode->description) }}"
                        placeholder="যেমন: ঈদ উপলক্ষে বিশেষ ছাড়" class="input">
                    @error('description')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <h3 class="font-bold text-lg mb-5">Discount</h3>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="label" for="discount_type">Discount Type <span class="text-red-500">*</span></label>
                    <select id="discount_type" name="discount_type" class="input" onchange="toggleMaxDiscount()" required>
                        <option value="percentage" @selected(old('discount_type', $promoCode->discount_type ?? 'percentage') === 'percentage')>
                            Percentage (%)
                        </option>
                        <option value="fixed" @selected(old('discount_type', $promoCode->discount_type) === 'fixed')>
                            Fixed Amount (৳)
                        </option>
                    </select>
                    @error('discount_type')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label" for="discount_value">Discount Value <span class="text-red-500">*</span></label>
                    <input id="discount_value" name="discount_value" type="number" step="0.01" min="0"
                        value="{{ old('discount_value', $promoCode->discount_value) }}" required class="input">
                    @error('discount_value')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div id="maxDiscountField" class="mt-5">
                <label class="label" for="max_discount">Maximum Discount (৳)</label>
                <input id="max_discount" name="max_discount" type="number" step="0.01" min="0"
                    value="{{ old('max_discount', $promoCode->max_discount) }}" class="input"
                    placeholder="খালি রাখলে সীমা থাকবে না">
                <p class="mt-1.5 text-xs text-slate-500">শুধুমাত্র percentage ছাড়ে কাজ করে।</p>
                @error('max_discount')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <h3 class="font-bold text-lg mb-5">Validity &amp; Usage</h3>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="label" for="valid_from">Start Date</label>
                    <input id="valid_from" name="valid_from" type="date"
                        value="{{ old('valid_from', $promoCode->valid_from?->format('Y-m-d')) }}" class="input">
                    @error('valid_from')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label" for="valid_until">End Date</label>
                    <input id="valid_until" name="valid_until" type="date"
                        value="{{ old('valid_until', $promoCode->valid_until?->format('Y-m-d')) }}" class="input">
                    @error('valid_until')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label" for="usage_limit">Usage Limit</label>
                    <input id="usage_limit" name="usage_limit" type="number" min="1"
                        value="{{ old('usage_limit', $promoCode->usage_limit) }}" class="input"
                        placeholder="খালি রাখলে সীমা থাকবে না">
                    @error('usage_limit')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-end">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                            @checked(old('is_active', $promoCode->is_active ?? true))
                            class="w-5 h-5 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                        <span class="text-sm font-semibold">Active রাখুন</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <div class="flex items-center justify-between gap-4 mb-2">
                <h3 class="font-bold text-lg">Applicable Tours</h3>
                <button type="button" onclick="toggleAllTours(this)"
                    class="text-xs font-bold text-teal-700 hover:underline">
                    সব নির্বাচন / বাতিল
                </button>
            </div>
            <p class="text-sm text-slate-500 mb-4">কোনো ট্যুর নির্বাচন না করলে প্রোমো কোডটি সব ট্যুরে কাজ করবে।</p>

            <div class="max-h-72 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-800 divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($tours as $tour)
                    <label class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <input type="checkbox" name="tour_ids[]" value="{{ $tour->id }}" class="tour-checkbox"
                            @checked(in_array($tour->id, (array) $selectedTours))
                            class="w-4 h-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                        <div class="min-w-0">
                            <p class="font-semibold text-sm truncate">{{ $tour->title }}</p>
                            <p class="text-xs text-slate-500">{{ $tour->location }}</p>
                        </div>
                    </label>
                @empty
                    <p class="p-6 text-center text-sm text-slate-500">কোনো ট্যুর নেই।</p>
                @endforelse
            </div>
            @error('tour_ids')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <aside class="space-y-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <h3 class="font-bold mb-4">Save</h3>
            <button type="submit"
                class="w-full bg-teal-700 hover:bg-teal-800 text-white font-semibold py-3 rounded-xl transition">
                {{ $submitLabel }}
            </button>
            <a href="{{ route('admin.promo-codes.index') }}"
                class="w-full mt-3 block text-center border border-slate-300 dark:border-slate-700 font-semibold py-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                Cancel
            </a>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-5 text-sm">
            <p class="font-bold text-blue-800 dark:text-blue-300 mb-2">ব্যবহারের নিয়ম</p>
            <ul class="space-y-2 text-blue-700 dark:text-blue-300 text-xs leading-6">
                <li>• Percentage অথবা fixed — দুটোর যেকোনো একটি বেছে নিন।</li>
                <li>• Percentage ছাড়ে সর্বোচ্চ সীমা দিতে পারেন।</li>
                <li>• তারিখের বিস্তার দিলে ওই সময়ের মধ্যেই কোডটি কাজ করবে।</li>
                <li>• নির্দিষ্ট ট্যুর না বাছলে সব ট্যুরে প্রযোজ্য হবে।</li>
                <li>• Fixed ছাড় কখনোই মোট টাকার চেয়ে বেশি হবে না।</li>
            </ul>
        </div>
    </aside>
</div>

<script>
    function toggleMaxDiscount() {
        const isPercentage = document.getElementById('discount_type').value === 'percentage';
        const field = document.getElementById('maxDiscountField');
        field.classList.toggle('hidden', !isPercentage);
        const input = document.getElementById('max_discount');
        input.disabled = !isPercentage;
        if (!isPercentage) input.value = '';
    }

    function toggleAllTours(button) {
        const boxes = document.querySelectorAll('.tour-checkbox');
        const anyUnchecked = Array.from(boxes).some((b) => !b.checked);
        boxes.forEach((b) => (b.checked = anyUnchecked));
        button.textContent = anyUnchecked ? 'সব বাতিল করুন' : 'সব নির্বাচন করুন';
    }

    toggleMaxDiscount();
</script>
