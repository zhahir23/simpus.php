<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function adminLogin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identity' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $admin = User::query()
            ->where('email', $validated['identity'])
            ->where('role', 'admin')
            ->first();

        if (! $admin || ! Auth::attempt([
            'email' => $admin->email,
            'password' => $validated['password'],
        ], $request->boolean('remember'))) {
            return back()
                ->withErrors(['identity' => 'Email admin atau kata sandi tidak sesuai.'])
                ->onlyInput('identity');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function studentLogin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identity' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $student = User::query()
            ->where('role', 'student')
            ->where(function ($query) use ($validated): void {
                $query->where('email', $validated['identity'])
                    ->orWhere('student_number', $validated['identity']);
            })
            ->first();

        if (! $student || ! Auth::attempt([
            'email' => $student->email,
            'password' => $validated['password'],
        ], $request->boolean('remember'))) {
            return back()
                ->withErrors(['identity' => 'NIM/email mahasiswa atau kata sandi tidak sesuai.'])
                ->onlyInput('identity');
        }

        if ($student->member_status !== 'active') {
            Auth::logout();

            return back()->withErrors([
                'identity' => 'Akun mahasiswa sedang diblokir. Hubungi petugas perpustakaan.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('student.portal'));
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'student_number' => ['required', 'string', 'max:30', 'unique:users,student_number'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'study_program' => ['required', 'string', 'max:120'],
            'cohort' => ['required', 'integer', 'between:2000,'.now()->year],
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => ['accepted'],
        ]);

        $student = User::query()->create([
            'name' => $validated['name'],
            'student_number' => $validated['student_number'],
            'email' => $validated['email'],
            'study_program' => $validated['study_program'],
            'cohort' => $validated['cohort'],
            'password' => $validated['password'],
            'role' => 'student',
            'member_status' => 'active',
        ]);

        Auth::login($student);
        $request->session()->regenerate();

        return redirect()->route('student.portal')
            ->with('status', 'Akun berhasil dibuat. Selamat datang di SIMPUS.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $loginRoute = $request->user()?->role === 'admin'
            ? 'admin.login'
            : 'student.login';

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($loginRoute);
    }
}
