@php
    /**
     * @param  string  $name     Input name, also the setting key.
     * @param  string  $label
     * @param  string|null  $url  Currently stored image URL, or null.
     * @param  string  $hint
     * @param  string  $shape   Tailwind sizing class for the preview box.
     */
    $id = 'img-'.$name;
@endphp

<div class="border border-slate-200 dark:border-slate-700 rounded-2xl p-5">
    <label class="label" for="{{ $id }}">{{ $label }}</label>

    <div class="flex items-start gap-4">
        <div class="{{ $shape }} shrink-0 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
            @if ($url)
                <img src="{{ $url }}" alt="{{ $label }}" class="w-full h-full object-contain">
            @else
                <span class="text-xs text-slate-400 text-center px-2">কোনো ইমেজ নেই</span>
            @endif
        </div>

        <div class="flex-1 min-w-0">
            <input id="{{ $id }}" type="file" name="{{ $name }}" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/x-icon"
                class="input text-xs py-2">

            <p class="text-xs text-slate-500 mt-2 leading-6">{{ $hint }}</p>

            @if ($url)
                <label class="inline-flex items-center gap-2 mt-3 text-xs font-semibold text-red-600 cursor-pointer">
                    <input type="checkbox" name="remove_{{ $name }}" value="1" class="rounded border-slate-300">
                    বর্তমান ইমেজটি সরান
                </label>
            @endif

            @error($name)
                <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
