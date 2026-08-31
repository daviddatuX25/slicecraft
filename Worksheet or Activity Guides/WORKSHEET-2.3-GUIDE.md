# Worksheet 2.3 — Dashboard: Study & Defense Guide

> This is YOUR reference for the activity. It explains what was built, how the Laravel
> app actually works, and — most importantly — how to defend every change if your
> instructor asks "why did you do X when it wasn't in the instructions?"

This is a **laravel 12** app. This worksheet is a **CPRMIS v3** dashboard page built with
an INSPINIA admin theme pasted into a Blade layout.

---

## 1. LAYOUT OF THIS
W3 activity where we make a dashboard `x`, by building:

| File | Role |
|---|---|
| `routes/web.php` | The MAP: URL → what to do |
| `resources/views/layouts/app.blade.php` | The SHELL/CHASSIS (header, sidebar, footer, the `@yield` hole) |
| `resources/views/pages/dashboard.blade.php` | PAGE BODY for **/dashboard** (fills the shell) |
| `resources/views/pages/page1/2/3.blade.php` | PAGE BODY for /p1 /p2 /p3 (placeholders) |
| `resources/views/components/page-header.blade.php` | Reusable piece: the page title bar (`<x-page-header>`) |
| `resources/views/components/widget.blade.php` | Reusable piece: 3 stat boxes (`<x-widget>`) |
| `resources/views/components/box.blade.php` | Reusable piece: a card/panel (`<x-box>`) |
| `public/css` & `public/font-awesome` | Static assets served as-is (the INSPINIA theme) |

---

## 2. HOW THE APP WORKS (beginner / refresh)

### The one idea to memorize
`@extends('layouts.app')` + `@section('content')` ... `@endsection` + `@yield('content')`

- The small page file says "I belong INSIDE `app.blade.php`."
- The layout has a `@yield('content')` HOLE.
- The page's `@section('content')` is injected INTO that hole.
- A **component** (`<x-thing>`) is just a reusable view; Laravel looks it up in
  `resources/views/components/`.

### Request / response flow (every page load)

```
 BROWSER                          LARAVEL SERVER                                 OUTPUT
 -------                          --------------                                 ------
 1 type URL  -->  2 public/index.php      : FRONT CONTROLLER (catches every request)
             ->  3 routes/web.php         : ROUTER (match URL -> route/closure)
             ->  4 the closure            : LOGIC   -> return view('pages.dashboard')
             ->  5 Blade views            : VIEW    -> layouts.app shell + page content
             ->  6 compiled HTML          : RESPONSE back to browser
 7 <-- render <--
```

### The actual sequence for GET /dashboard (our code)

```
 browser: http://localhost:8000/dashboard
      |  routes/web.php:  Route::get('/dashboard', fn => view('pages.dashboard'));
      v
 view('pages.dashboard') loads:
      |  @extends('layouts.app')        -> attach to the shell
      |  @section('content') ... @endsection
      v
 layouts/app.blade.php  (the SHELL)
      |  <head>  : <title>CPRMIS v3</title> + CSS links  ({{ asset('css/style.css') }})
      |  <body>  : sidebar  ({{ url('/dashboard') }} ... 5 links)
      |           top navbar
      |           @yield('content')       <-- HOLE the page drops into
      |  scripts : javascript + Pusher/Toastr ({{ asset('https://...') }})
      v
 @yield('content')  pulls the child's content into the hole:
      |  <x-page-header pagetitle="Dashboard" class="bg-dark"/>
      |  <x-widget> ... three stat boxes ... </x-widget>
      |  <x-box>   ... report card ... </x-box>
      v
 compiled HTML  -->  response  -->  browser renders it
```

### The two `{{ }}` helpers you'll see everywhere
- `{{ asset('css/style.css') }}` → turns into a **URL to a file in `public/`**
  (e.g. `http://localhost:8000/css/style.css`).
- `{{ url('/dashboard') }}` → turns into a **URL to a route** so links keep working
  whatever the host/port is.
- `{{ asset('https://js.pusher.com/...') }}` → returns the URL **unchanged** because
  Laravel sees it already has a scheme (this is intentional in the worksheet, not a bug).

---

## 3. DEFENSE GUIDE — "why did you do that?"

### Q: Why is `app.blade.php` so long? The worksheet said paste `empty_page.php`.
A: The worksheet step 5 says "paste the copied codes from the `empty_page.php` file to
your `app.blade.php`". That is the whole INSPINIA **empty_page** template (≈400 lines).
We pasted the complete file, then applied the three modifications (head / nav / footer).
The real source is `HTML5_Full_Version/HTML5_Full_Version/empty_page.html`.

### Q: Why did you change the head CSS links to `{{ asset('...') }}`?  (step 6a)
A: The worksheet's own step 6a shows the head with `{{ asset('css/bootstrap.min.css') }}`.
`asset()` builds a proper URL to the asset inside `public/`, so they load correctly under
Laravel's rout. That's exactly what the worksheet wanted.

### Q: Why is the sidebar just 5 links now?  (step 6b)
A: Step 6b's target is exactly those five `<li>{{ url('/...') }}</li>` links. The huge demo
menu that ships with the INSPINIA theme was replaced by them, as instructed.

### Q: Why are the Pusher and Toastr scripts `{{ asset('https://...') }}`?  (step 6c)  <-- key one
A: Because the worksheet's step 6c shows them exactly that way, so we matched it. The good
news: `asset()` on an absolute URL returns it unchanged, so the CDN script still loads from
`https://js.pusher.com/...`. It is NOT broken.

### Q: Why are you showing `@yield('content')` in the layout?  (step 6c)
A: That is the hole where each page's `@section('content')` goes. Step 6c places it in the
content area of the layout.

### Q: Why are there three extra files in `resources/views/components/`?  (the big one)
A: Those are **Blade components** (`<x-page-header>`, `<x-widget>`, `<x-box>`) referenced by
the dashboard code you were told to paste in step 9. The worksheet **uses** them but never
**gives** their definitions, and the INSPINIA static theme has no Blade components. Without
them Laravel throws `Component [x-page-header] not found` and the page errors. So they're
REQUIRED to make the dashboard render, and each has a `NOTE` comment at the top saying so.
They were built in the INSPINIA panel style (page-heading bar, three stat boxes, a card).

### Q: Why is the dashboard's file named `.blade.php` when the worksheet said `.php`?  (step 4b)
A: Laravel views MUST end in `.blade.php` to be compiled as Blade. The worksheet's `.php`
is a typo/usability label; the actual file is `dashboard.blade.php` (and the route returns
`view('pages.dashboard')`).

### Q: Why does the "Page 4" link 404?
A: The worksheet's nav (step 6b) includes a `Page 4` link (`{{ url('/p4') }}`), but its
route list (step 5) only goes through `/p3` and provides no fifth page. It is an
**inconsistency in the worksheet itself**. We left it exactly as written. If you want a
fully working nav, add `page4.blade.php` + `Route::get('/p4', ...)` — your/instructor call.

---

## 4. COMMANDS

### Run the dev server (from inside the project folder)
```
cd /c/Users/sarmi/ProfElec5/project-sample
php artisan serve
```
- Default address: http://localhost:8000
- Pick a port: `php artisan serve --port=8001`
- Then open in the browser: http://localhost:8000/dashboard  (and /p1 /p2 /p3)

### Stop the server
- Press **Ctrl + C** in that terminal tab.
- If it's stuck, in a **PowerShell** terminal:
  `Get-NetTCPConnection -LocalPort 8000 | %{ Stop-Process -Id $_.OwningProcess -Force }`
- From git-bash:
  `MSYS_NO_PATHCONV=1 netstat -ano | grep ":8000"` then `taskkill /PID <pid> /F`

### Clear cached views (after editing a blade file, if it doesn't refresh)
```
php artisan view:clear
```

### Git (the two states we made)
```
git log --oneline          # see the two commits
git branch                 # confirm which branch (all on main)
git checkout worksheet-original   # revert to the exact per-instructions state
git checkout worksheet-improved   # go back to the improved (components) state
```

---

## 5. VERIFICATION
Open: `/dashboard` `/p1` `/p2` `/p3` — all should render. `/p4` returns 404 (see above).
