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
        if (! Schema::hasColumn('kelas', 'ruangan')) {
            Schema::table('kelas', function (Blueprint $table): void {
                $table->string('ruangan', 50)->nullable()->after('kapasitas');
            });
        }

        // Isi data default ruangan pada kelas yang sudah ada secara konsisten
        $kelasList = DB::table('kelas')->get();
        foreach ($kelasList as $kelas) {
            $ruangan = 'R-'.$kelas->nama_kelas.'-'.($kelas->jenjang ?: 'KLS');

            DB::table('kelas')
                ->where('id', $kelas->id)
                ->update(['ruangan' => $ruangan]);

            // Sinkronkan seluruh jadwal pelajaran kelas tersebut ke 1 ruangan tetap ini
            DB::table('jadwal_pelajaran')
                ->where('kelas_id', $kelas->id)
                ->update(['ruangan' => $ruangan]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('kelas', 'ruangan')) {
            Schema::table('kelas', function (Blueprint $table): void {
                $table->dropColumn('ruangan');
            });
        }
    }
};
