<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PengumumanRequest extends FormRequest
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
            'judul' => ['required', 'string', 'max:200'],
            'konten' => ['required', 'string'],
            'target_role' => ['nullable', Rule::in(['semua', 'guru', 'wali', 'admin'])],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'tanggal_publish' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'target_role' => 'target peran',
            'tanggal_publish' => 'tanggal publish',
            'is_active' => 'status aktif',
        ];
    }
}
