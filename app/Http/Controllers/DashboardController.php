<?php

namespace App\Http\Controllers;

use App\Models\BookCopy;
use App\Models\Loan;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->toDateString();
        $availableCopies = BookCopy::query()->where('status', BookCopy::STATUS_AVAILABLE)->count();
        $totalCopies = BookCopy::query()->count();
        $chartData = collect(range(6, 0))
            ->map(function (int $daysAgo): array {
                $date = now()->subDays($daysAgo);

                return [
                    'label' => $date->translatedFormat('j M'),
                    'count' => Loan::query()->whereDate('borrowed_at', $date)->count(),
                ];
            });

        return view('simpus', [
            'page' => 'dashboard',
            'metrics' => [
                'borrowed_today' => Loan::query()->whereDate('borrowed_at', $today)->count(),
                'returned_today' => Loan::query()->whereDate('returned_at', $today)->count(),
                'active_loans' => Loan::query()->whereNull('returned_at')->count(),
                'overdue_copies' => Loan::query()->whereNull('returned_at')->where('due_at', '<', now()->startOfDay())->count(),
            ],
            'recentLoans' => Loan::query()
                ->with(['user', 'bookCopy.book', 'processedBy'])
                ->latest('borrowed_at')
                ->limit(8)
                ->get(),
            'chartData' => $chartData,
            'attentionLoans' => Loan::query()
                ->with(['user', 'bookCopy.book'])
                ->whereNull('returned_at')
                ->whereBetween('due_at', [now()->startOfDay(), now()->addDays(2)->endOfDay()])
                ->orderBy('due_at')
                ->limit(5)
                ->get(),
            'availableCopies' => $availableCopies,
            'availablePercentage' => $totalCopies
                ? (int) round($availableCopies / $totalCopies * 100)
                : 0,
        ]);
    }
}
