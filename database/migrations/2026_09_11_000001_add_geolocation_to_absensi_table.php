<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('keterangan');
            $table->decimal('longitude', 11, 7)->nullable()->after('latitude');
            $table->unsignedSmallInteger('jarak_meter')->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'jarak_meter']);
        });
    }
};
