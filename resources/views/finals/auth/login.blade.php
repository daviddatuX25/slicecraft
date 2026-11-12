@extends('finals.layouts.storefront')

@section('title', 'Customer Login')

@section('content')
<div class="max-w-md mx-auto my-6">
    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm space-y-6">
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto text-xl font-bold">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Customer Login</h1>
            <p class="text-xs text-slate-500">Sign in to your customer account to manage cart & orders</p>
        </div>

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                @foreach($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('finals.login.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    placeholder="e.g. maria@example.com"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"
                >
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-semibold text-slate-700">Password</label>
                </div>
                <input
                    type="password"
                    name="password"
                    required
                    placeholder="••••••••"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"
                >
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                    <span>Remember me</span>
                </label>
            </div>

            <button
                type="submit"
                class="w-full py-3 px-4 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-bold text-sm rounded-xl shadow-md shadow-rose-600/20 transition-all"
            >
                Sign In to Customer Account
            </button>
        </form>

        <div class="pt-4 border-t border-slate-100 text-center text-xs text-slate-500 space-y-2">
            <div>
                Don't have a customer account?
                <a href="{{ route('finals.register') }}" class="font-bold text-rose-600 hover:underline">Register now</a>
            </div>
            <div class="text-[11px] text-slate-400">
                Store staff? <a href="{{ route('login') }}" class="font-semibold text-slate-600 hover:underline">Staff Portal Sign In</a>
            </div>
        </div>
    </div>
</div>
@endsection
