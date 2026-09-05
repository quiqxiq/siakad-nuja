<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\NilaiRequest;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Konfigurasi;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Services\RankingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NilaiController extends Controller
{
    public function __construct(
        protected RankingService $rankingService
    ) {}

    public function index(): View
    {
        $user = request()->user();
        $isGuru = $user?->isGuru();
        $guru = $user?->guru;

        $nilai = Nilai::with(['siswa', 'mapel', 'kelas'])
            ->when($isGuru, function ($query) use ($guru): void {
                // Guru hanya melihat dan mengelola nilai kelas & mapel yang ia ampu.
                $kelasMapel = $guru?->jadwal()->get(['kelas_id', 'mapel_id']) ?? collect();

                $query->where(function ($q) use ($kelasMapel): void {
                    if ($kelasMapel->isEmpty()) {
                        $q->whereRaw('1 = 0');
                        return;
                    }

                    foreach ($kelasMapel as $km) {
                        $q->orWhere(fn ($sub) => $sub->where('kelas_id', $km->kelas_id)->where('mapel_id', $km->mapel_id));
                    }
                });
            })
            ->when(request('kelas_id'), fn ($query, $id) => $query->where('kelas_id', $id))
            ->when(request('mapel_id'), fn ($query, $id) => $query->where('mapel_id', $id))
            ->when(request('semester'), fn ($query, $s) => $query->where('semester', $s))
            ->when(request('search'), function ($query, $search): void {
                $query->whereHas('siswa', function ($q) use ($search): void {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        if ($isGuru) {
            $teachingKelasIds = $guru?->teachingKelasIds() ?? [];
            $teachingMapelIds = $guru?->teachingMapelIds() ?? [];
            $kelasList = Kelas::whereIn('id', $teachingKelasIds ?: [0])->orderBy('nama_kelas')->get();
            $mapelList = MataPelajaran::whereIn('id', $teachingMapelIds ?: [0])->orderBy('nama_mapel')->get();
        } else {
            $kelasList = Kelas::orderBy('nama_kelas')->get();
            $mapelList = MataPelajaran::orderBy('nama_mapel')->get();
        }

        return view('nilai.index', compact('nilai', 'kelasList', 'mapelList'));
    }

    /**
     * Form Entri Nilai Massal (Matrix Ledger Entry per Kelas & Mapel).
     */
    public function matrix(Request $request): View
    {
        $user = $request->user();
        $isGuru = $user?->isGuru();
        $guru = $user?->guru;

        // Opsi kelas & mapel yang tersedia untuk user
        if ($isGuru) {
            $teachingKelasIds = $guru?->teachingKelasIds() ?? [];
            $kelasList = Kelas::whereIn('id', $teachingKelasIds ?: [0])->orderBy('nama_kelas')->get();
        } else {
            $kelasList = Kelas::orderBy('nama_kelas')->get();
        }

        $selectedKelasId = $request->filled('kelas_id') ? (int) $request->input('kelas_id') : ($kelasList->first()?->id ?? null);

        // Mapel yang tersedia untuk kelas terpilih
        if ($selectedKelasId) {
            if ($isGuru) {
                $teachingMapelIds = $guru?->jadwal()->where('kelas_id', $selectedKelasId)->pluck('mapel_id')->unique()->all() ?? [];
                $mapelList = MataPelajaran::whereIn('id', $teachingMapelIds ?: [0])->orderBy('nama_mapel')->get();
            } else {
                $mapelIdsInKelas = JadwalPelajaran::where('kelas_id', $selectedKelasId)->pluck('mapel_id')->unique()->all();
                $mapelList = MataPelajaran::whereIn('id', $mapelIdsInKelas ?: [0])->orderBy('nama_mapel')->get();
                if ($mapelList->isEmpty()) {
                    $mapelList = MataPelajaran::orderBy('nama_mapel')->get();
                }
            }
        } else {
            $mapelList = collect();
        }

        $selectedMapelId = $request->filled('mapel_id') ? (int) $request->input('mapel_id') : ($mapelList->first()?->id ?? null);
        $semester = $request->input('semester', Konfigurasi::semesterAktif());
        $tahunAjaran = $request->input('tahun_ajaran', Konfigurasi::tahunAjaranAktif());

        $matrixData = null;
        $selectedKelas = null;
        $selectedMapel = null;

        if ($selectedKelasId && $selectedMapelId) {
            // Validasi otorisasi guru
            if ($isGuru && ! $guru?->isTeaching($selectedKelasId, $selectedMapelId)) {
                abort(403, 'Anda tidak memiliki jadwal mengajar untuk mata pelajaran di kelas ini.');
            }

            $selectedKelas = Kelas::find($selectedKelasId);
            $selectedMapel = MataPelajaran::find($selectedMapelId);

            $matrixData = $this->rankingService->getPeringkatMapel(
                $selectedKelasId,
                $selectedMapelId,
                $semester,
                $tahunAjaran
            );
        }

        return view('nilai.matrix', compact(
            'kelasList',
            'mapelList',
            'selectedKelasId',
            'selectedMapelId',
            'selectedKelas',
            'selectedMapel',
            'semester',
            'tahunAjaran',
            'matrixData'
        ));
    }

    /**
     * Simpan Entri Nilai Massal.
     */
    public function storeMatrix(Request $request): RedirectResponse
    {
        $user = $request->user();
        $isGuru = $user?->isGuru();
        $guru = $user?->guru;

        $validated = $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mapel_id' => ['required', 'exists:mata_pelajaran,id'],
            'semester' => ['required', 'in:Ganjil,Genap'],
            'tahun_ajaran' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'nilai_harian' => ['nullable', 'array'],
            'nilai_harian.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nilai_uts' => ['nullable', 'array'],
            'nilai_uts.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nilai_uas' => ['nullable', 'array'],
            'nilai_uas.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $kelasId = (int) $validated['kelas_id'];
        $mapelId = (int) $validated['mapel_id'];
        $semester = $validated['semester'];
        $tahunAjaran = $validated['tahun_ajaran'];

        if ($isGuru && ! $guru?->isTeaching($kelasId, $mapelId)) {
            abort(403, 'Anda tidak berwenang menyimpan nilai untuk mata pelajaran dan kelas ini.');
        }

        $mapel = MataPelajaran::findOrFail($mapelId);
        $kkm = $mapel->kkm;

        $siswaIds = Siswa::where('kelas_id', $kelasId)->pluck('id')->all();

        DB::transaction(function () use ($siswaIds, $kelasId, $mapelId, $semester, $tahunAjaran, $request, $kkm): void {
            $harianInput = $request->input('nilai_harian', []);
            $utsInput = $request->input('nilai_uts', []);
            $uasInput = $request->input('nilai_uas', []);

            foreach ($siswaIds as $siswaId) {
                $h = isset($harianInput[$siswaId]) && $harianInput[$siswaId] !== '' ? (float) $harianInput[$siswaId] : null;
                $u = isset($utsInput[$siswaId]) && $utsInput[$siswaId] !== '' ? (float) $utsInput[$siswaId] : null;
                $a = isset($uasInput[$siswaId]) && $uasInput[$siswaId] !== '' ? (float) $uasInput[$siswaId] : null;

                // Jika ketiga nilai kosong, cek apakah sudah ada record nilai sebelumnya untuk dihapus atau dibiarkan kosong
                if ($h === null && $u === null && $a === null) {
                    Nilai::where([
                        'siswa_id' => $siswaId,
                        'kelas_id' => $kelasId,
                        'mapel_id' => $mapelId,
                        'semester' => $semester,
                        'tahun_ajaran' => $tahunAjaran,
                    ])->delete();
                    continue;
                }

                $akhir = Nilai::hitungNilaiAkhir($h, $u, $a);
                $predikat = Nilai::hitungPredikat($akhir, $kkm);

                Nilai::updateOrCreate(
                    [
                        'siswa_id' => $siswaId,
                        'kelas_id' => $kelasId,
                        'mapel_id' => $mapelId,
                        'semester' => $semester,
                        'tahun_ajaran' => $tahunAjaran,
                    ],
                    [
                        'nilai_harian' => $h,
                        'nilai_uts' => $u,
                        'nilai_uas' => $a,
                        'nilai_akhir' => $akhir,
                        'predikat' => $predikat,
                    ]
                );
            }
        });

        return redirect()
            ->route('nilai.matrix', [
                'kelas_id' => $kelasId,
                'mapel_id' => $mapelId,
                'semester' => $semester,
                'tahun_ajaran' => $tahunAjaran,
            ])
            ->with('success', 'Nilai massal berhasil disimpan.');
    }

    /**
     * Halaman Buku Leger Nilai & Peringkat Kelas (Khusus Admin).
     */
    public function leger(Request $request): View
    {
        $user = $request->user();

        if (! $user?->isAdmin()) {
            abort(403, 'Akses ditolak. Buku leger dan peringkat kelas hanya dapat diakses oleh Administrator.');
        }

        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $selectedKelasId = $request->filled('kelas_id') ? (int) $request->input('kelas_id') : ($kelasList->first()?->id ?? null);
        $semester = $request->input('semester', Konfigurasi::semesterAktif());
        $tahunAjaran = $request->input('tahun_ajaran', Konfigurasi::tahunAjaranAktif());

        $legerData = null;

        if ($selectedKelasId) {
            $legerData = $this->rankingService->getLegerKelas($selectedKelasId, $semester, $tahunAjaran);
        }

        return view('nilai.leger', compact(
            'kelasList',
            'selectedKelasId',
            'semester',
            'tahunAjaran',
            'legerData'
        ));
    }

    /**
     * Ekspor Buku Leger Nilai ke PDF atau Excel (Khusus Admin).
     */
    public function exportLeger(Request $request)
    {
        $user = $request->user();

        if (! $user?->isAdmin()) {
            abort(403, 'Akses ditolak. Unduh buku leger hanya dapat dilakukan oleh Administrator.');
        }

        $validated = $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'semester' => ['required', 'in:Ganjil,Genap'],
            'tahun_ajaran' => ['required', 'string'],
            'format' => ['nullable', 'in:pdf,excel'],
        ]);

        $kelasId = (int) $validated['kelas_id'];
        $semester = $validated['semester'];
        $tahunAjaran = $validated['tahun_ajaran'];
        $format = $validated['format'] ?? 'pdf';

        $legerData = $this->rankingService->getLegerKelas($kelasId, $semester, $tahunAjaran);
        $title = 'Buku_Leger_Nilai_' . Str::slug($legerData['kelas']->nama_kelas) . '_' . Str::slug($semester) . '_' . Str::slug($tahunAjaran);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('nilai.pdf_leger', compact('legerData'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($title . '.pdf');
        }

        return response()->view('nilai.pdf_leger', compact('legerData'))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $title . '.xls"');
    }

    public function create(): View
    {
        $this->authorize('create', Nilai::class);

        return view('nilai.create', $this->formData());
    }

    public function store(NilaiRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->authorize('create', Nilai::class);
        $this->authorizeScope((int) $validated['kelas_id'], (int) $validated['mapel_id']);

        $validated = $this->withCalculatedGrades($validated);

        Nilai::updateOrCreate(
            [
                'siswa_id' => $validated['siswa_id'],
                'mapel_id' => $validated['mapel_id'],
                'semester' => $validated['semester'],
                'tahun_ajaran' => $validated['tahun_ajaran'],
            ],
            $validated
        );

        return redirect()->route('nilai.index')->with('success', 'Nilai berhasil ditambahkan.');
    }

    public function show(Nilai $nilai): View
    {
        $this->authorize('view', $nilai);

        $nilai->load('siswa', 'mapel', 'kelas');

        return view('nilai.show', compact('nilai'));
    }

    public function edit(Nilai $nilai): View
    {
        $this->authorize('update', $nilai);

        return view('nilai.edit', ['nilai' => $nilai] + $this->formData());
    }

    public function update(NilaiRequest $request, Nilai $nilai): RedirectResponse
    {
        $this->authorize('update', $nilai);

        $validated = $request->validated();
        $this->authorizeScope((int) $validated['kelas_id'], (int) $validated['mapel_id']);

        $validated = $this->withCalculatedGrades($validated);

        $nilai->update($validated);

        return redirect()->route('nilai.index')->with('success', 'Nilai berhasil diperbarui.');
    }

    public function destroy(Nilai $nilai): RedirectResponse
    {
        $this->authorize('delete', $nilai);

        $nilai->delete();

        return redirect()->route('nilai.index')->with('success', 'Nilai berhasil dihapus.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withCalculatedGrades(array $data): array
    {
        $harian = isset($data['nilai_harian']) && $data['nilai_harian'] !== '' && $data['nilai_harian'] !== null ? (float) $data['nilai_harian'] : null;
        $uts = isset($data['nilai_uts']) && $data['nilai_uts'] !== '' && $data['nilai_uts'] !== null ? (float) $data['nilai_uts'] : null;
        $uas = isset($data['nilai_uas']) && $data['nilai_uas'] !== '' && $data['nilai_uas'] !== null ? (float) $data['nilai_uas'] : null;

        $mapel = isset($data['mapel_id']) ? MataPelajaran::find($data['mapel_id']) : null;
        $kkm = $mapel?->kkm;

        $akhir = Nilai::hitungNilaiAkhir($harian, $uts, $uas);

        $data['nilai_akhir'] = $akhir;
        $data['predikat'] = Nilai::hitungPredikat($akhir, $kkm);

        return $data;
    }

    /**
     * Pastikan guru hanya menyimpan nilai untuk kelas/mapel miliknya.
     */
    private function authorizeScope(int $kelasId, int $mapelId): void
    {
        $user = request()->user();
        if ($user?->isAdmin()) {
            return;
        }

        $guru = $user?->guru;
        if (! $guru || ! $guru->isTeaching($kelasId, $mapelId)) {
            abort(403, 'Anda tidak berwenang mengelola nilai pada mata pelajaran dan kelas ini.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        $user = request()->user();
        $isGuru = $user?->isGuru();
        $guru = $user?->guru;

        if ($isGuru) {
            $teachingKelasIds = $guru?->teachingKelasIds() ?? [];
            $teachingMapelIds = $guru?->teachingMapelIds() ?? [];

            $kelas = Kelas::whereIn('id', $teachingKelasIds ?: [0])->orderBy('nama_kelas')->get();
            $mapel = MataPelajaran::whereIn('id', $teachingMapelIds ?: [0])->orderBy('nama_mapel')->get();
            $siswa = Siswa::with('kelas')->whereIn('kelas_id', $teachingKelasIds ?: [0])->orderBy('nama_lengkap')->get();

            $jadwalMapelByKelas = $guru?->jadwal()
                ->get(['kelas_id', 'mapel_id'])
                ->groupBy('kelas_id')
                ->map(fn ($items) => $items->pluck('mapel_id')->unique()->values()->all())
                ->all() ?? [];
        } else {
            $kelas = Kelas::orderBy('nama_kelas')->get();
            $mapel = MataPelajaran::orderBy('nama_mapel')->get();
            $siswa = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
            $jadwalMapelByKelas = null;
        }

        return compact('siswa', 'mapel', 'kelas', 'jadwalMapelByKelas');
    }
}
