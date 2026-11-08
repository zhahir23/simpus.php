<?php

namespace App\Http\Controllers;

use App\Actions\CheckoutBook;
use App\Actions\ReturnBook;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Loan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentPortalController extends Controller
{
    private const RECOMMENDATION_STOP_WORDS = [
        'a',
        'and',
        'dalam',
        'dari',
        'for',
        'guide',
        'ini',
        'ke',
        'of',
        'pada',
        'the',
        'to',
        'untuk',
        'yang',
    ];

    public function index(Request $request): View
    {
        $student = $request->user();
        $books = Book::query()
            ->with(['copies' => fn ($query) => $query->where('status', 'available')])
            ->withCount([
                'copies',
                'copies as available_copies_count' => fn ($query) => $query->where('status', 'available'),
            ])
            ->orderBy('title')
            ->limit(12)
            ->get();
        $viewedBook = $request->filled('book')
            ? $books->firstWhere('id', $request->integer('book'))
            : null;
        $recommendedBooks = collect();

        if ($viewedBook) {
            $viewedTerms = $this->bookKeywords($viewedBook->title.' '.$viewedBook->synopsis);
            $rankedBooks = $books
                ->reject(fn (Book $book): bool => $book->is($viewedBook))
                ->map(function (Book $book) use ($viewedBook, $viewedTerms): array {
                    $sharedTerms = array_intersect(
                        $viewedTerms,
                        $this->bookKeywords($book->title.' '.$book->synopsis),
                    );
                    $sameAuthor = mb_strtolower(trim($book->author)) === mb_strtolower(trim($viewedBook->author));
                    $samePublisher = filled($book->publisher)
                        && filled($viewedBook->publisher)
                        && mb_strtolower(trim($book->publisher)) === mb_strtolower(trim($viewedBook->publisher));
                    $score = count($sharedTerms) + ($sameAuthor ? 5 : 0) + ($samePublisher ? 2 : 0);
                    $reasons = [];

                    if ($sameAuthor) {
                        $reasons[] = 'Penulis yang sama';
                    }

                    if ($samePublisher) {
                        $reasons[] = 'Penerbit yang sama';
                    }

                    if ($sharedTerms !== []) {
                        $reasons[] = 'Topik serupa';
                    }

                    return [
                        'book' => $book,
                        'score' => $score,
                        'reason' => implode(' · ', $reasons),
                    ];
                });
            $recommendedBooks = $rankedBooks
                ->filter(fn (array $recommendation): bool => $recommendation['score'] > 0)
                ->sortByDesc('score')
                ->take(4)
                ->values();

            if ($recommendedBooks->isEmpty()) {
                $recommendedBooks = $books
                    ->reject(fn (Book $book): bool => $book->is($viewedBook))
                    ->filter(fn (Book $book): bool => $book->available_copies_count > 0 && ! $book->reference_only)
                    ->take(4)
                    ->map(fn (Book $book): array => [
                        'book' => $book,
                        'score' => 0,
                        'reason' => 'Alternatif lain yang tersedia',
                    ])
                    ->values();
            }
        }

        return view('simpus', [
            'page' => 'portal',
            'books' => $books,
            'viewedBook' => $viewedBook,
            'recommendedBooks' => $recommendedBooks,
            'activeLoans' => $student->loans()
                ->with('bookCopy.book')
                ->whereNull('returned_at')
                ->orderBy('due_at')
                ->get(),
            'loanHistory' => $student->loans()
                ->with('bookCopy.book')
                ->latest('borrowed_at')
                ->limit(10)
                ->get(),
            'recentLoan' => $request->filled('loan')
                ? $student->loans()->with('bookCopy.book')->find($request->integer('loan'))
                : null,
        ]);
    }

    /**
     * @return list<string>
     */
    private function bookKeywords(string $text): array
    {
        $keywords = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique(array_filter(
            $keywords,
            fn (string $keyword): bool => mb_strlen($keyword) >= 4
                && ! in_array($keyword, self::RECOMMENDATION_STOP_WORDS, true),
        )));
    }

    public function checkout(Request $request): View
    {
        $student = $request->user();
        $loan = $request->filled('loan')
            ? $student->loans()->with('bookCopy.book')->find($request->integer('loan'))
            : null;

        $copy = $request->filled('barcode')
            ? BookCopy::query()->with('book')->where('barcode', $request->query('barcode'))->first()
            : null;

        return view('simpus', [
            'page' => 'self-checkout',
            'state' => $loan ? 'success' : ($copy?->book->reference_only ? 'blocked' : $request->query('state', 'confirm')),
            'loan' => $loan,
            'copy' => $copy,
            'activeLoanCount' => $student->loans()->whereNull('returned_at')->count(),
        ]);
    }

    public function storeLoan(Request $request, CheckoutBook $checkout): RedirectResponse
    {
        $validated = $request->validate([
            'barcode' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($validated['password'], $request->user()->password)) {
            return back()->withErrors(['password' => 'Kata sandi akun tidak sesuai.']);
        }

        $loan = $checkout->handle($request->user(), $validated['barcode']);

        return redirect()->route('student.checkout', ['loan' => $loan->id])
            ->with('status', 'Peminjaman berhasil disimpan.');
    }

    public function storeReturn(Request $request, Loan $loan, ReturnBook $returnBook): RedirectResponse
    {
        abort_unless($loan->user_id === $request->user()->id, 404);
        $returnBook->handle($loan);

        return back()->with('status', 'Pengembalian eksemplar berhasil dicatat.');
    }
}
