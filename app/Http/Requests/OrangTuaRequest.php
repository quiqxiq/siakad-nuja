<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrangTuaRequest extends FormRequest
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
            'nama' => ['required', 'string', 'max:150'],
            'hubungan' => ['nullable', Rule::in(['Ayah', 'Ibu', 'Wali'])],
            'no_wa' => ['required', 'string', 'regex:/^[0-9+\s-]+$/', 'max:20'],
            'no_hp' => ['nullable', 'string', 'regex:/^[0-9+\s-]+$/', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'pekerjaan' => ['nullable', 'string', 'max:100'],
            'is_kontak_utama' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $noWa = $this->input('no_wa');
        $noHp = $this->input('no_hp') ?: $noWa;

        $this->merge([
            'no_wa' => $noWa,
            'no_hp' => $noHp,
            'is_kontak_utama' => $this->boolean('is_kontak_utama'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'siswa_id' => 'nama siswa',
            'nama' => 'nama orang tua',
            'no_hp' => 'nomor HP',
            'no_wa' => 'nomor WhatsApp',
            'is_kontak_utama' => 'kontak utama',
            'pekerjaan' => 'pekerjaan',
        ];
    }
}
