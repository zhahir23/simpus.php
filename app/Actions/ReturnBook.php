<?php

namespace App\Actions;

use App\Models\BookCopy;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnBook
{
    public function handle(Loan $loan, ?int $staffId = null): Loan
    {
        return DB::transaction(function () use ($loan, $staffId): Loan {
            $loan = Loan::query()
                ->lockForUpdate()
                ->with('bookCopy.book')
                ->findOrFail($loan->id);

            if ($loan->returned_at) {
                throw ValidationException::withMessages([
                    'loan' => 'Eksemplar ini sudah tercatat kembali.',
                ]);
            }

            $loan->update([
                'returned_at' => now(),
                'processed_by' => $staffId ?? $loan->processed_by,
            ]);

            $loan->bookCopy->update([
                'status' => $loan->bookCopy->book->reference_only
                    ? BookCopy::STATUS_REFERENCE_ONLY
                    : BookCopy::STATUS_AVAILABLE,
            ]);

            return $loan;
        });
    }
}
