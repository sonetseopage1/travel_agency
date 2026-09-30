@php
    $selectedDestination = old('destination_id', $photo->destination_id);
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
            <h3 class="font-bold text-lg mb-5">ছবির তথ্য</h3>

            <div class="space-y-5">
                <div>
                    <label class="label" for="image">
                        ছবি <span class="text-red-500">*</span>
                    </label>
                    <input id="image" name="image" type="file" accept="image/*" class="input" required>
                    <p class="mt-1.5 text-xs text-slate-500">
                        JPG, PNG, WEBP, GIF · সর্বোচ্চ ৪ মেগাবাইট · বর্ধনের জন্য স্ক্যান করা আকার রাখুন।
                    </p>
                    @error('image')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label" for="title">শিরোনাম</label>
                    <input id="title" name="title" value="{{ old('title', $photo->title) }}" class="input"
                        placeholder="যেমন: সাজেকের সকাল">
                    @error('title')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label" for="caption">ক্যাপশন</label>
                    <input id="caption" name="caption" value="{{ old('caption', $photo->caption) }}" class="input"
                        placeholder="ছবির নিচে দেখানো ছোট বর্ণনা">
                    @error('caption')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label" for="alt_text">Alt টেক্সট</label>
                    <input id="alt_text" name="alt_text" value="{{ old('alt_text', $photo->alt_text) }}" class="input"
                        placeholder="স্ক্রিন রিডারের জন্য ছবির বর্ণনা">
                    <p class="mt-1.5 text-xs text-slate-500">
                        অ্যাক্সেসিবিলিটির জন্য ব্যবহৃত হয়, পাতায় দেখানো হয় না।
                    </p>
                    @error('alt_text')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label" for="destination_id">গন্তব্য</label>
                    <select id="destination_id" name="destination_id" class="input">
                        <option value="">কোনো গন্তব্য নির্ধারণ করা হয়নি</option>
                        @foreach ($destinations as $destination)
                            <option value="{{ $destination->id }}" @selected((string) $selectedDestination === (string) $destination->id)>
                                {{ $destination->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('destination_id')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <aside class="space-y-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <h3 class="font-bold mb-4">প্রকাশ</h3>
            <label class="flex items-center gap-3 cursor-pointer mb-4">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $photo->is_active ?? true))
                    class="w-5 h-5 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                <span class="text-sm font-semibold">সাইটে প্রদর্শন করুন</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $photo->is_featured ?? false))
                    class="w-5 h-5 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                <span class="text-sm font-semibold">ফিচার্ড করুন</span>
            </label>
            <p class="text-xs text-slate-500 mt-2">ফিচার্ড ছবি হোমপেজের গ্যালারি সেকশনে প্রথমে দেখানো হয়।</p>

            <div class="mt-5">
                <label class="label" for="sort_order">অর্ডার</label>
                <input id="sort_order" name="sort_order" type="number" min="0" max="9999"
                    value="{{ old('sort_order', $photo->sort_order ?? 0) }}" class="input">
                <p class="mt-1.5 text-xs text-slate-500">ছোট সংখ্যা আগে দেখানো হয়।</p>
                @error('sort_order')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <h3 class="font-bold mb-4">Save</h3>
            <button type="submit"
                class="w-full bg-teal-700 hover:bg-teal-800 text-white font-semibold py-3 rounded-xl transition">
                {{ $submitLabel }}
            </button>
            <a href="{{ route('admin.gallery.index') }}"
                class="w-full mt-3 block text-center border border-slate-300 dark:border-slate-700 font-semibold py-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                Cancel
            </a>
        </div>

        @if ($photo->exists && $photo->image)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
                <h3 class="font-bold mb-3 text-sm">বর্তমান ছবি</h3>
                <img src="{{ $photo->image_url }}" alt="{{ $photo->alt_text ?: $photo->displayTitle() }}"
                    class="w-full rounded-xl aspect-square object-cover bg-slate-100 dark:bg-slate-800">
                <p class="text-xs text-slate-500 mt-2 break-all">{{ $photo->image }}</p>
                <a href="{{ route('gallery.show', $photo) }}" target="_blank" rel="noopener"
                    class="block text-center mt-3 px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    পাবলিক পেজে দেখুন ↗
                </a>
            </div>
        @endif
    </aside>
</div>
