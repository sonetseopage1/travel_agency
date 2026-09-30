@extends('layouts.admin')

@section('title', 'Add Tour')
@section('breadcrumb', 'Tours / Create')
@section('page-title', 'নতুন ট্যুর যোগ করুন')

@section('content')

@if (session('success'))
    <div class="mb-5 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm px-4 py-3">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-5 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm px-4 py-3">
        <p class="font-semibold mb-1">নিচের বিষয়গুলো ঠিক করতে হবে:</p>
        <ul class="list-disc ps-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-extrabold">Tour Information</h2>
        <p class="text-sm text-slate-500 mt-1">Customer-facing tour details এখানে manage করুন।</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.tours.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold">Cancel</a>
        <button type="button" onclick="document.getElementById('tourForm').submit();" class="px-4 py-2.5 rounded-xl bg-slate-800 text-white text-sm font-bold">Save Draft</button>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-x-auto hide-scrollbar mb-6">
    <div class="flex min-w-max">
        <button data-tab="basic" class="tour-tab tab-active px-5 py-4 border-b-2 text-sm font-bold">01. Basic Info</button>
        <button data-tab="media" class="tour-tab px-5 py-4 border-b-2 border-transparent text-sm font-semibold text-slate-500">02. Media</button>
        <button data-tab="pricing" class="tour-tab px-5 py-4 border-b-2 border-transparent text-sm font-semibold text-slate-500">03. Pricing</button>
        <button data-tab="itinerary" class="tour-tab px-5 py-4 border-b-2 border-transparent text-sm font-semibold text-slate-500">04. Itinerary</button>
        <button data-tab="included" class="tour-tab px-5 py-4 border-b-2 border-transparent text-sm font-semibold text-slate-500">05. Included</button>
        <button data-tab="faq" class="tour-tab px-5 py-4 border-b-2 border-transparent text-sm font-semibold text-slate-500">06. FAQ</button>
        <button data-tab="seo" class="tour-tab px-5 py-4 border-b-2 border-transparent text-sm font-semibold text-slate-500">07. SEO</button>
    </div>
</div>

<form id="tourForm" method="POST" action="{{ route('admin.tours.store') }}" enctype="multipart/form-data">
@csrf

<div class="grid xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-6">

        <section data-tab-section="basic" class="tab-section bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-7">
            <div class="mb-6">
                <h2 class="text-lg font-extrabold">Basic Information</h2>
                <p class="text-xs text-slate-500 mt-1">Tour-এর মূল information দিন।</p>
            </div>
            <div class="grid md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="label">Tour Name <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="যেমন: কক্সবাজার ৩ দিন ২ রাত" class="input" value="{{ old('title') }}">
                </div>
                <div>
                    <label class="label">Short Title</label>
                    <input type="text" name="short_title" placeholder="যেমন: Cox's Bazar Escape" class="input" value="{{ old('short_title') }}">
                </div>
                <div>
                    <label class="label">Category <span class="text-red-500">*</span></label>
                    <select name="category" class="input" required>
                        @foreach (\App\Models\Tour::CATEGORIES as $category)
                            <option value="{{ $category }}" @selected(old('category') === $category)>
                                {{ ucfirst($category) }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="label">Destination <span class="text-red-500">*</span></label>
                    <input type="text" name="destination" required placeholder="গন্তব্য" class="input" value="{{ old('destination') }}">
                </div>
                <div>
                    <label class="label">Country</label>
                    <select name="country" class="input">
                        <option {{ old('country')=='বাংলাদেশ'?'selected':'' }}>বাংলাদেশ</option>
                        <option {{ old('country')=='Malaysia'?'selected':'' }}>Malaysia</option>
                        <option {{ old('country')=='Thailand'?'selected':'' }}>Thailand</option>
                        <option {{ old('country')=='Indonesia'?'selected':'' }}>Indonesia</option>
                        <option {{ old('country')=='Singapore'?'selected':'' }}>Singapore</option>
                        <option {{ old('country')=='India'?'selected':'' }}>India</option>
                        <option {{ old('country')=='Nepal'?'selected':'' }}>Nepal</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="label">Description</label>
                    <textarea name="description" rows="6" maxlength="3000" placeholder="Tour সম্পর্কে বিস্তারিত লিখুন..." class="input resize-none">{{ old('description') }}</textarea>
                    <div class="text-right text-xs text-slate-400 mt-1"><span id="descCount">0</span> / 3000</div>
                </div>
            </div>
        </section>

        <section data-tab-section="basic" class="tab-section bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-7">
            <div class="mb-6"><h2 class="text-lg font-extrabold">Date & Duration</h2></div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div>
                    <label class="label">Departure Date</label>
                    <input type="date" name="departure_date" class="input" value="{{ old('departure_date') }}">
                </div>
                <div>
                    <label class="label">Return Date</label>
                    <input type="date" name="return_date" class="input" value="{{ old('return_date') }}">
                </div>
                <div>
                    <label class="label">Days</label>
                    <input type="number" name="days" min="1" class="input" value="{{ old('days', 3) }}">
                </div>
                <div>
                    <label class="label">Nights</label>
                    <input type="number" name="nights" min="0" class="input" value="{{ old('nights', 2) }}">
                </div>
            </div>
            <div class="mt-5 grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="label">Departure Location</label>
                    <input type="text" name="departure_location" placeholder="যাত্রা শুরু" class="input" value="{{ old('departure_location', 'ঢাকা') }}">
                </div>
                <div>
                    <label class="label">Meeting Point</label>
                    <input type="text" name="meeting_point" placeholder="Meeting point" class="input" value="{{ old('meeting_point') }}">
                </div>
                <div>
                    <label class="label">Transport Type</label>
                    @php $transportValue = old('transport_type', 'AC Bus'); @endphp
                    <select name="transport_type" class="input">
                        <option value="" @selected(! $transportValue)>— নির্বাচন করুন —</option>
                        @foreach (\App\Models\Tour::transportOptions($transportValue) as $option)
                            <option value="{{ $option }}" @selected($transportValue === $option)>
                                {{ \App\Models\Tour::transportIconFor($option) }} {{ $option }}
                            </option>
                        @endforeach
                    </select>
                    @error('transport_type')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section data-tab-section="pricing" class="tab-section bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-7 hidden">
            <div class="mb-6"><h2 class="text-lg font-extrabold">Pricing & Capacity</h2></div>
            <div class="grid md:grid-cols-3 gap-5">
                <div>
                    <label class="label">Price / Person (৳)</label>
                    <input type="number" name="price_per_person" min="0" class="input" value="{{ old('price_per_person') }}">
                </div>
                <div>
                    <label class="label">Maximum Slots</label>
                    <input type="number" name="max_slots" min="1" class="input" value="{{ old('max_slots', 30) }}">
                </div>
                <div>
                    <label class="label">Current Booked</label>
                    <input type="number" name="current_booked" min="0" class="input bg-slate-100 dark:bg-slate-800" value="{{ old('current_booked', 0) }}">
                </div>
            </div>
        </section>

        <section data-tab-section="itinerary" class="tab-section bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-7 hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-extrabold">Tour Itinerary</h2>
                    <p class="text-xs text-slate-500 mt-1">প্রতিদিনের সময়সূচি এবং activities যোগ করুন।</p>
                </div>
                <button type="button" onclick="addDay()" class="px-4 py-2.5 rounded-xl bg-teal-700 text-white text-sm font-bold">+ Add Day</button>
            </div>
            <div id="itineraryContainer"></div>
        </section>

        <section data-tab-section="included" class="tab-section bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-7 hidden">
            <div class="mb-6"><h2 class="text-lg font-extrabold">Included & Excluded</h2></div>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-green-600">✓ Included</h3>
                        <button type="button" onclick="addRow('includesContainer', 'includes[]')" class="text-sm text-teal-700 font-bold">+ Add</button>
                    </div>
                    <div id="includesContainer" class="space-y-2"></div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-red-500">× Excluded</h3>
                        <button type="button" onclick="addRow('excludesContainer', 'excludes[]')" class="text-sm text-teal-700 font-bold">+ Add</button>
                    </div>
                    <div id="excludesContainer" class="space-y-2"></div>
                </div>
            </div>
            <div class="mt-8">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h2 class="text-lg font-extrabold">Important Information</h2>
                        <p class="text-xs text-slate-500">Traveller-দের জানানো প্রয়োজন এমন তথ্য।</p>
                    </div>
                    <button type="button" onclick="addRow('impContainer', 'important_info[]')" class="text-teal-700 text-sm font-bold">+ Add</button>
                </div>
                <div id="impContainer" class="space-y-3"></div>
            </div>
        </section>

        <section data-tab-section="faq" class="tab-section bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-7 hidden">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-lg font-extrabold">Frequently Asked Questions</h2>
                    <p class="text-xs text-slate-500 mt-1">Tour-specific FAQ যোগ করুন।</p>
                </div>
                <button type="button" onclick="addFaq()" class="px-3 py-2 rounded-xl bg-teal-700 text-white text-sm font-bold">+ Add FAQ</button>
            </div>
            <div id="faqContainer" class="space-y-4"></div>
        </section>

        <section data-tab-section="seo" class="tab-section bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-7 hidden">
            <h2 class="text-lg font-extrabold mb-6">SEO Settings</h2>
            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label class="label">Meta Title</label>
                    <input type="text" name="meta_title" placeholder="Cox's Bazar Tour Package..." class="input" value="{{ old('meta_title') }}">
                </div>
                <div>
                    <label class="label">Meta Keywords</label>
                    <input type="text" name="meta_keywords" placeholder="cox bazar tour, travel..." class="input" value="{{ old('meta_keywords') }}">
                </div>
                <div class="md:col-span-2">
                    <label class="label">Meta Description</label>
                    <textarea name="meta_description" rows="4" class="input resize-none" placeholder="Search engine description...">{{ old('meta_description') }}</textarea>
                </div>
            </div>
        </section>

        <section data-tab-section="media" class="tab-section bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-7 hidden">
            @include('admin.tours.partials.image-uploader', ['tour' => null])
        </section>

    </div>

    <aside class="space-y-6">
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <h2 class="font-extrabold">Publish</h2>
            <div class="mt-5">
                <label class="label">Status</label>
                <select name="status" class="input">
                    @foreach (\App\Models\Tour::STATUSES as $option)
                        <option value="{{ $option }}" @selected(old('status') === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-5">
                <label class="flex items-center justify-between cursor-pointer">
                    <span class="text-sm font-semibold">Featured Tour</span>
                    <input type="checkbox" name="is_featured" value="1" class="w-5 h-5 accent-teal-700" {{ old('is_featured')?'checked':'' }}>
                </label>
            </div>
            <button type="submit" class="w-full mt-6 bg-teal-700 hover:bg-teal-800 text-white py-3.5 rounded-xl font-bold">Publish Tour</button>
        </section>

        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <h2 class="font-extrabold">Pricing & Availability</h2>
            <div class="mt-5">
                <label class="label">Price / Person</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">৳</span>
                    <input type="number" name="price_per_person_sidebar" min="0" class="input pl-9" value="{{ old('price_per_person_sidebar') }}" oninput="document.querySelector('[name=price_per_person]').value=this.value">
                </div>
            </div>
            <div class="mt-4">
                <label class="label">Maximum Slots</label>
                <input type="number" name="max_slots_sidebar" min="1" class="input" value="{{ old('max_slots_sidebar', 30) }}" oninput="document.querySelector('[name=max_slots]').value=this.value; updateAvailable();">
            </div>
            <div class="mt-4">
                <label class="label">Current Booked</label>
                <input type="number" name="current_booked_sidebar" min="0" readonly class="input bg-slate-100 dark:bg-slate-800" value="{{ old('current_booked_sidebar', 0) }}" oninput="document.querySelector('[name=current_booked]').value=this.value; updateAvailable();">
            </div>
            <div class="mt-5 bg-teal-50 dark:bg-teal-950 rounded-xl p-4">
                <div class="text-xs text-slate-500">Available Slots</div>
                <div id="availableSlots" class="text-2xl font-extrabold text-teal-700 mt-1">30</div>
            </div>
        </section>

        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <h2 class="font-extrabold mb-5">Tour Features</h2>
            <div class="space-y-3">
                <label class="flex gap-3 items-center text-sm">
                    <input type="checkbox" name="features[]" value="Hotel Included" class="w-4 h-4 accent-teal-700" {{ is_array(old('features')) && in_array('Hotel Included', old('features')) ? 'checked' : '' }}>
                    Hotel Included
                </label>
                <label class="flex gap-3 items-center text-sm">
                    <input type="checkbox" name="features[]" value="Transport Included" class="w-4 h-4 accent-teal-700" {{ is_array(old('features')) && in_array('Transport Included', old('features')) ? 'checked' : '' }}>
                    Transport Included
                </label>
                <label class="flex gap-3 items-center text-sm">
                    <input type="checkbox" name="features[]" value="Breakfast Included" class="w-4 h-4 accent-teal-700" {{ is_array(old('features')) && in_array('Breakfast Included', old('features')) ? 'checked' : '' }}>
                    Breakfast Included
                </label>
                <label class="flex gap-3 items-center text-sm">
                    <input type="checkbox" name="features[]" value="Tour Guide" class="w-4 h-4 accent-teal-700" {{ is_array(old('features')) && in_array('Tour Guide', old('features')) ? 'checked' : '' }}>
                    Tour Guide
                </label>
                <label class="flex gap-3 items-center text-sm">
                    <input type="checkbox" name="features[]" value="Airport Pickup" class="w-4 h-4 accent-teal-700" {{ is_array(old('features')) && in_array('Airport Pickup', old('features')) ? 'checked' : '' }}>
                    Airport Pickup
                </label>
            </div>
        </section>
    </aside>
</div>

</form>

@endsection

@section('mobile-actions')
<div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 p-3">
    <div class="flex gap-2">
        <button type="button" onclick="document.getElementById('tourForm').submit();" class="flex-1 border border-slate-300 dark:border-slate-700 rounded-xl py-3 font-semibold">Save Draft</button>
        <button type="submit" form="tourForm" class="flex-1 bg-teal-700 text-white rounded-xl py-3 font-bold">Publish</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const tabs = document.querySelectorAll('.tour-tab');
    const sections = document.querySelectorAll('.tab-section');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            tabs.forEach(t => {
                t.classList.remove('tab-active');
                t.classList.add('text-slate-500', 'border-transparent');
            });
            tab.classList.add('tab-active');
            tab.classList.remove('text-slate-500', 'border-transparent');
            sections.forEach(s => {
                if (s.dataset.tabSection === target) s.classList.remove('hidden');
                else s.classList.add('hidden');
            });
        });
    });

    const descTa = document.querySelector('[name=description]');
    if (descTa) {
        descTa.addEventListener('input', () => {
            document.getElementById('descCount').textContent = descTa.value.length;
        });
        document.getElementById('descCount').textContent = descTa.value.length;
    }

    function updateAvailable() {
        const max = parseInt(document.querySelector('[name=max_slots_sidebar]').value || 0);
        const cur = parseInt(document.querySelector('[name=current_booked_sidebar]').value || 0);
        document.getElementById('availableSlots').textContent = Math.max(0, max - cur);
    }

    const iconOptions = ['📍','🚌','🍛','🏨','🌊','🚤','🏝️','🛍️','🌅','🍳','🗼','🎭'];
    let dayCounter = 0;

    function activityTemplate(dayIdx, actIdx, data={}) {
        const opts = iconOptions.map(i => `<option ${data.icon===i?'selected':''}>${i}</option>`).join('');
        return `
        <div class="activity border border-slate-200 dark:border-slate-700 rounded-xl p-4">
            <div class="grid md:grid-cols-12 gap-3">
                <div class="md:col-span-2">
                    <label class="small-label">Time</label>
                    <input type="time" name="itinerary[${dayIdx}][activities][${actIdx}][time]" value="${data.time||''}" class="input">
                </div>
                <div class="md:col-span-1">
                    <label class="small-label">Icon</label>
                    <select name="itinerary[${dayIdx}][activities][${actIdx}][icon]" class="input">${opts}</select>
                </div>
                <div class="md:col-span-4">
                    <label class="small-label">Activity</label>
                    <input type="text" name="itinerary[${dayIdx}][activities][${actIdx}][title]" value="${data.title||''}" placeholder="Activity name" class="input">
                </div>
                <div class="md:col-span-3">
                    <label class="small-label">Location</label>
                    <input type="text" name="itinerary[${dayIdx}][activities][${actIdx}][location]" value="${data.location||''}" placeholder="Location" class="input">
                </div>
                <div class="md:col-span-2 flex items-end">
                    <button type="button" onclick="this.closest('.activity').remove()" class="w-full py-2.5 rounded-xl bg-red-50 dark:bg-red-950 text-red-600">🗑 Remove</button>
                </div>
                <div class="md:col-span-12">
                    <label class="small-label">Description</label>
                    <textarea rows="2" name="itinerary[${dayIdx}][activities][${actIdx}][description]" placeholder="Activity সম্পর্কে..." class="input resize-none">${data.description||''}</textarea>
                </div>
            </div>
        </div>`;
    }

    function addDay(prefill=null) {
        const container = document.getElementById('itineraryContainer');
        const dayNumber = container.querySelectorAll('.day-card').length + 1;
        const idx = dayCounter++;
        const title = prefill?.day_title || `Day ${dayNumber} Title`;
        const activities = prefill?.activities || [{time:'', icon:'📍', title:'', location:'', description:''}];
        const day = document.createElement('div');
        day.className = 'day-card border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden mb-5';
        let actsHtml = '';
        activities.forEach((a, ai) => { actsHtml += activityTemplate(idx, ai, a); });
        day.innerHTML = `
            <div class="bg-slate-50 dark:bg-slate-800 px-5 py-4 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-teal-700 text-white flex items-center justify-center font-bold day-num">${dayNumber}</div>
                    <div>
                        <input type="text" name="itinerary[${idx}][day_title]" value="${title}" class="bg-transparent font-bold outline-none">
                        <div class="text-[11px] text-slate-500">Day ${dayNumber}</div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="px-3 py-1.5 rounded-lg text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700">Duplicate</button>
                    <button type="button" onclick="this.closest('.day-card').remove(); renumDays();" class="px-3 py-1.5 rounded-lg text-xs text-red-500 bg-red-50 dark:bg-red-950">Delete</button>
                </div>
            </div>
            <div class="p-5">
                <div class="activity-container space-y-4">${actsHtml}</div>
                <button type="button" onclick="addActivity(this, ${idx})" class="mt-4 w-full border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl py-3 text-sm font-bold text-teal-700">+ Add Activity</button>
            </div>`;
        container.appendChild(day);
    }

    function addActivity(btn, dayIdx) {
        const container = btn.previousElementSibling;
        const actIdx = container.querySelectorAll('.activity').length;
        container.insertAdjacentHTML('beforeend', activityTemplate(dayIdx, actIdx));
    }

    function renumDays() {
        document.querySelectorAll('#itineraryContainer .day-num').forEach((el, i) => el.textContent = i + 1);
    }

    function addRow(containerId, name, prefill='') {
        const c = document.getElementById(containerId);
        const row = document.createElement('div');
        row.className = 'flex gap-2';
        row.innerHTML = `
            <input type="text" name="${name}" value="${prefill}" class="input flex-1" placeholder="Type here...">
            <button type="button" onclick="this.parentElement.remove()" class="w-11 rounded-xl bg-red-50 text-red-500">×</button>`;
        c.appendChild(row);
    }

    function addFaq(prefill=null) {
        const c = document.getElementById('faqContainer');
        const idx = c.children.length;
        const div = document.createElement('div');
        div.className = 'border border-slate-200 dark:border-slate-700 rounded-xl p-5';
        div.innerHTML = `
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="small-label">Question</label>
                    <input type="text" name="faqs[${idx}][question]" value="${prefill?.question||''}" class="input" placeholder="Question...">
                </div>
                <div>
                    <label class="small-label">Answer</label>
                    <input type="text" name="faqs[${idx}][answer]" value="${prefill?.answer||''}" class="input" placeholder="Answer...">
                </div>
            </div>
            <button type="button" onclick="this.closest('.border').remove(); reindexFaqs();" class="text-red-500 text-xs font-bold mt-4">Remove FAQ</button>`;
        c.appendChild(div);
    }

    function reindexFaqs() {
        document.querySelectorAll('#faqContainer > div').forEach((div, i) => {
            div.querySelectorAll('input').forEach((inp, j) => {
                inp.name = `faqs[${i}][${j===0?'question':'answer'}]`;
            });
        });
    }

    addDay();
    addRow('includesContainer', 'includes[]', 'AC Bus Transport');
    addRow('includesContainer', 'includes[]', '২ রাত Hotel');
    addRow('includesContainer', 'includes[]', 'Daily Breakfast');
    addRow('excludesContainer', 'excludes[]', 'Personal Shopping');
    addRow('excludesContainer', 'excludes[]', 'Lunch & Dinner');
    addRow('impContainer', 'important_info[]', 'যাত্রার ৩০ মিনিট আগে উপস্থিত হতে হবে।');
    addRow('impContainer', 'important_info[]', 'আবহাওয়ার কারণে itinerary পরিবর্তিত হতে পারে।');
    addFaq({question: 'Booking করার পর confirmation পাব?', answer: 'Payment successful হওয়ার পর confirmation পাঠানো হবে।'});
</script>
@endsection
