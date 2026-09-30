@php
    /**
     * Image uploader for tour cover + gallery.
     *
     * @var \App\Models\Tour|null $tour  Pass the tour on edit, omit on create.
     */
    $tour = $tour ?? null;
    $existingGallery = $tour ? array_values(array_filter((array) $tour->gallery)) : [];
    $maxGallery = \App\Services\TourImageService::MAX_GALLERY_IMAGES;
    $accept = 'image/jpeg,image/png,image/webp';
@endphp

<div class="space-y-8" id="imageUploader"
    data-max-gallery="{{ $maxGallery }}"
    data-placeholder="{{ asset('images/placeholder.svg') }}">

    {{-- ---------------- Cover image ---------------- --}}
    <div>
        <h2 class="text-lg font-extrabold">কভার ইমেজ</h2>
        <p class="text-xs text-slate-500 mt-1">ট্যুরের প্রচারের জন্য প্রধান ছবি। JPG, PNG বা WEBP (সর্বোচ্চ ৪ MB)।</p>
    </div>

    <input type="file" id="coverInput" name="cover_image" accept="{{ $accept }}" class="hidden">
    <input type="hidden" name="remove_cover" id="removeCoverFlag" value="0">

    <div id="coverZone"
        class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-5 cursor-pointer hover:border-teal-500 transition-colors">
        <div class="text-center py-6">
            <div class="text-4xl mb-2">🖼️</div>
            <p class="text-sm font-semibold">ইমেজটি এখানে টেনে ছেড়ে দিন</p>
            <p class="text-xs text-slate-500 mt-1">অথবা ক্লিক করে কম্পিউটার থেকে বেছে নিন</p>
        </div>
    </div>

    <div id="coverPreviewWrap" class="relative h-52 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800 {{ ($tour && $tour->cover_image) || old('cover_image') ? '' : 'hidden' }}">
        <img id="coverPreview"
            src="{{ $tour?->cover_image ? $tour->image_url : (old('cover_image') ? \App\Models\Tour::resolveImageUrl(old('cover_image')) : '') }}"
            class="w-full h-full object-cover" alt="কভার প্রিভিউ">
        <div class="absolute top-3 right-3 flex gap-2">
            <button type="button" data-action="replace-cover"
                class="px-3 py-1.5 rounded-lg bg-black/60 text-white text-xs font-semibold hover:bg-black/75">
                পরিবর্তন
            </button>
            <button type="button" data-action="remove-cover"
                class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-700">
                সরান
            </button>
        </div>
        <p id="coverFileName" class="absolute bottom-0 inset-x-0 bg-black/60 text-white text-xs px-3 py-1.5 truncate hidden"></p>
    </div>

    {{-- ---------------- Gallery ---------------- --}}
    <div>
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold">গ্যালারি</h2>
                <p class="text-xs text-slate-500 mt-1">একসাথে একাধিক ইমেজ আপলোড করতে পারেন (সর্বোচ্চ {{ $maxGallery }}টি)।</p>
            </div>
            <span class="text-xs font-semibold text-slate-500 shrink-0">
                <span id="galleryCount">{{ count($existingGallery) }}</span>/{{ $maxGallery }}
            </span>
        </div>
    </div>

    <input type="file" id="galleryInput" name="gallery_files[]" accept="{{ $accept }}" multiple class="hidden">

    <div id="galleryZone"
        class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-5 cursor-pointer hover:border-teal-500 transition-colors">
        <div class="text-center py-6">
            <div class="text-4xl mb-2">🖼️🖼️</div>
            <p class="text-sm font-semibold">একাধিক ইমেজ এখানে টেনে ছেড়ে দিন</p>
            <p class="text-xs text-slate-500 mt-1">অথবা ক্লিক করে ফাইল বেছে নিন</p>
        </div>
    </div>

    <div id="galleryGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 {{ count($existingGallery) ? '' : 'hidden' }}">
        @foreach ($existingGallery as $path)
            <div class="relative group rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 aspect-[4/3]"
                data-existing-image="{{ $path }}">
                <img src="{{ \App\Models\Tour::resolveImageUrl($path) }}" alt="গ্যালারি"
                    class="w-full h-full object-cover">
                <input type="hidden" name="existing_gallery[]" value="{{ $path }}">
                <button type="button" data-action="remove-image"
                    class="absolute top-2 right-2 w-8 h-8 rounded-lg bg-black/60 text-white text-xs font-bold opacity-0 group-hover:opacity-100 transition">
                    ✕
                </button>
            </div>
        @endforeach
    </div>

    <p id="galleryError" class="text-xs text-red-500 hidden"></p>
</div>

<script>
    (function () {
        const root = document.getElementById('imageUploader');
        if (!root) return;

        const MAX_GALLERY = parseInt(root.dataset.maxGallery, 10) || 12;
        const ACCEPTED = ['image/jpeg', 'image/png', 'image/webp'];
        const MAX_BYTES = 4 * 1024 * 1024;

        const coverInput = document.getElementById('coverInput');
        const coverZone = document.getElementById('coverZone');
        const coverPreview = document.getElementById('coverPreview');
        const coverPreviewWrap = document.getElementById('coverPreviewWrap');
        const coverFileName = document.getElementById('coverFileName');
        const removeCoverFlag = document.getElementById('removeCoverFlag');

        const galleryInput = document.getElementById('galleryInput');
        const galleryZone = document.getElementById('galleryZone');
        const galleryGrid = document.getElementById('galleryGrid');
        const galleryCount = document.getElementById('galleryCount');
        const galleryError = document.getElementById('galleryError');

        // Newly picked files, tracked manually so each can be removed again.
        let pending = [];
        // Guard against a re-submit wiping the pending list.
        let submitted = false;

        const existingCount = () => root.querySelectorAll('[data-existing-image]').length;

        const updateGalleryCount = () => {
            galleryCount.textContent = existingCount() + pending.length;
        };

        const showGalleryError = (message) => {
            if (!message) {
                galleryError.classList.add('hidden');
                return;
            }
            galleryError.textContent = message;
            galleryError.classList.remove('hidden');
        };

        function validateFiles(files) {
            const rejected = [];

            for (const file of files) {
                if (!ACCEPTED.includes(file.type)) {
                    rejected.push(`${file.name} (অসমর্থিত ফরম্যাট)`);
                } else if (file.size > MAX_BYTES) {
                    rejected.push(`${file.name} (৪ MB এর বেশি)`);
                }
            }

            return rejected;
        }

        function setCoverPreview(src, fileName) {
            coverPreview.src = src;
            coverPreviewWrap.classList.remove('hidden');
            coverZone.classList.add('hidden');
            removeCoverFlag.value = '0';

            if (fileName) {
                coverFileName.textContent = fileName;
                coverFileName.classList.remove('hidden');
            }
        }

        coverZone.addEventListener('click', () => coverInput.click());
        root.querySelector('[data-action="replace-cover"]')?.addEventListener('click', (e) => {
            e.stopPropagation();
            coverInput.click();
        });

        root.querySelector('[data-action="remove-cover"]')?.addEventListener('click', (e) => {
            e.stopPropagation();
            coverInput.value = '';
            coverPreview.removeAttribute('src');
            coverPreviewWrap.classList.add('hidden');
            coverZone.classList.remove('hidden');
            coverFileName.classList.add('hidden');
            removeCoverFlag.value = '1';
        });

        coverInput.addEventListener('change', () => {
            const file = coverInput.files[0];
            if (!file) return;

            const rejected = validateFiles([file]);
            if (rejected.length) {
                alert(rejected.join('\n'));
                coverInput.value = '';
                return;
            }

            setCoverPreview(URL.createObjectURL(file), file.name);
        });

        // ---- Gallery ----
        galleryZone.addEventListener('click', () => galleryInput.click());

        function addFiles(fileList) {
            const files = Array.from(fileList);
            const rejected = validateFiles(files);

            if (rejected.length) {
                showGalleryError(rejected.join(', '));
            } else {
                showGalleryError('');
            }

            const room = MAX_GALLERY - existingCount() - pending.length;
            const accepted = files.filter((file) => !rejected.includes(`${file.name} (অসমর্ণিত ফরম্যাট)`) && !rejected.includes(`${file.name} (৪ MB এর বেশি)`));

            if (accepted.length > room) {
                showGalleryError(`গ্যালারিতে আরও ${room}টির বেশি ইমেজ যোগ করা যাবে না।`);
            }

            accepted.slice(0, Math.max(room, 0)).forEach((file) => {
                pending.push({ file, url: URL.createObjectURL(file) });
                renderPending();
            });

            updateGalleryCount();
        }

        function renderPending() {
            pending.forEach((item, index) => {
                if (item.element) return;

                const wrap = document.createElement('div');
                wrap.className = 'relative group rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 aspect-[4/3]';
                wrap.innerHTML = `
                    <img src="${item.url}" alt="" class="w-full h-full object-cover">
                    <span class="absolute bottom-0 inset-x-0 bg-emerald-600/90 text-white text-[10px] px-2 py-1 truncate">নতুন</span>
                    <button type="button" class="absolute top-2 right-2 w-8 h-8 rounded-lg bg-black/60 text-white text-xs font-bold opacity-0 group-hover:opacity-100 transition">✕</button>
                `;

                wrap.querySelector('button').addEventListener('click', () => {
                    URL.revokeObjectURL(item.url);
                    pending.splice(index, 1);
                    renderPending();
                    updateGalleryCount();
                });

                item.element = wrap;
                galleryGrid.appendChild(wrap);
            });

            galleryGrid.classList.toggle('hidden', existingCount() + pending.length === 0);
        }

        galleryInput.addEventListener('change', () => {
            addFiles(galleryInput.files);
            galleryInput.value = '';
        });

        // Remove an already-saved gallery image: swap its keep-flag for a remove flag.
        galleryGrid.addEventListener('click', (e) => {
            const button = e.target.closest('[data-action="remove-image"]');
            if (!button) return;

            const wrapper = button.closest('[data-existing-image]');
            const keepInput = wrapper.querySelector('input[name="existing_gallery[]"]');
            if (!keepInput) return;

            const path = keepInput.value;
            keepInput.remove();

            const flag = document.createElement('input');
            flag.type = 'hidden';
            flag.name = 'remove_gallery[]';
            flag.value = path;
            wrapper.appendChild(flag);

            button.textContent = '↺';
            button.classList.remove('opacity-0');
            button.title = 'সংরক্ষণ করলে মুছে যাবে';

            updateGalleryCount();
        });

        // Wire up drag & drop on both zones.
        function wireDrop(zone, handler) {
            ['dragenter', 'dragover'].forEach((type) => {
                zone.addEventListener(type, (e) => {
                    e.preventDefault();
                    zone.classList.add('border-teal-500', 'bg-teal-50', 'dark:bg-teal-900/20');
                });
            });

            ['dragleave', 'drop'].forEach((type) => {
                zone.addEventListener(type, (e) => {
                    e.preventDefault();
                    zone.classList.remove('border-teal-500', 'bg-teal-50', 'dark:bg-teal-900/20');
                });
            });

            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                if (e.dataTransfer?.files?.length) handler(e.dataTransfer.files);
            });
        }

        wireDrop(coverZone, (files) => {
            const file = files[0];
            if (!file) return;
            coverInput.files = files;
            coverInput.dispatchEvent(new Event('change'));
        });

        wireDrop(galleryZone, addFiles);

        // Rebuild the gallery input's FileList from what is still pending, since
        // a file input cannot have individual entries removed.
        root.closest('form')?.addEventListener('submit', (e) => {
            if (submitted) return;
            submitted = true;

            if (pending.length === 0) return;

            if (typeof DataTransfer === 'undefined') {
                showGalleryError('এই ব্রাউজারে একাধিক ফাইল আপলোড সমর্থিত নয়। একটি করে যোগ করুন।');
                e.preventDefault();
                submitted = false;
                return;
            }

            const transfer = new DataTransfer();
            pending.forEach((item) => transfer.items.add(item.file));
            galleryInput.files = transfer.files;
        });

        updateGalleryCount();
    })();
</script>
