<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    // Display all books
    public function index()
    {
        $data = DB::table('books')->get();

        return view('pages.page2', compact('data'));
    }

    // Show Add Form
    public function show_add_form()
    {
        return view('pages.add-book-form');
    }

    // Add book
    public function do_add(Request $request)
    {
        $filename = null;
        if ($request->file('photo')) {
            $file = $request->file('photo');
            $filename = $file->getClientOriginalName();
            $file->move('_uploads', $filename);
        }

        DB::table('books')->insert([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'country_id' => $request->input('country_id'),
            'stock' => $request->input('stock'),
            'amount' => $request->input('amount'),
            'photo' => $filename,
        ]);

        $bookId = DB::table('books')->insertGetId([
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'country_id'  => $request->input('country_id'),
            'stock'       => $request->input('stock'),
            'amount'      => $request->input('amount'),
            'photo'       => $filename ?? null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $this->writeActivityLog(
            'BOOK_CREATED',
            (int) $bookId,
            'A book record was created.',
            $request
        );

        return redirect(url('/p2'))->with('success', 'Book added successfully!');
    }

    // Delete book
    public function do_delete(Request $request, $id)
    {
        $book = DB::table('books')->where('id', $id)->first();

        if (! $book) {
            Log::warning('Book deletion attempted for a missing record', [
                'book_id'    => (int) $id,
                'ip_address' => $request->ip(),
            ]);
            abort(404, 'Book not found.');
        }

        DB::table('books')->where('id', $id)->delete();

        $this->writeActivityLog(
            'BOOK_DELETED',
            (int) $id,
            'A book record was deleted.',
            $request
        );

        return redirect(url('/p2'))->with('success', 'Book deleted!');
    }

    // Show Edit Form
    public function show_edit_form($id)
    {
        $data = DB::table('books')->where('id', $id)->first();

        return view('pages.edit-book-form', compact('data'));
    }

    // Update book
    public function do_update(Request $request)
    {
        $filename = $request->input('old_photo');
        if ($request->file('photo')) {
            $file = $request->file('photo');
            $filename = $file->getClientOriginalName();
            $file->move('_uploads', $filename);
        }

        $bookId = (int) $request->input('id');
        $bookExists = DB::table('books')->where('id', $bookId)->exists();

        if (! $bookExists) {
            Log::warning('Book update attempted for a missing record', [
                'book_id'    => $bookId,
                'ip_address' => $request->ip(),
            ]);
            abort(404, 'Book not found.');
        }

        DB::table('books')->where('id', $request->input('id'))->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'country_id' => $request->input('country_id'),
            'stock' => $request->input('stock'),
            'amount' => $request->input('amount'),
            'photo' => $filename,
        ]);
        
        $this->writeActivityLog(
            'BOOK_UPDATED',
            $bookId,
            'A book record was updated.',
            $request
        );

        return redirect(url('/p2'))->with('success', 'Book updated successfully!');
    }

    private function writeActivityLog(
        string $action,
        ?int $bookId,
        string $description,
        Request $request
    ): void {
        // 1. Write to storage/logs/laravel.log
        Log::info('Book activity', [
            'action'      => $action,
            'book_id'     => $bookId,
            'description' => $description,
            'ip_address'  => $request->ip(),
            'http_method' => $request->method(),
            'url'         => $request->path(),
        ]);

        // 2. Insert record into database table
        DB::table('activity_logs')->insert([
            'user_id'     => null, // Null for now since auth is not yet implemented
            'action'      => $action,
            'entity_type' => 'Book',
            'entity_id'   => $bookId,
            'description' => $description,
            'ip_address'  => $request->ip(),
            'http_method' => $request->method(),
            'url'         => $request->path(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}
