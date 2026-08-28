<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 30)->unique();
            $table->string('nama_lengkap', 150);
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin', 10);
            $table->text('alamat')->nullable();
            $table->string('foto', 255)->nullable();
            $table->string('status', 20)->nullable();
            $table->year('tahun_masuk');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
