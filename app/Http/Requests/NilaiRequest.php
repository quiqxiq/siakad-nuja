<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NilaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $nilai = $this->route('nilai');
        $nilaiId = $nilai instanceof \App\Models\Nilai ? $nilai->id : $nilai;

        $uniqueRule = Rule::unique('nilai', 'siswa_id')
            ->where(function ($query) {
                return $query->where('mapel_id', $this->input('mapel_id'))
                    ->where('semester', $this->input('semester'))
                    ->where('tahun_ajaran', $this->input('tahun_ajaran'));
            });

        if ($nilaiId) {
            $uniqueRule->ignore($nilaiId);
        }

        return [
            'siswa_id' => [
                'required',
                'exists:siswa,id',
                $uniqueRule,
            ],
            'mapel_id' => ['required', 'exists:mata_pelajaran,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'semester' => ['required', Rule::in(['Ganjil', 'Genap'])],
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
            'nilai_harian' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nilai_uts' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nilai_uas' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * Validasi tambahan agar mata pelajaran dan siswa sesuai dengan kelas.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator): void {
            $kelasId = (int) $this->input('kelas_id');
            $mapelId = (int) $this->input('mapel_id');
            $siswaId = (int) $this->input('siswa_id');

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

            if ($kelasId && $siswaId) {
                $siswa = \App\Models\Siswa::find($siswaId);
                if ($siswa && (int) $siswa->kelas_id !== $kelasId) {
                    $kelas = \App\Models\Kelas::find($kelasId);
                    $validator->errors()->add('siswa_id', "Siswa '{$siswa->nama_lengkap}' bukan merupakan siswa di kelas {$kelas?->nama_lengkap}.");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'siswa_id.unique' => 'Nilai untuk siswa ini pada mata pelajaran, semester, dan tahun ajaran tersebut sudah pernah diinput. Silakan gunakan menu Edit untuk memperbarui nilai.',
            'tahun_ajaran.regex' => 'Format tahun ajaran harus YYYY/YYYY (contoh: 2026/2027).',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'siswa_id' => 'siswa',
            'mapel_id' => 'mata pelajaran',
            'kelas_id' => 'kelas',
            'tahun_ajaran' => 'tahun ajaran',
            'nilai_harian' => 'nilai harian',
            'nilai_uts' => 'nilai UTS',
            'nilai_uas' => 'nilai UAS',
        ];
    }
}
