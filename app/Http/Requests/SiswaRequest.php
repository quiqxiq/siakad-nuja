<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('jenis_kelamin')) {
            $jk = strtoupper(trim((string) $this->input('jenis_kelamin')));
            if ($jk === 'L' || str_starts_with($jk, 'LAKI')) {
                $this->merge(['jenis_kelamin' => 'L']);
            } elseif ($jk === 'P' || str_starts_with($jk, 'PEREMPUAN')) {
                $this->merge(['jenis_kelamin' => 'P']);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $siswaId = $this->route('siswa')?->id;

        $currentYear = (int) date('Y');

        return [
            'nis' => ['required', 'numeric', 'digits_between:1,30', Rule::unique('siswa', 'nis')->ignore($siswaId)],
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P', 'Laki-laki', 'Perempuan'])],
            'alamat' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['nullable', Rule::in(['Aktif', 'Lulus', 'Pindah', 'Keluar'])],
            'tahun_masuk' => ['required', 'integer', 'min:1990', 'max:' . $currentYear],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nis' => 'NIS',
            'nama_lengkap' => 'nama lengkap',
            'kelas_id' => 'kelas',
            'tanggal_lahir' => 'tanggal lahir',
            'jenis_kelamin' => 'jenis kelamin',
            'tahun_masuk' => 'tahun masuk',
        ];
    }
}
