<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            SystemLog::write('user.login', 'info', [
                'email' => $request->email,
            ]);

            return redirect()->intended(route('dashboard'));
        }

        SystemLog::write('user.login_failed', 'warning', [
            'email' => $request->email,
        ]);

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Невірний email або пароль.']);
    }

    public function logout(Request $request): RedirectResponse
    {
        SystemLog::write('user.logout', 'info');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
