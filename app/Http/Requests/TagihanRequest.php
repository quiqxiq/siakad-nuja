<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TagihanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'siswa_id'        => 'required_without:kelas_id_massal|nullable|exists:siswa,id',
            'kelas_id_massal' => 'nullable|exists:kelas,id',
            'judul'           => 'required|string|max:200',
            'jenis'           => 'required|string|max:50',
            'periode'         => [
                'required',
                'string',
                'max:50',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (preg_match_all('/\b(20\d{2})\b/', (string) $value, $matches)) {
                        $currentYear = (int) date('Y');
                        foreach ($matches[1] as $year) {
                            if ((int) $year > $currentYear) {
                                $fail("Tahun pada periode tagihan tidak boleh melebihi tahun saat ini ({$currentYear}).");
                                return;
                            }
                        }
                    }
                },
            ],
            'nominal'         => 'required|numeric|min:0',
            'jatuh_tempo'     => 'nullable|date',
            'keterangan'      => 'nullable|string|max:500',
        ];
    }

    public function attributes(): array
    {
        return [
            'siswa_id'    => 'siswa',
            'jatuh_tempo' => 'jatuh tempo',
            'nominal'     => 'nominal tagihan',
        ];
    }
}
