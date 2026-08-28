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
            'tahun_ajaran' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'wali_kelas_id' => ['nullable', 'exists:guru,id'],
            'kapasitas' => ['nullable', 'integer', 'min:1', 'max:255'],
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
            'nama_kelas' => 'nama kelas',
            'tahun_ajaran' => 'tahun ajaran',
            'wali_kelas_id' => 'wali kelas',
        ];
    }
}
