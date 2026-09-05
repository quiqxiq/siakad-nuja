<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bersihkan rekaman duplikat jika ada, pertahankan id paling baru (terbesar)
        $duplicates = DB::table('nilai')
            ->select('siswa_id', 'mapel_id', 'semester', 'tahun_ajaran', DB::raw('MAX(id) as max_id'))
            ->groupBy('siswa_id', 'mapel_id', 'semester', 'tahun_ajaran')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('nilai')
                ->where('siswa_id', $dup->siswa_id)
                ->where('mapel_id', $dup->mapel_id)
                ->where('semester', $dup->semester)
                ->where('tahun_ajaran', $dup->tahun_ajaran)
                ->where('id', '<', $dup->max_id)
                ->delete();
        }

        // 2. Pasang unique constraint pada kombinasi siswa, mapel, semester, dan tahun ajaran
        Schema::table('nilai', function (Blueprint $table) {
            $table->unique(
                ['siswa_id', 'mapel_id', 'semester', 'tahun_ajaran'],
                'nilai_siswa_mapel_semester_ta_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('nilai', function (Blueprint $table) {
            $table->dropUnique('nilai_siswa_mapel_semester_ta_unique');
        });
    }
};
