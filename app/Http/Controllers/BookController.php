<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $books = Book::query()
            ->with('copies')
            ->withCount([
                'copies as available_copies_count' => fn ($query) => $query->where('status', BookCopy::STATUS_AVAILABLE),
            ])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->toString().'%';

                $query->where(fn ($books) => $books
                    ->where('title', 'like', $search)
                    ->orWhere('author', 'like', $search)
                    ->orWhere('isbn', 'like', $search));
            })
            ->orderBy('title')
            ->paginate(8)
            ->withQueryString();

        $selectedBook = $request->filled('book')
            ? Book::query()->with(['copies.loans.user'])->find($request->integer('book'))
            : null;

        $selectedBook ??= $books->first();

        return view('simpus', [
            'page' => 'catalog',
            'books' => $books,
            'selectedBook' => $selectedBook,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:30', 'unique:books,isbn'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'published_year' => ['nullable', 'integer', 'between:1000,'.now()->year],
            'synopsis' => ['nullable', 'string', 'max:3000'],
            'reference_only' => ['nullable', 'boolean'],
            'barcode' => ['required', 'string', 'max:100', 'unique:book_copies,barcode'],
            'shelf_location' => ['nullable', 'string', 'max:100'],
        ]);

        $book = DB::transaction(function () use ($validated, $request): Book {
            $referenceOnly = $request->boolean('reference_only');
            $book = Book::query()->create([
                ...collect($validated)->only([
                    'title',
                    'author',
                    'isbn',
                    'publisher',
                    'published_year',
                    'synopsis',
                ])->all(),
                'reference_only' => $referenceOnly,
            ]);

            $book->copies()->create([
                'barcode' => $validated['barcode'],
                'shelf_location' => $validated['shelf_location'] ?? null,
                'status' => $referenceOnly
                    ? BookCopy::STATUS_REFERENCE_ONLY
                    : BookCopy::STATUS_AVAILABLE,
            ]);

            return $book;
        });

        return redirect()->route('admin.books.index', ['book' => $book->id])
            ->with('status', 'Bibliografi dan eksemplar pertama berhasil disimpan.');
    }

    public function storeCopy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'barcode' => ['required', 'string', 'max:100', 'unique:book_copies,barcode'],
            'shelf_location' => ['nullable', 'string', 'max:100'],
        ]);

        $book = Book::query()->findOrFail($validated['book_id']);
        $copy = $book->copies()->create([
            'barcode' => $validated['barcode'],
            'shelf_location' => $validated['shelf_location'] ?? null,
            'status' => $book->reference_only
                ? BookCopy::STATUS_REFERENCE_ONLY
                : BookCopy::STATUS_AVAILABLE,
        ]);

        return redirect()->route('admin.books.index', ['book' => $book->id])
            ->with('status', 'Eksemplar '.$copy->barcode.' berhasil ditambahkan.');
    }
}
