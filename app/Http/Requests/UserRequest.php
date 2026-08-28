<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $user = $this->route('user');
        $userId = $user?->id;
        $isUpdate = $user !== null;

        return [
            'nama' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_GURU])],
            'password' => [$isUpdate ? 'nullable' : 'required', 'string', 'min:8'],
            'no_hp' => ['nullable', 'string', 'regex:/^[0-9+\s-]+$/', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
            'kirim_wa' => ['nullable', 'boolean'],

            // Field guru (hanya relevan saat role = guru)
            'nip' => ['nullable', 'required_if:role,guru', 'numeric', 'digits_between:1,30', Rule::unique('guru', 'nip')->ignore($user?->guru?->id)],
            'jabatan' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'kirim_wa'  => $this->boolean('kirim_wa'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'no_hp' => 'nomor HP',
            'nip' => 'NIP',
            'is_active' => 'status aktif',
        ];
    }
}
