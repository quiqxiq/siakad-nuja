@props([
    'label' => null,
    'name',
    'options' => [], // array of ['id' => ..., 'label' => ..., 'sublabel' => ..., 'kelas_id' => ...]
    'selected' => '',
    'required' => false,
    'placeholder' => null,
    'searchPlaceholder' => null,
    'emptyText' => null,
    'hint' => null,
    'watchKelas' => false, // jika true, otomatis menyaring opsi berdasarkan selectedKelas dari x-data luar
    'optionsExpr' => null, // nama variabel Alpine di parent scope, misal 'availableMapels'
    'disabledExpr' => null, // nama/ekspresi kondisi disabled di Alpine, misal '!selectedKelas'
    'disabledPlaceholder' => null, // placeholder saat kondisi disabled
    'disabledHint' => null, // hint saat kondisi disabled
    'disabled' => false,
    'autoSubmit' => false,
])

@php
    $isSiswa = ($name === 'siswa_id' || str_contains(strtolower($label ?? ''), 'siswa'));
    $placeholder = $placeholder ?? ($isSiswa ? '— Cari & Pilih Siswa —' : '— Pilih Opsi —');
    $searchPlaceholder = $searchPlaceholder ?? ($isSiswa ? 'Ketik nama atau NIS untuk mencari siswa...' : 'Ketik untuk mencari...');
    $emptyText = $emptyText ?? ($isSiswa ? 'Tidak ada siswa yang cocok dengan pencarian' : 'Tidak ada data yang cocok dengan pencarian');
    $disabledPlaceholder = $disabledPlaceholder ?? $placeholder;
@endphp

<div x-data="{
    open: false,
    search: '',
    selectedId: '{{ old($name, $selected) }}',
    selectedLabel: '',
    staticOptions: {{ json_encode($options) }},
    optionsExpr: {{ $optionsExpr ? json_encode($optionsExpr) : 'null' }},
    disabledExpr: {{ $disabledExpr ? json_encode($disabledExpr) : 'null' }},
    staticDisabled: {{ $disabled ? 'true' : 'false' }},
    watchKelas: {{ $watchKelas ? 'true' : 'false' }},
    autoSubmit: {{ $autoSubmit ? 'true' : 'false' }},
    disabledPlaceholder: {{ json_encode($disabledPlaceholder) }},
    defaultPlaceholder: {{ json_encode($placeholder) }},
    disabledHint: {{ json_encode($disabledHint) }},

    get isDisabled() {
        if (this.staticDisabled) return true;
        if (this.disabledExpr) {
            if (this.disabledExpr === '!selectedKelas') {
                return typeof this.selectedKelas !== 'undefined' ? !this.selectedKelas : false;
            }
            if (this.disabledExpr.startsWith('!')) {
                const prop = this.disabledExpr.slice(1);
                return typeof this[prop] !== 'undefined' ? !this[prop] : true;
            }
            return typeof this[this.disabledExpr] !== 'undefined' ? Boolean(this[this.disabledExpr]) : false;
        }
        return false;
    },

    get rawOptions() {
        if (this.optionsExpr && typeof this[this.optionsExpr] !== 'undefined') {
            const dyn = this[this.optionsExpr];
            if (Array.isArray(dyn)) {
                return dyn.map(item => ({
                    id: item.id,
                    label: item.label || item.nama_mapel || item.nama_lengkap || item.nama || String(item.id),
                    sublabel: item.sublabel || (item.kkm ? 'KKM: ' + item.kkm : (item.kode_mapel ? 'Kode: ' + item.kode_mapel : '')),
                    kelas_id: item.kelas_id || null,
                }));
            }
        }
        return this.staticOptions;
    },

    get currentPlaceholder() {
        return this.isDisabled ? this.disabledPlaceholder : this.defaultPlaceholder;
    },

    init() {
        this.updateSelectedLabel();

        // Sinkronisasi dengan parent model (misal selectedMapel) jika ada
        if (this.optionsExpr === 'availableMapels' && typeof this.selectedMapel !== 'undefined') {
            if (this.selectedId) {
                this.selectedMapel = this.selectedId;
            } else if (this.selectedMapel) {
                this.selectedId = this.selectedMapel;
                this.updateSelectedLabel();
            }
        }

        // Watch perubahan kelas jika dynamic optionsExpr digunakan
        if (this.optionsExpr && typeof this.selectedKelas !== 'undefined') {
            this.$watch('selectedKelas', () => {
                this.$nextTick(() => {
                    this.updateSelectedLabel();
                    if (this.selectedId) {
                        const found = this.rawOptions.find(o => String(o.id) === String(this.selectedId));
                        if (!found) {
                            this.clear();
                        }
                    }
                });
            });
        }

        if (this.watchKelas && typeof this.selectedKelas !== 'undefined') {
            this.$watch('selectedKelas', (newVal) => {
                if (newVal) {
                    const found = this.rawOptions.find(o => String(o.id) === String(this.selectedId));
                    if (found && found.kelas_id && String(found.kelas_id) !== String(newVal)) {
                        this.clear();
                    }
                }
            });
        }
    },

    updateSelectedLabel() {
        const found = this.rawOptions.find(o => String(o.id) === String(this.selectedId));
        if (found) {
            this.selectedLabel = found.label + (found.sublabel ? ' (' + found.sublabel + ')' : '');
        } else {
            this.selectedLabel = '';
        }
    },

    get filteredOptions() {
        let list = this.rawOptions;
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

    toggleOpen() {
        if (this.isDisabled) return;
        this.open = !this.open;
        if (this.open) {
            this.$nextTick(() => this.$refs.searchInput.focus());
        }
    },

    select(opt) {
        this.selectedId = opt.id;
        this.selectedLabel = opt.label + (opt.sublabel ? ' (' + opt.sublabel + ')' : '');
        this.open = false;
        this.search = '';
        if (this.optionsExpr === 'availableMapels' && typeof this.selectedMapel !== 'undefined') {
            this.selectedMapel = opt.id;
        }
        $dispatch('input', this.selectedId);
        $dispatch('change', this.selectedId);

        if (this.autoSubmit) {
            this.$nextTick(() => {
                const form = this.$el.closest('form');
                if (form) form.submit();
            });
        }
    },

    clear() {
        this.selectedId = '';
        this.selectedLabel = '';
        this.search = '';
        if (this.optionsExpr === 'availableMapels' && typeof this.selectedMapel !== 'undefined') {
            this.selectedMapel = '';
        }
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
    <div @click="toggleOpen()"
        class="flex items-center justify-between gap-2 w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white shadow-sm text-sm px-4 py-3 transition {{ $errors->has($name) ? 'border-rose-400 ring-2 ring-rose-400/20' : '' }}"
        :class="isDisabled ? 'opacity-50 bg-slate-100 dark:bg-slate-900/50 cursor-not-allowed border-slate-200 dark:border-slate-700' : (open ? 'border-brand-500 ring-2 ring-brand-500/20 cursor-pointer' : 'cursor-pointer hover:border-brand-500')">
        <div class="flex items-center gap-2 truncate">
            <x-icon name="search" class="h-4 w-4 shrink-0 text-slate-400" />
            <span x-show="selectedLabel" x-text="selectedLabel" class="truncate font-medium text-slate-900 dark:text-white"></span>
            <span x-show="!selectedLabel" x-text="currentPlaceholder" class="text-slate-400 dark:text-slate-500 truncate"></span>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            <button type="button" x-show="selectedId && !isDisabled" @click.stop="clear()" class="p-0.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" title="Hapus pilihan">
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
                @keydown.enter.prevent="if (filteredOptions.length > 0) select(filteredOptions[0])"
                placeholder="{{ $searchPlaceholder }}"
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
                {{ $emptyText }}
            </div>
        </div>
    </div>

    <p x-show="isDisabled && disabledHint" class="mt-1 text-xs text-slate-400" x-text="disabledHint"></p>

    @if ($hint && ! $errors->has($name))
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
    @enderror
</div>
