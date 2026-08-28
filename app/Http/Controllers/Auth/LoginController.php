<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $input = trim((string) $request->input('email'));
        $password = (string) $request->input('password');

        // Resolve email jika user memasukkan username (admin/guru1) atau NIP
        $targetEmail = $input;
        if (! filter_var($input, FILTER_VALIDATE_EMAIL)) {
            if ($input === 'admin') {
                $targetEmail = 'admin@siakadnuja.sch.id';
            } elseif (preg_match('/^guru\d+$/i', $input)) {
                $targetEmail = strtolower($input) . '@siakadnuja.sch.id';
            } else {
                $guru = \App\Models\Guru::where('nip', $input)->first();
                if ($guru !== null && $guru->user !== null) {
                    $targetEmail = $guru->user->email;
                }
            }
        }

        // Cek status keaktifan user terlebih dahulu sebelum attempt
        $user = \App\Models\User::where('email', $targetEmail)->first();
        if ($user !== null && ! $user->is_active) {
            return back()->withErrors([
                'email' => 'Akun Anda dinonaktifkan. Hubungi administrator.',
            ])->onlyInput('email');
        }

        $credentials = [
            'email' => $targetEmail,
            'password' => $password,
            'is_active' => true,
        ];

        if (Auth::attempt($credentials, (bool) $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
