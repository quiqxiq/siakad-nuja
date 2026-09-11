<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Services\GeolocationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AbsensiController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Base query dengan otorisasi guru
        $baseQuery = Absensi::query()
            ->when($user->isGuru(), function ($query) use ($user): void {
                $jadwalIds = $this->jadwalIdsUntukGuru($user);
                $query->whereIn('jadwal_id', $jadwalIds ?: [0]);
            });

        // Summary counts
        $summary = [
            'total' => (clone $baseQuery)->count(),
            'hadir' => (clone $baseQuery)->where('status', 'Hadir')->count(),
            'izin'  => (clone $baseQuery)->where('status', 'Izin')->count(),
            'sakit' => (clone $baseQuery)->where('status', 'Sakit')->count(),
            'alpa'  => (clone $baseQuery)->where('status', 'Alpa')->count(),
        ];

        // Filtered query
        $absensi = (clone $baseQuery)
            ->with(['siswa.kelas', 'jadwal.mapel', 'jadwal.kelas', 'jadwal.guru'])
            ->when($request->filled('q'), function ($q) use ($request): void {
                $search = '%' . trim((string) $request->input('q')) . '%';
                $q->where(function ($sub) use ($search): void {
                    $sub->whereHas('siswa', function ($sq) use ($search): void {
                        $sq->where('nama_lengkap', 'like', $search)
                            ->orWhere('nis', 'like', $search);
                    })
                    ->orWhere('keterangan', 'like', $search);
                });
            })
            ->when($request->filled('kelas_id'), function ($q) use ($request): void {
                $kelasId = $request->input('kelas_id');
                $q->whereHas('jadwal', fn ($jq) => $jq->where('kelas_id', $kelasId));
            })
            ->when($request->filled('mapel_id'), function ($q) use ($request): void {
                $mapelId = $request->input('mapel_id');
                $q->whereHas('jadwal', fn ($jq) => $jq->where('mapel_id', $mapelId));
            })
            ->when($request->filled('hari'), function ($q) use ($request): void {
                $hari = $request->input('hari');
                $q->whereHas('jadwal', fn ($jq) => $jq->where('hari', $hari));
            })
            ->when($request->filled('guru_id'), function ($q) use ($request): void {
                $guruId = $request->input('guru_id');
                $q->whereHas('jadwal', fn ($jq) => $jq->where('guru_id', $guruId));
            })
            ->when($request->filled('status'), function ($q) use ($request): void {
                $q->where('status', $request->input('status'));
            })
            ->when($request->filled('tanggal'), function ($q) use ($request): void {
                $q->whereDate('tanggal', $request->input('tanggal'));
            })
            ->when($request->filled('jadwal_id'), function ($q) use ($request): void {
                $q->where('jadwal_id', $request->input('jadwal_id'));
            })
            ->orderByDesc('tanggal')
            ->orderBy('jadwal_id')
            ->orderBy('siswa_id')
            ->paginate(20)
            ->withQueryString();

        // Dropdowns data
        if ($user->isGuru()) {
            $guruJadwalIds = $this->jadwalIdsUntukGuru($user);
            $kelasIds = JadwalPelajaran::whereIn('id', $guruJadwalIds)->pluck('kelas_id');
            $mapelIds = JadwalPelajaran::whereIn('id', $guruJadwalIds)->pluck('mapel_id');

            $kelasList = Kelas::whereIn('id', $kelasIds)->orderBy('nama_kelas')->get();
            $mapelList = MataPelajaran::whereIn('id', $mapelIds)->orderBy('nama_mapel')->get();
            $guruList = collect();
        } else {
            $kelasList = Kelas::orderBy('nama_kelas')->get();
            $mapelList = MataPelajaran::orderBy('nama_mapel')->get();
            $guruList = Guru::orderBy('nama_lengkap')->get();
        }

        $hariList = ['Sabtu', 'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis'];
        $statusList = ['Hadir', 'Izin', 'Sakit', 'Alpa'];

        return view('absensi.index', compact(
            'absensi',
            'summary',
            'kelasList',
            'mapelList',
            'guruList',
            'hariList',
            'statusList'
        ));
    }

    /**
     * Langkah 1: pilih jadwal + tanggal.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Absensi::class);

        $jadwal = $this->jadwalTerpilih($request)
            ->with(['mapel', 'kelas', 'guru'])
            ->orderBy('kelas_id')
            ->get();

        $kelasIds = $jadwal->pluck('kelas_id')->unique()->filter()->all();
        $kelasList = Kelas::whereIn('id', $kelasIds ?: [0])->orderBy('nama_kelas')->get();
        $lokasiConfig = app(GeolocationService::class)->getMadrasahConfig();

        return view('absensi.create', compact('jadwal', 'kelasList', 'lokasiConfig'));
    }

    /**
     * Langkah 2: tampilkan daftar siswa pada kelas jadwal tsb untuk entri massal.
     */
    public function roster(Request $request): View
    {
        $this->authorize('create', Absensi::class);

        $validated = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwal_pelajaran,id'],
            'tanggal' => ['required', 'date'],
        ]);

        $jadwal = JadwalPelajaran::with(['mapel', 'kelas'])->findOrFail($validated['jadwal_id']);

        // Pastikan guru berwenang atas jadwal ini.
        $this->authorizeJadwal($request, (int) $jadwal->id);

        $siswa = Siswa::where('kelas_id', $jadwal->kelas_id)
            ->orderBy('nama_lengkap')
            ->get();

        $existing = Absensi::where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', $validated['tanggal'])
            ->get()
            ->keyBy('siswa_id');

        $lokasiConfig = app(GeolocationService::class)->getMadrasahConfig();

        return view('absensi.roster', [
            'jadwal' => $jadwal,
            'tanggal' => $validated['tanggal'],
            'siswa' => $siswa,
            'existing' => $existing,
            'lokasiConfig' => $lokasiConfig,
        ]);
    }

    /**
     * Simpan absensi massal (upsert per siswa + jadwal + tanggal).
     * Dibatasi hanya dalam radius lokasi madrasah (guru & admin).
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Absensi::class);

        $validated = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwal_pelajaran,id'],
            'tanggal' => ['required', 'date'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'status' => ['required', 'array'],
            'status.*' => [Rule::in(['Hadir', 'Izin', 'Sakit', 'Alpa'])],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'string', 'max:255'],
        ], [
            'latitude.required' => 'Koordinat GPS (latitude) wajib disertakan untuk verifikasi lokasi absensi.',
            'longitude.required' => 'Koordinat GPS (longitude) wajib disertakan untuk verifikasi lokasi absensi.',
            'latitude.numeric' => 'Format koordinat latitude tidak valid.',
            'longitude.numeric' => 'Format koordinat longitude tidak valid.',
            'latitude.between' => 'Koordinat latitude berada di luar rentang valid bumi.',
            'longitude.between' => 'Koordinat longitude berada di luar rentang valid bumi.',
        ]);

        $this->authorizeJadwal($request, (int) $validated['jadwal_id']);

        // Verifikasi geolokasi guru & admin
        $geoService = app(GeolocationService::class);
        $locationCheck = $geoService->validateAttendanceLocation(
            (float) $validated['latitude'],
            (float) $validated['longitude']
        );

        if (! $locationCheck['valid']) {
            throw ValidationException::withMessages([
                'lokasi' => $locationCheck['message'],
            ]);
        }

        DB::transaction(function () use ($validated, $locationCheck): void {
            $lat = (float) $validated['latitude'];
            $lon = (float) $validated['longitude'];
            $jarak = $locationCheck['distance'] !== null ? (int) round($locationCheck['distance']) : null;

            foreach ($validated['status'] as $siswaId => $status) {
                Absensi::updateOrCreate(
                    [
                        'siswa_id' => $siswaId,
                        'jadwal_id' => $validated['jadwal_id'],
                        'tanggal' => $validated['tanggal'],
                    ],
                    [
                        'status' => $status,
                        'keterangan' => $validated['keterangan'][$siswaId] ?? null,
                        'latitude' => $lat,
                        'longitude' => $lon,
                        'jarak_meter' => $jarak,
                    ],
                );
            }
        });

        return redirect()
            ->route('absensi.index', ['jadwal_id' => $validated['jadwal_id'], 'tanggal' => $validated['tanggal']])
            ->with('success', 'Absensi berhasil disimpan di lokasi ' . $locationCheck['target_name'] . '.');
    }

    public function show(Absensi $absensi): View
    {
        $this->authorize('view', $absensi);

        $absensi->load('siswa', 'jadwal.mapel', 'jadwal.kelas');

        return view('absensi.show', compact('absensi'));
    }

    public function destroy(Absensi $absensi): RedirectResponse
    {
        $this->authorize('delete', $absensi);

        $absensi->delete();

        return redirect()->route('absensi.index')->with('success', 'Data absensi berhasil dihapus.');
    }

    /**
     * Pastikan user berwenang atas jadwal tertentu (admin selalu boleh).
     */
    private function authorizeJadwal(Request $request, int $jadwalId): void
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return;
        }

        $policy = new \App\Policies\AbsensiPolicy();

        if (! $policy->mengampuJadwal($user, $jadwalId)) {
            abort(403, 'Anda tidak berwenang atas jadwal ini.');
        }
    }

    /**
     * Query jadwal yang boleh diakses guru (yang diampu atau kelas yang diwalikan).
     */
    private function jadwalTerpilih(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $user = $request->user();
        $query = JadwalPelajaran::query();

        if ($user->isGuru()) {
            $ids = $this->jadwalIdsUntukGuru($user);
            $query->whereIn('id', $ids ?: [0]);
        }

        return $query;
    }

    /**
     * @return array<int, int>
     */
    private function jadwalIdsUntukGuru(\App\Models\User $user): array
    {
        $guru = $user->guru;

        if ($guru === null) {
            return [];
        }

        return $guru->jadwal()->pluck('id')->values()->all();
    }
}
