<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Book;

class BookQueryController extends Controller
{
    public function index(Request $request)
    {
        // 12. Read and validate the input (0 to 1000)
        $validated = $request->validate([
            'stock_limit' => ['nullable', 'integer', 'between:0,1000'],
        ]);

        $search = $request->input('search', '');
        $lowStock = $request->boolean('low_stock');
        $stockThreshold = (int) ($validated['stock_limit'] ?? 10);

        // 13. Query Builder using the threshold
        $queryBuilderBooks = DB::table('books')
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', '%' . $search . '%');
            })
            ->when($lowStock, function ($query) use ($stockThreshold) {
                $query->where('stock', '<', $stockThreshold);
            })
            ->orderBy('amount')
            ->limit(20)
            ->get();

        // 13. Eloquent ORM using the same threshold
        $eloquentBooks = Book::query()
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', '%' . $search . '%');
            })
            ->when($lowStock, function ($query) use ($stockThreshold) {
                $query->where('stock', '<', $stockThreshold);
            })
            ->orderBy('amount')
            ->limit(20)
            ->get();

        // 14. Pass $stockThreshold to the view
        return view('pages.book-query-comparison', compact(
            'search', 'lowStock', 'stockThreshold',
            'queryBuilderBooks', 'eloquentBooks'
        ));
    }
}