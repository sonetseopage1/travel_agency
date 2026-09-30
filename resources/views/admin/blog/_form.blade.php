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
            <h3 class="font-bold text-lg mb-5">পোস্টের কনটেন্ট</h3>

            <div class="space-y-5">
                <div>
                    <label class="label" for="title">শিরোনাম <span class="text-red-500">*</span></label>
                    <input id="title" name="title" value="{{ old('title', $post->title) }}" required class="input"
                        placeholder="যেমন: সাজেক ভ্যালির সেরা ৫টি দৃশ্য">
                    @error('title')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label" for="slug">স্লাগ</label>
                    <input id="slug" name="slug" value="{{ old('slug', $post->slug) }}" class="input"
                        placeholder="খালি রাখলে শিরোনাম থেকে তৈরি হবে">
                    <p class="mt-1.5 text-xs text-slate-500">শুধু ছোট হাতের অক্ষর, সংখ্যা এবং ড্যাশ ব্যবহার করুন।</p>
                    @error('slug')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label" for="excerpt">সংক্ষিপ্ত বিবরণ</label>
                    <textarea id="excerpt" name="excerpt" rows="3" class="input"
                        placeholder="লিস্টিং ও পোস্টের শুরুতে দেখানো সংক্ষিপ্ত লেখা">{{ old('excerpt', $post->excerpt) }}</textarea>
                    <p class="mt-1.5 text-xs text-slate-500">খালি রাখলে কনটেন্ট থেকে স্বয়ংক্রিয়ভাবে তৈরি হবে।</p>
                    @error('excerpt')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label" for="body">কনটেন্ট</label>
                    <textarea id="body" name="body" rows="16" class="input font-mono text-sm leading-7"
                        placeholder="প্যারাগ্রাফ আলাদা করতে একটি ফাঁকা লাইন দিন। প্যারাগ্রাফে HTML ব্যবহার করা যাবে।">{{ old('body', $post->body) }}</textarea>
                    @error('body')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <h3 class="font-bold text-lg mb-5">SEO</h3>

            <div class="space-y-5">
                <div>
                    <label class="label" for="meta_title">মেটা শিরোনাম</label>
                    <input id="meta_title" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}"
                        class="input" placeholder="খালি রাখলে পোস্টের শিরোনাম ব্যবহার হবে">
                    @error('meta_title')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label" for="meta_description">মেটা বিবরণ</label>
                    <textarea id="meta_description" name="meta_description" rows="3" class="input">{{ old('meta_description', $post->meta_description) }}</textarea>
                    @error('meta_description')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label" for="meta_keywords">মেটা কীওয়ার্ড</label>
                    <input id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $post->meta_keywords) }}"
                        class="input" placeholder="কমা দিয়ে আলাদা করুন">
                    @error('meta_keywords')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <aside class="space-y-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <h3 class="font-bold mb-4">প্রকাশ</h3>

            <div class="space-y-4">
                <div>
                    <label class="label" for="status">স্ট্যাটাস <span class="text-red-500">*</span></label>
                    <select id="status" name="status" class="input" required>
                        @foreach (\App\Models\BlogPost::STATUSES as $status)
                            <option value="{{ $status }}" @selected(old('status', $post->status ?? 'draft') === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-xs text-slate-500">Draft পোস্ট সাইটে দেখানো হয় না।</p>
                    @error('status')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label" for="published_at">প্রকাশের তারিখ</label>
                    <input id="published_at" name="published_at" type="datetime-local"
                        value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}" class="input">
                    <p class="mt-1.5 text-xs text-slate-500">
                        ভবিষ্যতের তারিখ দিলে পোস্টটি তারিখ পর্যন্ত প্রকাশিত হবে না।
                    </p>
                    @error('published_at')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $post->is_featured ?? false))
                        class="w-5 h-5 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                    <span class="text-sm font-semibold">ফিচার্ড পোস্ট করুন</span>
                </label>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <h3 class="font-bold mb-4">কভার ছবি</h3>
            <input name="cover_image" type="file" accept="image/*" class="input">
            <p class="mt-1.5 text-xs text-slate-500">JPG, PNG, WEBP, GIF · সর্বোচ্চ ৪ মেগাবাইট।</p>
            @error('cover_image')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror

            @if ($post->exists && $post->cover_image)
                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}"
                    class="w-full mt-4 rounded-xl aspect-video object-cover bg-slate-100 dark:bg-slate-800">
            @endif
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <div>
                <label class="label" for="category">ক্যাটাগরি</label>
                <input id="category" name="category" value="{{ old('category', $post->category) }}" class="input"
                    placeholder="যেমন: গন্তব্য গাইড">
                @error('category')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div class="mt-4">
                <label class="label" for="author">লেখক</label>
                <input id="author" name="author" value="{{ old('author', $post->author) }}" class="input"
                    placeholder="যেমন: {{ \App\Models\Setting::string('site_name') }}">
                @error('author')
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
            <a href="{{ route('admin.blog.index') }}"
                class="w-full mt-3 block text-center border border-slate-300 dark:border-slate-700 font-semibold py-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                Cancel
            </a>
        </div>
    </aside>
</div>
