<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    protected $fillable = [
        'user_id',
        'book_copy_id',
        'processed_by',
        'borrowed_at',
        'due_at',
        'returned_at',
        'fine_paid_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookCopy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function fineAmount(): int
    {
        if ($this->fine_paid_at || $this->due_at->isToday() || $this->due_at->isFuture()) {
            return 0;
        }

        return (int) $this->due_at->copy()->startOfDay()->diffInDays(now()->startOfDay()) * 1000;
    }

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
            'fine_paid_at' => 'datetime',
            'renewals' => 'integer',
        ];
    }
}
