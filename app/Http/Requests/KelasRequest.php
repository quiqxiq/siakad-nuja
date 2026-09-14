<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KelasRequest extends FormRequest
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
            'nama_kelas' => ['required', 'string', 'max:50'],
            'tingkat' => ['required', 'string', 'max:10'],
            'jenjang' => ['required', Rule::in(['SD', 'SMP', 'SMA', 'SMK', 'MI', 'MTs', 'MA'])],
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
            'wali_kelas_id' => ['nullable', 'exists:guru,id'],
            'kapasitas' => ['nullable', 'integer', 'min:1', 'max:255'],
            'ruangan' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('kelas', 'ruangan')
                    ->where(fn ($query) => $query->where('tahun_ajaran', $this->input('tahun_ajaran') ?? $this->route('kela')?->tahun_ajaran))
                    ->ignore($this->route('kela')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'tahun_ajaran.regex' => 'Format tahun ajaran harus YYYY/YYYY (contoh: 2026/2027).',
            'ruangan.unique' => 'Ruangan ini sudah digunakan oleh kelas lain pada tahun ajaran yang sama. 1 ruangan hanya untuk 1 kelas.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama_kelas' => 'nama kelas',
            'tahun_ajaran' => 'tahun ajaran',
            'wali_kelas_id' => 'wali kelas',
            'ruangan' => 'ruangan kelas',
        ];
    }
}
