<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $members = User::query()
            ->where('role', 'student')
            ->with(['loans' => fn ($query) => $query->whereNull('returned_at')->with('bookCopy.book')])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->toString().'%';

                $query->where(fn ($users) => $users
                    ->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('student_number', 'like', $search));
            })
            ->when($request->query('status') === 'blocked', fn ($query) => $query->where('member_status', 'blocked'))
            ->orderBy('name')
            ->paginate(8)
            ->withQueryString();

        return view('simpus', [
            'page' => 'members',
            'members' => $members,
            'blockedCount' => User::query()->where('role', 'student')->where('member_status', 'blocked')->count(),
            'activeLoans' => Loan::query()
                ->with(['user', 'bookCopy.book'])
                ->whereNull('returned_at')
                ->where('due_at', '<', now()->startOfDay())
                ->orderBy('due_at')
                ->limit(20)
                ->get(),
            'loanTotals' => [
                'borrowed' => Loan::query()->whereBetween('borrowed_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
                'returned' => Loan::query()->whereBetween('returned_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'student_number' => ['required', 'string', 'max:30', 'unique:users,student_number'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'study_program' => ['required', 'string', 'max:120'],
            'cohort' => ['required', 'integer', 'between:2000,'.now()->year],
        ]);

        $temporaryPassword = Str::password(16);

        User::query()->create([
            ...$validated,
            'password' => $temporaryPassword,
            'role' => 'student',
            'member_status' => 'active',
        ]);

        return redirect()->route('admin.members.index')
            ->with('status', 'Anggota berhasil dibuat. Sampaikan kata sandi sementara ini kepada anggota.')
            ->with('temporary_password', $temporaryPassword);
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        abort_unless($user->role === 'student', 404);

        $user->update([
            'member_status' => $user->member_status === 'active' ? 'blocked' : 'active',
        ]);

        return back()->with('status', 'Status keanggotaan berhasil diperbarui.');
    }
}
