<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SiswaRequest;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Services\WhatsappGatewayService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiswaController extends Controller
{
    public function index(): View
    {
        $user = request()->user();

        $siswa = Siswa::with('kelas')
            ->accessibleBy($user)
            ->when(request('q'), function ($query, string $q): void {
                $query->where('nama_lengkap', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%");
            })
            ->when(request('kelas_id'), fn ($query, $id) => $query->where('kelas_id', $id))
            ->orderBy('nama_lengkap')
            ->paginate(15)
            ->withQueryString();

        $kelasList = Kelas::accessibleBy($user)->orderBy('nama_kelas')->get();

        return view('siswa.index', compact('siswa', 'kelasList'));
    }

    public function create(): View
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();

        return view('siswa.create', compact('kelas'));
    }

    public function store(SiswaRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('siswa', 'public');
        }

        Siswa::create($validated);

        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function show(Siswa $siswa): View
    {
        $this->authorize('view', $siswa);

        $siswa->load('kelas', 'orangTua', 'nilai.mapel', 'absensi.jadwal.mapel');

        return view('siswa.show', compact('siswa'));
    }

    public function edit(Siswa $siswa): View
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();

        return view('siswa.edit', compact('siswa', 'kelas'));
    }

    public function update(SiswaRequest $request, Siswa $siswa): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            if ($siswa->foto) {
                Storage::disk('public')->delete($siswa->foto);
            }
            $validated['foto'] = $request->file('foto')->store('siswa', 'public');
        }

        $siswa->update($validated);

        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        if ($siswa->foto) {
            Storage::disk('public')->delete($siswa->foto);
        }

        $siswa->delete();

        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil dihapus.');
    }

    /**
     * Kirim peringatan / teguran WA khusus ke semua wali murid siswa ini.
     */
    public function kirimTeguran(Request $request, Siswa $siswa): RedirectResponse
    {
        $this->authorize('kirimTeguran', $siswa);

        $request->validate([
            'jenis_teguran'    => ['required', 'string', 'max:100'],
            'catatan'          => ['required', 'string', 'max:1000'],
            'perlu_ke_sekolah' => ['nullable', 'boolean'],
        ]);

        $siswa->load('kelas');

        // Ambil kontak utama wali murid dari siswa ini
        $wali = $siswa->getKontakUtamaWali();

        if (! $wali || ! ($wali->no_wa ?? $wali->no_hp)) {
            return redirect()->back()->with('error', 'Gagal mengirim teguran: Siswa ini belum memiliki Kontak Utama WhatsApp terdaftar.');
        }

        $jenisTeguran   = $request->input('jenis_teguran');
        $catatan        = trim($request->input('catatan'));
        $perluKeSekolah = $request->boolean('perlu_ke_sekolah');

        $gateway = app(WhatsappGatewayService::class);
        $noTarget = $wali->no_wa ?? $wali->no_hp;
        $namaKelas = $siswa->kelas?->nama_kelas ?? '—';

        $pesan  = "⚠️ *PEMBERITAHUAN / CATATAN SEKOLAH*\n";
        $pesan .= "Yth. Bpk/Ibu *{$wali->nama}*,\n\n";
        $pesan .= "Memberitahukan catatan mengenai Ananda *{$siswa->nama_lengkap}* (Kelas {$namaKelas}):\n";
        $pesan .= "• *Perihal*: {$jenisTeguran}\n";
        $pesan .= "• *Catatan*: {$catatan}\n";

        if ($perluKeSekolah) {
            $pesan .= "\n📌 *Tindak Lanjut*: Bapak/Ibu dimohon untuk berkoordinasi dengan wali kelas / hadir ke sekolah.";
        }

        $pesan .= "\n\n— SIAKAD Nurul Jadid Karduluk";

        $success = $gateway->sendNotification(
            noHp: $noTarget,
            pesan: $pesan,
            jenis: 'teguran',
            orangTuaId: $wali->id,
            siswaId: $siswa->id
        );

        if ($success) {
            return redirect()->back()->with('success', "Peringatan WhatsApp berhasil terkirim ke Kontak Utama Wali Murid: {$wali->nama} ({$noTarget}).");
        }

        return redirect()->back()->with('error', "Gagal mengirim teguran ke Wali Murid ({$wali->nama} - {$noTarget}). Pastikan nomor WhatsApp terdaftar dan aktif.");
    }
}
