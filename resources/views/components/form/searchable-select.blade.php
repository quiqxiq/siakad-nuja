@props([
    'label' => null,
    'name',
    'options' => [], // array of ['id' => ..., 'label' => ..., 'sublabel' => ..., 'kelas_id' => ...]
    'selected' => '',
    'required' => false,
    'placeholder' => '— Cari & Pilih Siswa —',
    'hint' => null,
    'watchKelas' => false, // jika true, otomatis menyaring opsi berdasarkan selectedKelas dari x-data luar
])

<div x-data="{
    open: false,
    search: '',
    selectedId: '{{ old($name, $selected) }}',
    selectedLabel: '',
    options: {{ json_encode($options) }},
    watchKelas: {{ $watchKelas ? 'true' : 'false' }},
    init() {
        this.updateSelectedLabel();
        if (this.watchKelas && typeof this.selectedKelas !== 'undefined') {
            this.$watch('selectedKelas', (newVal) => {
                if (newVal) {
                    const found = this.options.find(o => String(o.id) === String(this.selectedId));
                    if (found && String(found.kelas_id) !== String(newVal)) {
                        this.clear();
                    }
                }
            });
        }
    },
    updateSelectedLabel() {
        const found = this.options.find(o => String(o.id) === String(this.selectedId));
        if (found) {
            this.selectedLabel = found.label + (found.sublabel ? ' (' + found.sublabel + ')' : '');
        } else {
            this.selectedLabel = '';
        }
    },
    get filteredOptions() {
        let list = this.options;
        if (this.watchKelas && typeof this.selectedKelas !== 'undefined' && this.selectedKelas) {
            list = list.filter(o => String(o.kelas_id) === String(this.selectedKelas));
        }
        if (!this.search.trim()) return list;
        const q = this.search.toLowerCase().trim();
        return list.filter(o => {
            const l = (o.label || '').toLowerCase();
            const s = (o.sublabel || '').toLowerCase();
            return l.includes(q) || s.includes(q);
        });
    },
    select(opt) {
        this.selectedId = opt.id;
        this.selectedLabel = opt.label + (opt.sublabel ? ' (' + opt.sublabel + ')' : '');
        this.open = false;
        this.search = '';
        $dispatch('input', this.selectedId);
        $dispatch('change', this.selectedId);
    },
    clear() {
        this.selectedId = '';
        this.selectedLabel = '';
        this.search = '';
        $dispatch('input', '');
        $dispatch('change', '');
    }
}"
@click.outside="open = false"
class="relative w-full">
    @if ($label)
        <label for="{{ $name }}_search" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
            {{ $label }}
            @if ($required) <span class="text-rose-500">*</span> @endif
        </label>
    @endif

    {{-- Hidden input for real form submission --}}
    <input type="hidden" name="{{ $name }}" id="{{ $name }}" :value="selectedId" @if($required) required @endif>

    {{-- Trigger display box --}}
    <div @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())"
        class="flex items-center justify-between gap-2 w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white shadow-sm text-sm px-4 py-3 cursor-pointer hover:border-brand-500 transition {{ $errors->has($name) ? 'border-rose-400 ring-2 ring-rose-400/20' : '' }}"
        :class="open ? 'border-brand-500 ring-2 ring-brand-500/20' : ''">
        <div class="flex items-center gap-2 truncate">
            <x-icon name="search" class="h-4 w-4 shrink-0 text-slate-400" />
            <span x-show="selectedLabel" x-text="selectedLabel" class="truncate font-medium text-slate-900 dark:text-white"></span>
            <span x-show="!selectedLabel" class="text-slate-400 dark:text-slate-500">{{ $placeholder }}</span>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            <button type="button" x-show="selectedId" @click.stop="clear()" class="p-0.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" title="Hapus pilihan">
                <x-icon name="close" class="h-3.5 w-3.5" />
            </button>
            <x-icon name="chevron-down" class="h-4 w-4 text-slate-400 transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
        </div>
    </div>

    {{-- Dropdown popover panel --}}
    <div x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute left-0 right-0 z-50 mt-1.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5 shadow-xl ring-1 ring-black/5"
        style="display: none;">
        
        {{-- Search input inside popover --}}
        <div class="relative mb-2">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="text"
                x-ref="searchInput"
                x-model="search"
                @keydown.escape="open = false"
                placeholder="Ketik nama atau NIS untuk mencari..."
                class="block w-full rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white pl-9 pr-3 py-2 text-xs shadow-inner focus:border-brand-500 focus:ring-brand-500">
        </div>

        {{-- Scrollable List --}}
        <div class="max-h-56 overflow-y-auto space-y-0.5 rounded-lg pr-1">
            <template x-for="opt in filteredOptions" :key="opt.id">
                <div @click="select(opt)"
                    class="flex items-center justify-between gap-3 px-3 py-2 rounded-xl text-xs cursor-pointer transition hover:bg-brand-50 dark:hover:bg-slate-700/60"
                    :class="String(opt.id) === String(selectedId) ? 'bg-brand-50 dark:bg-slate-700/80 text-brand-700 dark:text-brand-300 font-semibold' : 'text-slate-800 dark:text-slate-200'">
                    <div class="min-w-0">
                        <p class="truncate font-medium text-slate-900 dark:text-white" x-text="opt.label"></p>
                        <p x-show="opt.sublabel" class="text-[11px] text-slate-400 truncate" x-text="opt.sublabel"></p>
                    </div>
                    <div x-show="String(opt.id) === String(selectedId)" class="shrink-0 text-brand-600 dark:text-brand-400">
                        <x-icon name="check" class="h-4 w-4" />
                    </div>
                </div>
            </template>
            <div x-show="filteredOptions.length === 0" class="py-6 text-center text-xs text-slate-400">
                Tidak ada siswa yang cocok dengan pencarian
            </div>
        </div>
    </div>

    @if ($hint && ! $errors->has($name))
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
    @enderror
</div>
