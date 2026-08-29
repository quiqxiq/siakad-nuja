@props(['label' => null, 'name', 'value' => '', 'type' => 'text', 'required' => false, 'hint' => null])

<div @if($type === 'password') x-data="{ show: false }" @endif>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
            {{ $label }}
            @if ($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    <div class="relative">
        <input
            @if($type === 'password') :type="show ? 'text' : 'password'" type="password" @else type="{{ $type }}" @endif
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            @if ($required) required @endif
            {{ $attributes->merge(['class' => 'block w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white shadow-sm text-sm px-4 py-3 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition placeholder:text-slate-400 ' . ($type === 'password' ? 'pr-11 ' : '') . ($errors->has($name) ? 'border-red-400 ring-2 ring-red-400/20' : '')]) }}
        >

        @if ($type === 'password')
            <button
                type="button"
                @click="show = !show"
                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none transition-colors"
                :title="show ? 'Sembunyikan password' : 'Lihat password'"
                tabindex="-1"
                aria-label="Tampilkan atau sembunyikan password"
            >
                <span x-show="!show" class="flex items-center">
                    <x-icon name="eye" class="h-5 w-5" />
                </span>
                <span x-show="show" x-cloak class="flex items-center" style="display:none">
                    <x-icon name="eye-slash" class="h-5 w-5" />
                </span>
            </button>
        @endif
    </div>

    @if ($hint && ! $errors->has($name))
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

