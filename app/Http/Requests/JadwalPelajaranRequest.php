<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JadwalPelajaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('hari')) {
            $hari = (string) $this->input('hari');
            if (strcasecmp($hari, 'Ahad') === 0) {
                $this->merge(['hari' => 'Minggu']);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mapel_id' => ['required', 'exists:mata_pelajaran,id'],
            'guru_id' => ['required', 'exists:guru,id'],
            'hari' => ['required', Rule::in(['Sabtu', 'Minggu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis'])],
            'jam_ke' => ['required', 'integer', 'min:1', 'max:4'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'ruangan' => ['nullable', 'string', 'max:50'],
            'tahun_ajaran' => [
                'required',
                'string',
                'regex:/^\d{4}\/\d{4}$/',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (preg_match('/^(\d{4})\/(\d{4})$/', (string) $value, $matches)) {
                        $startYear = (int) $matches[1];
                        $currentYear = (int) date('Y');
                        if ($startYear > $currentYear) {
                            $fail("Tahun ajaran tidak boleh melebihi tahun saat ini ({$currentYear}).");
                        }
                    }
                },
            ],
        ];
    }

    /**
     * Jalankan validasi kesesuaian kurikulum kelas & anti-bentrok jadwal (Guru, Kelas, Ruangan).
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator): void {
            $kelasId = (int) $this->input('kelas_id');
            $mapelId = (int) $this->input('mapel_id');

            if ($kelasId && $mapelId) {
                $kelas = \App\Models\Kelas::find($kelasId);
                $mapel = \App\Models\MataPelajaran::find($mapelId);

                if ($kelas && $mapel) {
                    if ($kelas->jenjang !== $mapel->jenjang) {
                        $validator->errors()->add('mapel_id', "Mata pelajaran '{$mapel->nama_mapel}' ({$mapel->jenjang}) tidak sesuai dengan jenjang {$kelas->nama_lengkap}.");
                    } else {
                        $registeredMapelIds = $kelas->mataPelajaran()->pluck('mata_pelajaran.id')->all();
                        if (! empty($registeredMapelIds) && ! in_array($mapelId, $registeredMapelIds, true)) {
                            $validator->errors()->add('mapel_id', "Mata pelajaran '{$mapel->nama_mapel}' tidak terdaftar pada kurikulum {$kelas->nama_lengkap}.");
                        }
                    }
                }
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $param = $this->route('jadwal');
            $ignoreId = is_object($param) ? (int) $param->id : (is_numeric($param) ? (int) $param : null);

            $service = app(\App\Services\JadwalConflictService::class);
            $conflicts = $service->checkConflict($this->all(), $ignoreId);

            foreach ($conflicts as $field => $message) {
                $validator->errors()->add($field, $message);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'jam_ke.max' => 'Jam pelajaran madrasah dibatasi maksimal sampai jam ke-4.',
            'jam_ke.min' => 'Jam pelajaran minimal adalah jam ke-1.',
            'jam_selesai.after' => 'Jam selesai harus lebih besar (setelah) dari jam mulai.',
            'tahun_ajaran.regex' => 'Format tahun ajaran harus YYYY/YYYY (contoh: 2026/2027).',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'kelas_id' => 'kelas',
            'mapel_id' => 'mata pelajaran',
            'guru_id' => 'guru',
            'hari' => 'hari',
            'jam_ke' => 'jam ke',
            'jam_mulai' => 'jam mulai',
            'jam_selesai' => 'jam selesai',
            'ruangan' => 'ruangan',
            'tahun_ajaran' => 'tahun ajaran',
        ];
    }
}
