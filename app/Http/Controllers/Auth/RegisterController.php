<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function show(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login'    => ['required', 'string', 'min:3', 'max:64', 'alpha_dash', 'unique:users,login'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:32', 'regex:/^\+?[0-9\s\-\(\)]{7,20}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'login.unique'   => 'Такой логин уже занят.',
            'email.unique'   => 'Этот email уже зарегистрирован.',
            'phone.regex'    => 'Телефон указан в неверном формате.',
            'password.min'   => 'Пароль должен быть не короче 8 символов.',
            'password.confirmed' => 'Пароли не совпадают.',
        ]);

        $user = User::create([
            'login'    => $data['login'],
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);

        return redirect()
            ->route('dashboard')
            ->with('flash', 'Добро пожаловать, ' . $user->login . '!');
    }
}
