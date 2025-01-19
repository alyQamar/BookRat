<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = $request->input('title');
        $filter = $request->input('filter');
        $query = Book::when($title, fn($query, $title) => $query->title($title));

        $query = match ($filter) {
            'popular_last_month' => $query->popular(now()->subMonth(), now()),
            'popular_last_6month' => $query->popular(now()->subMonths(6), now()),
            'highest_rated_last_month' => $query->highestRated(now()->subMonth(), now()),
            'highest_rated_last_6months' => $query->highestRated(now()->subMonths(6), now()),
            default => $query->latest()
        };
        $cacheKey = 'books:' . $filter . ':' . $title;
        $books = Cache::remember($cacheKey, 3600, fn() => $query->get());
        return view('books.index', compact('books'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $cacheKey = 'books:' . $book->id;

        $book = Cache::remember($cacheKey, 3600, fn() => $book->load(['reviews' => fn($query) => $query->latest()]));
        return view('books.show', ['book' => $book,]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
