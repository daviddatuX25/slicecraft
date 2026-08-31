```python
markdown_content = """# Prof Elective 5 Advanced Web Development: Back End
## Worksheet 2.4 – CRUD in Laravel: Books

**Objective:** Create a simple CRUD (Create, Read, Update, Delete) functionality for managing Books in your existing Laravel project.

---

### A. Database Configuration

#### 1. Create the database
* Open phpMyAdmin and create a new database named:
  `laravel_project_db`

#### 2. Edit the `.env` file
* Open your Laravel project folder.
* Locate and open the `.env` file.
* Find the line that begins with `DB_DATABASE=` (around line 25) and set it as:
  `DB_DATABASE=laravel_project_db`

#### 3. Create a migration for the books table
```bash
php artisan make:migration create_books_table --create=books

```

*(Note: use `--create=books` instead of `--table=book` for correct naming convention.)*

#### 4. Edit the migration file

Open the file located in `database/migrations/` (something like `2025_11_09_000000_create_books_table.php`) and replace the contents with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();$table->string('title');
            $table->longText('description');$table->integer('country_id');
            $table->integer('stock');$table->decimal('amount', 8, 2);
            $table->string('photo')->nullable();$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};

```

#### 5. Run the migration

```bash
php artisan migrate

```

* Check your database to ensure the `books` table was created successfully.

#### 6. Create a Book model

```bash
php artisan make:model Book

```

* The model will be located at `app/Models/Book.php` (not in `app/Http/Models/`).

#### 7. Create a Book factory

```bash
php artisan make:factory BookFactory --model=Book

```

* Open `database/factories/BookFactory.php` and replace with:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->text(),
            'country_id' => $this->faker->numberBetween(1, 100),
            'stock' => $this->faker->numberBetween(1, 1000),             'amount' =>$this->faker->randomFloat(2, 10, 9999),
            'photo' => 'default.jpg',
        ];
    }
}

```

#### 8. Create a Book seeder

```bash
php artisan make:seeder BookSeeder

```

* Open `database/seeders/BookSeeder.php` and replace with:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        Book::factory(100)->create();
    }
}

```

#### 9. Run migration and seed data

```bash
php artisan migrate:fresh --seed

```

* Check your database; it should contain 100 book records.

---

### B. Controller

#### 1. Create the BookController

```bash
php artisan make:controller BookController

```

#### 2. Edit `BookController.php`

*(located at `app/Http/Controllers/BookController.php`)*

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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
            $file =$request->file('photo');
            $filename =$file->getClientOriginalName();
            $file->move('_uploads',$filename);
        }

        DB::table('books')->insert([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'country_id' => $request->input('country_id'),
            'stock' => $request->input('stock'),
            'amount' => $request->input('amount'),
            'photo' => $filename
        ]);

        return redirect(url('/p2'))->with('success', 'Book added successfully!');
    }

    // Delete book
    public function do_delete($id)
    {
        DB::table('books')->where('id', $id)->delete();
        return redirect(url('/p2'))->with('success', 'Book deleted!');
    }

    // Show Edit Form
    public function show_edit_form($id)
    {
        $data = DB::table('books')->where('id',$id)->first();
        return view('pages.edit-book-form', compact('data'));
    }

    // Update book
    public function do_update(Request $request)
    {
        $filename =$request->input('old_photo');
        if ($request->file('photo')) {
            $file =$request->file('photo');
            $filename =$file->getClientOriginalName();
            $file->move('_uploads',$filename);
        }

        DB::table('books')->where('id', $request->input('id'))->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'country_id' => $request->input('country_id'),
            'stock' => $request->input('stock'),
            'amount' => $request->input('amount'),
            'photo' => $filename
        ]);

        return redirect(url('/p2'))->with('success', 'Book updated successfully!');
    }
}

```

---

### C. Views (Blade Templates)

#### 1. Add the following codes in your `page2.blade.php` (Book Management) file.

```html
@extends('layouts.app')

@section('content')
@if(Session::has('success'))
<p class="alert alert-success">{{ Session::get('success') }}</p>
@endif

<x-page-header pagetitle="Manage Books" class="bg-info"/>

<div class="wrapper wrapper-content">
    <div class="animated fadeInRightBig">
        <a href="{{ url('/book-form') }}" class="btn btn-primary mb-3">Add Book</a>
        <div class="row">
            @foreach($data as$d)
            <div class="col-md-3 mb-4">
                <div class="card">
                    <img class="card-img-top" src="{{ asset('_uploads/'.$d->photo) }}" alt="Book Image" style="width:100%">
                    <div class="card-body">
                        <h5 class="card-title">{{ $d->title }}</h5>
                        <p class="card-text">
                            Country ID: {{ $d->country_id }}<br>
                            Stock: {{ $d->stock }}<br>
                            Price: ₱{{ number_format($d->amount, 2) }}
                        </p>
                        <a href="{{ url('/edit-book/'.$d->id) }}" class="btn btn-info btn-sm" onclick="return confirm('Update this book?')"><i class="fa fa-edit"></i></a>
                        <a href="{{ url('/delete-book/'.$d->id) }}" class="btn btn-danger btn-sm" onclick="return confirm('Delete this book?')"><i class="fa fa-trash"></i></a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

```

#### 2. Create `add-book-form.blade.php` file in `pages` folder and insert the following codes:

```html
@extends('layouts.app')

@section('content')
<x-page-header pagetitle="Add Book" class="bg-warning"/>

<div class="wrapper wrapper-content">
    <div class="animated fadeInRightBig">
        <form action="{{ url('/add-book') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" name="title" id="title" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="description">Description</label>
                        <textarea class="form-control" name="description" id="description"></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="country_id">Country Code</label>
                        <input type="number" class="form-control" name="country_id" id="country_id">
                    </div>
                    <div class="form-group mb-3">
                        <label for="stock">Stock</label>
                        <input type="number" class="form-control" name="stock" id="stock">
                    </div>
                    <div class="form-group mb-3">
                        <label for="amount">Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount" id="amount">
                    </div>
                    <div class="form-group mb-3">
                        <label for="photo">Photo</label>
                        <input type="file" class="form-control" name="photo" id="photo">
                    </div>
                    <hr>
                    <button class="btn btn-primary" type="submit">Save</button>
                    <a href="{{ url('/p2') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

```

#### 3. Create `edit-book-form.blade.php` file in `pages` folder and insert the following codes:

```html
@extends('layouts.app')

@section('content')
<x-page-header pagetitle="Edit Book" class="bg-warning"/>

<div class="wrapper wrapper-content">
    <div class="animated fadeInRightBig">
        <form action="{{ url('/update-book') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <input type="hidden" name="id" value="{{ $data->id }}">
                    <input type="hidden" name="old_photo" value="{{ $data->photo }}">
                    
                    <div class="form-group mb-3">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" name="title" id="title" required value="{{ $data->title }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="description">Description</label>
                        <textarea class="form-control" name="description" id="description">{{ $data->description }}</textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="country_id">Country Code</label>
                        <input type="number" class="form-control" name="country_id" id="country_id" value="{{ $data->country_id }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="stock">Stock</label>
                        <input type="number" class="form-control" name="stock" id="stock" value="{{ $data->stock }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="amount">Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount" id="amount" value="{{ $data->amount }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="photo">Photo</label>
                        <input type="file" class="form-control" name="photo" id="photo">
                        <small class="text-muted">Current:</small><br>
                        <img src="{{ asset('_uploads/'.$data->photo) }}" width="100" class="border mt-1">
                    </div>
                    <hr>
                    <button class="btn btn-primary" type="submit">Update</button>
                    <a href="{{ url('/p2') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

```

---

### D. Routing Setup (Final Step – Required)

Add these routes to your `routes/web.php` file:

```php
use App\Http\Controllers\BookController;

Route::get('/p2', [BookController::class, 'index']);
Route::get('/book-form', [BookController::class, 'show_add_form']);
Route::post('/add-book', [BookController::class, 'do_add']);
Route::get('/edit-book/{id}', [BookController::class, 'show_edit_form']);
Route::post('/update-book', [BookController::class, 'do_update']);
Route::get('/delete-book/{id}', [BookController::class, 'do_delete']);

```

---

### E. Presentation and Scoring Sheet

* Print the next page upon your presentation of this activity before due date.
* Prepare for Q and A, 2-3 questions per member.
* If something is not working, figure it out.

---

**Name:** _____________________________________________________ **Date:** _________________ **Score:** ______________

#### Score Sheet

##### A. Checklist of Files Created (90pts @ 10pts each)

| Type | Path | Purpose | Score |
| --- | --- | --- | --- |
| **Migration** | `database/migrations/create_books_table.php` | Table structure |  |
| **Model** | `app/Models/Book.php` | ORM model |  |
| **Factory** | `database/factories/BookFactory.php` | Sample data generator |  |
| **Seeder** | `database/seeders/BookSeeder.php` | Inserts 100 records |  |
| **Controller** | `app/Http/Controllers/BookController.php` | CRUD logic |  |
| **Views** | `resources/views/pages/page2.blade.php` | Display books |  |
| **Views** | `resources/views/pages/add-book-form.blade.php` | Add form |  |
| **Views** | `resources/views/pages/edit-book-form.blade.php` | Edit form |  |
| **Routes** | `routes/web.php` | URL definitions |  |
| **Total(A)** |  |  |  |

##### B. Submission and Presentation

| Criteria | Task | Score |
| --- | --- | --- |
| **Submission** | Presented before due date. (+10) |  |
| **Member 1** | Deductions if presentation and questioning are not satisfactory (-10 per question) |  |
| **Member 2** | Deductions if presentation and questioning are not satisfactory (-10 per question) |  |
| **Total(B)** |  |  |
| **Total(A + B)** |  |  |
| """ |  |  |

with open("worksheet_2.4_crud_laravel.md", "w", encoding="utf-8") as f:
f.write(markdown_content)

```

Your Markdown file is ready
[file-tag: code-generated-file-9021bdaf-cac9-4d39-9b83-ad44ab1fa4a7]

You can copy the clean, formatted Markdown text below or download the generated file:

```markdown
# Prof Elective 5 Advanced Web Development: Back End
## Worksheet 2.4 – CRUD in Laravel: Books

**Objective:** Create a simple CRUD (Create, Read, Update, Delete) functionality for managing Books in your existing Laravel project.

---

### A. Database Configuration

#### 1. Create the database
* Open phpMyAdmin and create a new database named:
  `laravel_project_db`

#### 2. Edit the `.env` file
* Open your Laravel project folder.
* Locate and open the `.env` file.
* Find the line that begins with `DB_DATABASE=` (around line 25) and set it as:
  `DB_DATABASE=laravel_project_db`

#### 3. Create a migration for the books table
```bash
php artisan make:migration create_books_table --create=books

```

*(Note: use `--create=books` instead of `--table=book` for correct naming convention.)*

#### 4. Edit the migration file

Open the file located in `database/migrations/` (something like `2025_11_09_000000_create_books_table.php`) and replace the contents with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();$table->string('title');
            $table->longText('description');$table->integer('country_id');
            $table->integer('stock');$table->decimal('amount', 8, 2);
            $table->string('photo')->nullable();$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};

```

#### 5. Run the migration

```bash
php artisan migrate

```

* Check your database to ensure the `books` table was created successfully.

#### 6. Create a Book model

```bash
php artisan make:model Book

```

* The model will be located at `app/Models/Book.php` (not in `app/Http/Models/`).

#### 7. Create a Book factory

```bash
php artisan make:factory BookFactory --model=Book

```

* Open `database/factories/BookFactory.php` and replace with:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->text(),
            'country_id' => $this->faker->numberBetween(1, 100),
            'stock' => $this->faker->numberBetween(1, 1000),             'amount' =>$this->faker->randomFloat(2, 10, 9999),
            'photo' => 'default.jpg',
        ];
    }
}

```

#### 8. Create a Book seeder

```bash
php artisan make:seeder BookSeeder

```

* Open `database/seeders/BookSeeder.php` and replace with:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        Book::factory(100)->create();
    }
}

```

#### 9. Run migration and seed data

```bash
php artisan migrate:fresh --seed

```

* Check your database; it should contain 100 book records.

---

### B. Controller

#### 1. Create the BookController

```bash
php artisan make:controller BookController

```

#### 2. Edit `BookController.php`

*(located at `app/Http/Controllers/BookController.php`)*

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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
            $file =$request->file('photo');
            $filename =$file->getClientOriginalName();
            $file->move('_uploads',$filename);
        }

        DB::table('books')->insert([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'country_id' => $request->input('country_id'),
            'stock' => $request->input('stock'),
            'amount' => $request->input('amount'),
            'photo' => $filename
        ]);

        return redirect(url('/p2'))->with('success', 'Book added successfully!');
    }

    // Delete book
    public function do_delete($id)
    {
        DB::table('books')->where('id', $id)->delete();
        return redirect(url('/p2'))->with('success', 'Book deleted!');
    }

    // Show Edit Form
    public function show_edit_form($id)
    {
        $data = DB::table('books')->where('id',$id)->first();
        return view('pages.edit-book-form', compact('data'));
    }

    // Update book
    public function do_update(Request $request)
    {
        $filename =$request->input('old_photo');
        if ($request->file('photo')) {
            $file =$request->file('photo');
            $filename =$file->getClientOriginalName();
            $file->move('_uploads',$filename);
        }

        DB::table('books')->where('id', $request->input('id'))->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'country_id' => $request->input('country_id'),
            'stock' => $request->input('stock'),
            'amount' => $request->input('amount'),
            'photo' => $filename
        ]);

        return redirect(url('/p2'))->with('success', 'Book updated successfully!');
    }
}

```

---

### C. Views (Blade Templates)

#### 1. Add the following codes in your `page2.blade.php` (Book Management) file.

```html
@extends('layouts.app')

@section('content')
@if(Session::has('success'))
<p class="alert alert-success">{{ Session::get('success') }}</p>
@endif

<x-page-header pagetitle="Manage Books" class="bg-info"/>

<div class="wrapper wrapper-content">
    <div class="animated fadeInRightBig">
        <a href="{{ url('/book-form') }}" class="btn btn-primary mb-3">Add Book</a>
        <div class="row">
            @foreach($data as$d)
            <div class="col-md-3 mb-4">
                <div class="card">
                    <img class="card-img-top" src="{{ asset('_uploads/'.$d->photo) }}" alt="Book Image" style="width:100%">
                    <div class="card-body">
                        <h5 class="card-title">{{ $d->title }}</h5>
                        <p class="card-text">
                            Country ID: {{ $d->country_id }}<br>
                            Stock: {{ $d->stock }}<br>
                            Price: ₱{{ number_format($d->amount, 2) }}
                        </p>
                        <a href="{{ url('/edit-book/'.$d->id) }}" class="btn btn-info btn-sm" onclick="return confirm('Update this book?')"><i class="fa fa-edit"></i></a>
                        <a href="{{ url('/delete-book/'.$d->id) }}" class="btn btn-danger btn-sm" onclick="return confirm('Delete this book?')"><i class="fa fa-trash"></i></a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

```

#### 2. Create `add-book-form.blade.php` file in `pages` folder and insert the following codes:

```html
@extends('layouts.app')

@section('content')
<x-page-header pagetitle="Add Book" class="bg-warning"/>

<div class="wrapper wrapper-content">
    <div class="animated fadeInRightBig">
        <form action="{{ url('/add-book') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" name="title" id="title" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="description">Description</label>
                        <textarea class="form-control" name="description" id="description"></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="country_id">Country Code</label>
                        <input type="number" class="form-control" name="country_id" id="country_id">
                    </div>
                    <div class="form-group mb-3">
                        <label for="stock">Stock</label>
                        <input type="number" class="form-control" name="stock" id="stock">
                    </div>
                    <div class="form-group mb-3">
                        <label for="amount">Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount" id="amount">
                    </div>
                    <div class="form-group mb-3">
                        <label for="photo">Photo</label>
                        <input type="file" class="form-control" name="photo" id="photo">
                    </div>
                    <hr>
                    <button class="btn btn-primary" type="submit">Save</button>
                    <a href="{{ url('/p2') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

```

#### 3. Create `edit-book-form.blade.php` file in `pages` folder and insert the following codes:

```html
@extends('layouts.app')

@section('content')
<x-page-header pagetitle="Edit Book" class="bg-warning"/>

<div class="wrapper wrapper-content">
    <div class="animated fadeInRightBig">
        <form action="{{ url('/update-book') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <input type="hidden" name="id" value="{{ $data->id }}">
                    <input type="hidden" name="old_photo" value="{{ $data->photo }}">
                    
                    <div class="form-group mb-3">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" name="title" id="title" required value="{{ $data->title }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="description">Description</label>
                        <textarea class="form-control" name="description" id="description">{{ $data->description }}</textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="country_id">Country Code</label>
                        <input type="number" class="form-control" name="country_id" id="country_id" value="{{ $data->country_id }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="stock">Stock</label>
                        <input type="number" class="form-control" name="stock" id="stock" value="{{ $data->stock }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="amount">Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount" id="amount" value="{{ $data->amount }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="photo">Photo</label>
                        <input type="file" class="form-control" name="photo" id="photo">
                        <small class="text-muted">Current:</small><br>
                        <img src="{{ asset('_uploads/'.$data->photo) }}" width="100" class="border mt-1">
                    </div>
                    <hr>
                    <button class="btn btn-primary" type="submit">Update</button>
                    <a href="{{ url('/p2') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

```

---

### D. Routing Setup (Final Step – Required)

Add these routes to your `routes/web.php` file:

```php
use App\Http\Controllers\BookController;

Route::get('/p2', [BookController::class, 'index']);
Route::get('/book-form', [BookController::class, 'show_add_form']);
Route::post('/add-book', [BookController::class, 'do_add']);
Route::get('/edit-book/{id}', [BookController::class, 'show_edit_form']);
Route::post('/update-book', [BookController::class, 'do_update']);
Route::get('/delete-book/{id}', [BookController::class, 'do_delete']);

```

---

### E. Presentation and Scoring Sheet

* Print the next page upon your presentation of this activity before due date.
* Prepare for Q and A, 2-3 questions per member.
* If something is not working, figure it out.

---

**Name:** _____________________________________________________ **Date:** _________________ **Score:** ______________

#### Score Sheet

##### A. Checklist of Files Created (90pts @ 10pts each)

| Type | Path | Purpose | Score |
| --- | --- | --- | --- |
| **Migration** | `database/migrations/create_books_table.php` | Table structure |  |
| **Model** | `app/Models/Book.php` | ORM model |  |
| **Factory** | `database/factories/BookFactory.php` | Sample data generator |  |
| **Seeder** | `database/seeders/BookSeeder.php` | Inserts 100 records |  |
| **Controller** | `app/Http/Controllers/BookController.php` | CRUD logic |  |
| **Views** | `resources/views/pages/page2.blade.php` | Display books |  |
| **Views** | `resources/views/pages/add-book-form.blade.php` | Add form |  |
| **Views** | `resources/views/pages/edit-book-form.blade.php` | Edit form |  |
| **Routes** | `routes/web.php` | URL definitions |  |
| **Total(A)** |  |  |  |

##### B. Submission and Presentation

| Criteria | Task | Score |
| --- | --- | --- |
| **Submission** | Presented before due date. (+10) |  |
| **Member 1** | Deductions if presentation and questioning are not satisfactory (-10 per question) |  |
| **Member 2** | Deductions if presentation and questioning are not satisfactory (-10 per question) |  |
| **Total(B)** |  |  |
| **Total(A + B)** |  |  |

```

```