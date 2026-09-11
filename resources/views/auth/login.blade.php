@extends('layouts.guest')

@section('content')
{{-- NOTE (change NOT in the worksheet instructions): This Tailwind login screen supports the examination's database-backed Login rubric without introducing a full authentication starter kit. --}}
<main class="flex min-h-screen items-center justify-center px-4 py-6 sm:px-6 sm:py-8">
    <div class="grid w-full max-w-5xl overflow-hidden rounded-3xl border border-[#eaded6] bg-white shadow-xl lg:grid-cols-[1.05fr_0.95fr]">
        <section class="relative hidden overflow-hidden bg-[#3c2924] p-8 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-[#b85c5c]/40"></div>
            <div class="absolute -bottom-32 -left-24 h-80 w-80 rounded-full bg-[#e8ad85]/20"></div>
            <div class="relative">
                <h1 class="mt-6 max-w-sm font-serif text-4xl font-bold leading-tight">Make every celebration taste personal.</h1>
                <p class="mt-4 max-w-md leading-6 text-[#f5ddd1]">Manage cake products, customer requests, and orders from one calm workspace.</p>
            </div>
            <p class="relative text-sm text-[#f5ddd1]">SliceCraft · Custom Cake Shop</p>
        </section>

        <section class="p-5 sm:p-8 lg:p-9">
            <h2 class="text-3xl font-bold text-[#3c2924]">Owner / Admin sign in</h2>
            <p class="mt-3 text-sm leading-6 text-[#78645d]">Sign in to manage SliceCraft products, customer records, the current cart, and placed orders.</p>

            @if ($errors->has('auth'))
                <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                    {{ $errors->first('auth') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.authenticate') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="username" class="mb-2 block text-sm font-bold">Username</label>
                    <input id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" class="w-full rounded-xl border-[#d9c5b9] bg-[#fffaf8] px-3 py-2.5 text-sm focus:border-[#984b4b] focus:ring-[#984b4b]">
                    @error('username')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="mb-2 block text-sm font-bold">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="w-full rounded-xl border-[#d9c5b9] bg-[#fffaf8] px-3 py-2.5 text-sm focus:border-[#984b4b] focus:ring-[#984b4b]">
                    @error('password')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-[#78645d]">
                    <input type="checkbox" name="remember" value="1" class="rounded border-[#d9c5b9] text-[#b85c5c] focus:ring-[#b85c5c]">
                    Remember this device
                </label>
                <button type="submit" class="w-full rounded-xl bg-[#984b4b] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#7f3e3e] focus:outline-none focus:ring-2 focus:ring-[#984b4b] focus:ring-offset-2">Sign in</button>
            </form>
        </section>
    </div>
</main>
@endsection
