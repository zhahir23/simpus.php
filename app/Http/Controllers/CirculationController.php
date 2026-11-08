<?php

namespace App\Http\Controllers;

use App\Actions\CheckoutBook;
use App\Actions\ReturnBook;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CirculationController extends Controller
{
    public function index(Request $request): View
    {
        $member = $request->filled('nim')
            ? User::query()->where('role', 'student')->where('student_number', $request->query('nim'))->first()
            : null;

        return view('simpus', [
            'page' => 'circulation',
            'member' => $member,
            'activeLoans' => $member
                ? $member->loans()->with('bookCopy.book')->whereNull('returned_at')->latest('borrowed_at')->get()
                : collect(),
            'recentLoans' => Loan::query()
                ->with(['user', 'bookCopy.book'])
                ->latest('borrowed_at')
                ->limit(8)
                ->get(),
        ]);
    }

    public function store(Request $request, CheckoutBook $checkout): RedirectResponse
    {
        $validated = $request->validate([
            'student_number' => ['required', 'string', 'max:30'],
            'barcode' => ['required', 'string', 'max:100'],
        ]);

        $member = User::query()
            ->where('role', 'student')
            ->where('student_number', $validated['student_number'])
            ->first();

        if (! $member) {
            return back()->withErrors([
                'student_number' => 'NIM tidak ditemukan di direktori mahasiswa.',
            ])->withInput();
        }

        $loan = $checkout->handle($member, $validated['barcode'], $request->user()->id);

        return redirect()->route('admin.circulation', ['nim' => $member->student_number])
            ->with('status', 'Peminjaman berhasil disimpan ke database.')
            ->with('loan_id', $loan->id);
    }

    public function storeReturn(Request $request, Loan $loan, ReturnBook $returnBook): RedirectResponse
    {
        $returnBook->handle($loan, $request->user()->id);

        return back()->with('status', 'Pengembalian eksemplar berhasil dicatat.');
    }

    public function renew(Loan $loan): RedirectResponse
    {
        $renewed = DB::transaction(function () use ($loan): bool {
            $loan = Loan::query()->lockForUpdate()->findOrFail($loan->id);

            if ($loan->returned_at || $loan->renewals >= 1 || $loan->fineAmount() > 0) {
                return false;
            }

            $loan->update([
                'due_at' => $loan->due_at->addDays(14),
                'renewals' => $loan->renewals + 1,
            ]);

            return true;
        });

        if (! $renewed) {
            return back()->withErrors([
                'loan' => 'Pinjaman tidak dapat diperpanjang: buku sudah kembali, sudah pernah diperpanjang, atau memiliki denda.',
            ]);
        }

        return back()->with('status', 'Masa pinjam berhasil diperpanjang 14 hari.');
    }
}
