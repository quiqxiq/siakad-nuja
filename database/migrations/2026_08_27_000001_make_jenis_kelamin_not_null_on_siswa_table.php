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
        // Pastikan tidak ada data NULL sebelum mengubah kolom menjadi NOT NULL
        DB::table('siswa')->whereNull('jenis_kelamin')->update(['jenis_kelamin' => 'L']);

        Schema::table('siswa', function (Blueprint $table) {
            $table->string('jenis_kelamin', 10)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->string('jenis_kelamin', 10)->nullable()->change();
        });
    }
};
