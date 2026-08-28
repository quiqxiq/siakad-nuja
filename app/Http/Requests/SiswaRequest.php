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
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
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
