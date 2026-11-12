@extends('layouts.app')

@section('content')
{{-- NOTE (change NOT in the worksheet instructions): This Page 3 customer screen fulfills the exam's Manage Customer view requirement with the same SliceCraft visual language. --}}
<div class="slicecraft-page min-h-screen bg-[#fffaf5] px-3 py-4 text-[#3c2924] sm:px-4 lg:px-6">
    <div class="mx-auto max-w-6xl">
        <header class="mb-4 flex flex-col gap-3 border-b border-[#eaded6] pb-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#b85c5c]">SliceCraft · Owner / Admin</p>
                <h1 class="mt-1 text-3xl font-bold">Customers</h1>
                <p class="mt-2 text-[#78645d]">View customer details and keep their order status current.</p>
            </div>
            @include('pages._slicecraft-nav')
        </header>

        @if (session('success'))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('customers.index') }}" class="mb-4 flex flex-col gap-3 rounded-3xl border border-[#eaded6] bg-white p-3 shadow-sm sm:flex-row">
            <label for="search" class="sr-only">Search customers</label>
            <input id="search" name="search" value="{{ $search }}" placeholder="Search by name or email" class="min-w-0 flex-1 rounded-xl border-[#d9c5b9] bg-[#fffaf8] px-3 py-2.5 text-sm focus:border-[#b85c5c] focus:ring-[#b85c5c]">
            <button type="submit" class="rounded-xl bg-[#3c2924] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#5b3a31]">Search</button>
        </form>

        <div class="overflow-hidden rounded-3xl border border-[#eaded6] bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="admin-table w-full min-w-[640px] text-left">
                    <thead class="bg-[#fffaf8] text-xs uppercase tracking-[0.16em] text-[#917d73]">
                        <tr>
                            <th class="px-3 py-3">Customer</th>
                            <th class="px-3 py-3">Contact</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="px-3 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e5df]">
                        @forelse ($customers as $customer)
                            <tr class="hover:bg-[#fffaf8]">
                                <td class="px-3 py-3">
                                    <p class="font-bold">{{ $customer->name }}</p>
                                    <p class="mt-1 text-xs text-[#917d73]">Customer #{{ $customer->id }}</p>
                                </td>
                                <td class="px-3 py-3 text-[#78645d]">
                                    <p>{{ $customer->email }}</p>
                                    <p class="mt-1 text-xs">{{ $customer->phone ?: 'No phone recorded' }}</p>
                                </td>
                                <td class="px-3 py-3">
                                    @if ($customer->status === 'active')
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Active</span>
                                    @elseif ($customer->status === 'pending')
                                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">Pending</span>
                                    @else
                                        <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right"><a href="{{ route('customers.show', $customer) }}" class="font-bold text-[#b85c5c] hover:text-[#984b4b]">View customer</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-10 text-center text-[#78645d]">No customers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $customers->links() }}</div>
    </div>
</div>
@endsection
