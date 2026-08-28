<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuruRequest extends FormRequest
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
        $guru = $this->route('guru');
        $guruId = $guru?->id;
        $userId = $guru?->user_id;
        $isUpdate = $guru !== null;

        return [
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'nip' => ['required', 'numeric', 'digits_between:1,30', Rule::unique('guru', 'nip')->ignore($guruId)],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            'password' => [$isUpdate ? 'nullable' : 'required', 'string', 'min:8'],
            'jabatan' => ['nullable', 'string', 'max:50'],
            'no_hp' => ['nullable', 'string', 'regex:/^[0-9+\s-]+$/', 'max:20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama_lengkap' => 'nama lengkap',
            'nip' => 'NIP',
            'no_hp' => 'nomor HP',
        ];
    }
}
