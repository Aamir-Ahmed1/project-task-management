<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $result = $this->authService->login($validated);

            $request->session()->put('auth_token', $result['token']);

            $role = $result['user']->roles->first()?->name;

            $redirectRoute = match ($role) {
                'admin' => '/dashboard/admin',
                'project-manager' => '/dashboard/project-manager',
                'employee' => '/dashboard/employee',
                default => '/login',
            };

            return redirect($redirectRoute)->with('success', 'Login successful');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => $e->getMessage()])->withInput();
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        try {
            $this->authService->logout($request->user());
        } catch (\Exception $e) {
        }

        $request->session()->forget('auth_token');

        return redirect('/login');
    }

    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $this->authService->register($validated);

        return redirect('/login')->with('success', 'Registration successful. Please login.');
    }
}
