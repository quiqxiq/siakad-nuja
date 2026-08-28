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
        return [
            'siswa_id' => ['required', 'exists:siswa,id'],
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

    public function messages(): array
    {
        return [
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
