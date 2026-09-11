<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';

    public $timestamps = false;

    protected $fillable = [
        'siswa_id',
        'jadwal_id',
        'tanggal',
        'status',
        'keterangan',
        'latitude',
        'longitude',
        'jarak_meter',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'latitude' => 'float',
            'longitude' => 'float',
            'jarak_meter' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalPelajaran::class, 'jadwal_id');
    }
}
