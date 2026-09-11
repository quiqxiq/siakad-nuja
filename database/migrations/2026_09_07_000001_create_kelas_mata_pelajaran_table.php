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
        Schema::create('kelas_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('mapel_id')->constrained('mata_pelajaran')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['kelas_id', 'mapel_id'], 'uq_kelas_mapel');
        });

        // Isi otomatis relasi kelas–mapel kurikulum dari jadwal yang sudah ada
        if (Schema::hasTable('jadwal_pelajaran')) {
            $pairs = DB::table('jadwal_pelajaran')
                ->select('kelas_id', 'mapel_id')
                ->distinct()
                ->get();

            $now = now();
            $insertData = [];
            foreach ($pairs as $p) {
                $insertData[] = [
                    'kelas_id' => $p->kelas_id,
                    'mapel_id' => $p->mapel_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (! empty($insertData)) {
                DB::table('kelas_mata_pelajaran')->insertOrIgnore($insertData);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas_mata_pelajaran');
    }
};
