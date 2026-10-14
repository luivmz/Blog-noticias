<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required|string'], ['email.required' => 'Escribe tu correo electrónico.', 'email.email' => 'Escribe un correo electrónico válido.', 'password.required' => 'Escribe tu contraseña.']);
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages(['email' => 'El correo o la contraseña no son correctos.']);
        }
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Sesión cerrada correctamente.');
    }
}
