<?php

declare(strict_types=1);

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $nameMap = [
            'ABD. AZIS, S.Pd.I' => 'Abd. Azis, M.Pd.I',
            'Abd. Azis, M.Pd.I' => 'Abd. Azis, M.Pd.I',
            'ABD. KAFI, S.Pd.I' => 'Abd. Kafi, S.Pd.I',
            'ASWARI, S.Pd' => 'Aswari, S.Pd.',
            'Aswari, S.Pd.' => 'Aswari, S.Pd.',
            'BAIDI, S.Pd' => 'Baidi, S.Pd.',
            'Baidi, S.Pd.' => 'Baidi, S.Pd.',
            'FARHATIN, S.Pd.I' => 'Farhatin, S.Pd.I',
            'HASIB, S.Pd. I' => 'Hasib, S.Pd.I',
            'HASIB, S.Pd.I' => 'Hasib, S.Pd.I',
            'Hasib, S.Pd.I' => 'Hasib, S.Pd.I',
            'K. ABUL HASAN, M.Ag' => 'K. Abul Hasan, S.Ag., M.Pd.I',
            'K. ABUL HASAN, S.Ag' => 'K. Abul Hasan, S.Ag., M.Pd.I',
            'K. Abul Hasan, S.Ag., M.Pd.I' => 'K. Abul Hasan, S.Ag., M.Pd.I',
            'K. ABUL MA\'ARIF, S.Pd' => 'K. Abul Ma\'arif, S.Pd.I.',
            'K. Abul Ma\'arif, S.Pd.I.' => 'K. Abul Ma\'arif, S.Pd.I.',
            'K. ACH. JAZULI MAHDI, S.Kom' => 'K. Ach. Jazuli Mahdi, S.Kom',
            'KH. HASAN BASHRI' => 'KH. Hasan Basri',
            'KH. Hasan Basri' => 'KH. Hasan Basri',
            'KHAIRUNNAS, S.H' => 'Khairunnas, S.H.',
            'Khairunnas, S.H.' => 'Khairunnas, S.H.',
            'KHATIBI, S.Pd' => 'Khatibi, S.Pd',
            'MOH. ROFI\'IE, S.H' => 'Moh. Rofi\'ie, S.H',
            'MUHAMMAD QUDSI, S.Kom.I' => 'Muhammad Qudsi, S.Kom.I',
            'SAHARI' => 'Sahari',
            'SHADRIYANTO' => 'Shadriyanto',
            'WAHED, S.Kom.I' => 'Wahed, S.Kom.I',
            'ZAINIYAH, S.Pd' => 'Zainiyah, S.Pd',
        ];

        DB::transaction(function () use ($nameMap): void {
            $allGurus = Guru::with('user')->get();
            $groups = [];

            foreach ($allGurus as $guru) {
                $canonical = $nameMap[$guru->nama_lengkap] ?? $guru->nama_lengkap;
                $groups[$canonical][] = $guru;
            }

            foreach ($groups as $canonicalName => $gurus) {
                // Pilih guru kanonikal: yang namanya sudah sama persis atau guru pertama
                $canonicalGuru = null;
                foreach ($gurus as $g) {
                    if ($g->nama_lengkap === $canonicalName) {
                        $canonicalGuru = $g;
                        break;
                    }
                }
                if (! $canonicalGuru) {
                    $canonicalGuru = $gurus[0];
                }

                // Update nama guru dan user kanonikal ke canonicalName yang rapi
                $canonicalGuru->update(['nama_lengkap' => $canonicalName]);
                if ($canonicalGuru->user) {
                    $canonicalGuru->user->update(['nama' => $canonicalName]);
                }

                // Alihkan semua data dari guru duplikat ke guru kanonikal
                foreach ($gurus as $g) {
                    if ($g->id === $canonicalGuru->id) {
                        continue;
                    }

                    // 1. Alihkan jadwal pelajaran
                    JadwalPelajaran::where('guru_id', $g->id)
                        ->update(['guru_id' => $canonicalGuru->id]);

                    // 2. Alihkan wali kelas
                    Kelas::where('wali_kelas_id', $g->id)
                        ->update(['wali_kelas_id' => $canonicalGuru->id]);

                    // 3. Simpan user_id untuk dihapus
                    $userIdToDelete = $g->user_id;

                    // 4. Hapus guru duplikat
                    $g->delete();

                    // 5. Hapus user duplikat jika ada
                    if ($userIdToDelete) {
                        User::where('id', $userIdToDelete)->delete();
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migrasi penggabungan data ini bersifat satu arah (irreversible).
    }
};
