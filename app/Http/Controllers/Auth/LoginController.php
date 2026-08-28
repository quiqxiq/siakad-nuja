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

        // Jika user mengetik 'admin' atau 'guru1', auto-resolve ke email siakadnuja.sch.id
        if (! filter_var($input, FILTER_VALIDATE_EMAIL)) {
            if ($input === 'admin') {
                $input = 'admin@siakadnuja.sch.id';
            } elseif (preg_match('/^guru\d+$/i', $input)) {
                $input = strtolower($input) . '@siakadnuja.sch.id';
            }
        }

        $credentials = [
            'email' => $input,
            'password' => $password,
        ];

        if (Auth::attempt($credentials, (bool) $request->boolean('remember'))) {
            $user = Auth::user();

            if ($user !== null && ! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun Anda dinonaktifkan. Hubungi administrator.',
                ])->onlyInput('email');
            }

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
