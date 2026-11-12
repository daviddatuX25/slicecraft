<?php

namespace App\Http\Controllers\Finals;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Finals\CustomerCartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAuthViewController extends Controller
{
    public function __construct(
        protected CustomerCartService $cartService
    ) {}

    /**
     * Show the customer login view.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('finals.storefront');
        }

        return view('finals.auth.login');
    }

    /**
     * Authenticate customer credentials using the dedicated 'customer' guard.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('customer')->attempt($credentials, $remember)) {
            $customer = Auth::guard('customer')->user();
            $request->session()->regenerate();

            // Merge any items the guest added before logging in
            $this->cartService->mergeSessionCartIntoCustomer($customer);

            return redirect()->intended(route('finals.storefront'))
                ->with('success', 'Welcome back, ' . $customer->name . '!');
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our customer records.',
        ])->onlyInput('email');
    }

    /**
     * Show the customer registration view.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('finals.storefront');
        }

        return view('finals.auth.register');
    }

    /**
     * Register a new customer and log them in via the 'customer' guard.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $customer = Customer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => 'active',
        ]);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        // Merge any items the guest added before registering
        $this->cartService->mergeSessionCartIntoCustomer($customer);

        return redirect()->route('finals.storefront')
            ->with('success', 'Registration successful! Welcome to SliceCraft Storefront.');
    }

    /**
     * Log out the customer session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('finals.storefront')
            ->with('info', 'You have been logged out.');
    }
}
