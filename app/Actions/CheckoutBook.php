<?php

namespace App\Actions;

use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutBook
{
    public function handle(User $member, string $barcode, ?int $staffId = null): Loan
    {
        return DB::transaction(function () use ($member, $barcode, $staffId): Loan {
            $member = User::query()->lockForUpdate()->findOrFail($member->id);

            if ($member->role !== 'student' || $member->member_status !== 'active') {
                throw ValidationException::withMessages([
                    'student_number' => 'Akun mahasiswa tidak aktif atau sedang diblokir.',
                ]);
            }

            if ($member->loans()->whereNull('returned_at')->count() >= 3) {
                throw ValidationException::withMessages([
                    'student_number' => 'Kuota peminjaman mahasiswa sudah mencapai tiga buku.',
                ]);
            }

            if ($member->loans()
                ->whereNull('returned_at')
                ->where('due_at', '<', now()->startOfDay())
                ->exists()) {
                throw ValidationException::withMessages([
                    'student_number' => 'Mahasiswa memiliki pinjaman terlambat dan belum dapat meminjam buku lain.',
                ]);
            }

            $copy = BookCopy::query()
                ->where('barcode', $barcode)
                ->lockForUpdate()
                ->with('book')
                ->first();

            if (! $copy || $copy->status !== BookCopy::STATUS_AVAILABLE || $copy->book->reference_only) {
                throw ValidationException::withMessages([
                    'barcode' => 'Eksemplar tidak ditemukan, tidak tersedia, atau hanya boleh dibaca di tempat.',
                ]);
            }

            $loan = $member->loans()->create([
                'book_copy_id' => $copy->id,
                'processed_by' => $staffId,
                'borrowed_at' => now(),
                'due_at' => now()->addDays(14)->endOfDay(),
            ]);

            $copy->update(['status' => BookCopy::STATUS_BORROWED]);

            return $loan;
        });
    }
}
