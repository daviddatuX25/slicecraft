<?php

namespace Tests\Feature;

use App\Jobs\ProcessFinalOrderConfirmation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FinalExamCustomerTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customerA;
    private Customer $customerB;
    private Product $cakeUbe;
    private Product $cakeChoco;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed products for testing
        $this->cakeUbe = Product::create([
            'name' => 'Classic Ube Delight',
            'slug' => 'classic-ube-delight',
            'description' => 'Authentic purple yam chiffon with luscious ube halaya frosting.',
            'category' => 'Artisan Cakes',
            'base_price_cents' => 75000, // ₱750.00
            'image_path' => 'img/ube_cake.jpg',
            'is_active' => true,
        ]);

        $this->cakeChoco = Product::create([
            'name' => 'Belgian Chocolate Fudge',
            'slug' => 'belgian-chocolate-fudge',
            'description' => 'Rich multi-layer dark chocolate fudge cake with ganache.',
            'category' => 'Chocolate Cakes',
            'base_price_cents' => 85000, // ₱850.00
            'image_path' => 'img/choco_cake.jpg',
            'is_active' => true,
        ]);

        // Customer A
        $this->customerA = Customer::create([
            'name' => 'Maria Clara',
            'email' => 'maria@example.com',
            'password' => 'secret123',
            'phone' => '0917-111-2222',
            'address' => 'Vigan City, Ilocos Sur',
            'status' => 'active',
        ]);

        // Customer B (for privacy tests)
        $this->customerB = Customer::create([
            'name' => 'Crisostomo Ibarra',
            'email' => 'ibarra@example.com',
            'password' => 'secret123',
            'phone' => '0918-333-4444',
            'address' => 'San Diego, Manila',
            'status' => 'active',
        ]);
    }

    // =========================================================================
    // 1. CUSTOMER ACCOUNT MODULE (10 pts)
    // =========================================================================

    public function test_customer_can_register_via_api(): void
    {
        $response = $this->postJson(route('api.customer.register'), [
            'name' => 'Juan Luna',
            'email' => 'juan.luna@example.com',
            'password' => 'artisan2026',
            'phone' => '0920-555-6666',
            'address' => 'Badoc, Ilocos Norte',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Customer registered successfully.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'customer' => ['id', 'name', 'email', 'status'],
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertDatabaseHas('customers', [
            'email' => 'juan.luna@example.com',
            'name' => 'Juan Luna',
            'status' => 'active',
        ]);
    }

    public function test_registration_validates_required_fields_and_uniqueness(): void
    {
        $response = $this->postJson(route('api.customer.register'), [
            'name' => '',
            'email' => 'maria@example.com', // Duplicate email
            'password' => '123', // Too short
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_customer_can_authenticate_via_api_and_receive_sanctum_token(): void
    {
        $response = $this->postJson(route('api.customer.login'), [
            'email' => 'maria@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Authentication successful.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'customer' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_invalid_login_credentials_are_rejected(): void
    {
        $response = $this->postJson(route('api.customer.login'), [
            'email' => 'maria@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'The provided credentials do not match our records.',
            ]);
    }

    public function test_customer_can_retrieve_profile_and_logout_via_sanctum(): void
    {
        $token = $this->customerA->createToken('test-token')->plainTextToken;

        // View profile
        $profileResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.customer.me'));

        $profileResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'customer' => [
                        'id' => $this->customerA->id,
                        'email' => 'maria@example.com',
                    ],
                ],
            ]);

        // Logout
        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.customer.logout'));

        $logoutResponse->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    public function test_customer_can_register_and_login_via_web_storefront(): void
    {
        // Web Registration
        $regResponse = $this->post(route('finals.register.post'), [
            'name' => 'Gabriela Silang',
            'email' => 'gabriela@example.com',
            'password' => 'freedom2026',
            'password_confirmation' => 'freedom2026',
            'phone' => '0919-777-8888',
            'address' => 'Santa, Ilocos Sur',
        ]);

        $regResponse->assertRedirect(route('finals.storefront'));
        $this->assertAuthenticatedAs(Customer::where('email', 'gabriela@example.com')->first(), 'customer');

        // Web Logout
        $logoutResponse = $this->post(route('finals.logout'));
        $logoutResponse->assertRedirect(route('finals.storefront'));
        $this->assertGuest('customer');

        // Web Login
        $loginResponse = $this->post(route('finals.login.post'), [
            'email' => 'gabriela@example.com',
            'password' => 'freedom2026',
        ]);

        $loginResponse->assertRedirect(route('finals.storefront'));
        $this->assertAuthenticatedAs(Customer::where('email', 'gabriela@example.com')->first(), 'customer');
    }

    // =========================================================================
    // 2. PRODUCT BROWSING MODULE (10 pts)
    // =========================================================================

    public function test_api_displays_products_retrieved_via_eloquent_orm(): void
    {
        $response = $this->getJson(route('api.customer.products.index'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'count' => 2,
            ])
            ->assertJsonFragment([
                'name' => 'Classic Ube Delight',
                'base_price_cents' => 75000,
                'base_price_pesos' => '750.00',
            ])
            ->assertJsonFragment([
                'name' => 'Belgian Chocolate Fudge',
                'base_price_cents' => 85000,
                'base_price_pesos' => '850.00',
            ]);
    }

    public function test_api_product_live_search_queries_database(): void
    {
        // Search specifically for "Ube"
        $response = $this->getJson(route('api.customer.products.index', ['search' => 'Ube']));

        $response->assertStatus(200)
            ->assertJson(['count' => 1])
            ->assertJsonFragment(['name' => 'Classic Ube Delight'])
            ->assertJsonMissing(['name' => 'Belgian Chocolate Fudge']);
    }

    public function test_api_view_product_details(): void
    {
        $response = $this->getJson(route('api.customer.products.show', $this->cakeUbe->id));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $this->cakeUbe->id,
                    'name' => 'Classic Ube Delight',
                    'base_price_cents' => 75000,
                ],
            ]);
    }

    public function test_web_storefront_renders_product_catalog(): void
    {
        $response = $this->get(route('finals.storefront'));

        $response->assertStatus(200)
            ->assertSee('Classic Ube Delight')
            ->assertSee('Belgian Chocolate Fudge')
            ->assertSee('₱750.00');
    }

    // =========================================================================
    // 3. SHOPPING CART MODULE (10 pts)
    // =========================================================================

    public function test_customer_can_add_product_to_cart_via_api(): void
    {
        $token = $this->customerA->createToken('cart-test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.customer.cart.items.store'), [
                'product_id' => $this->cakeUbe->id,
                'quantity' => 2,
                'customization' => 'With birthday candle',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Product added to cart successfully.',
            ]);

        $this->assertDatabaseHas('orders', [
            'customer_id' => $this->customerA->id,
            'status' => Order::CART_STATUS,
            'total_amount_cents' => 150000, // 2 * ₱750 = ₱1,500.00
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->cakeUbe->id,
            'quantity' => 2,
            'unit_price_cents' => 75000,
            'line_total_cents' => 150000,
        ]);
    }

    public function test_customer_can_view_cart_with_accurate_computations(): void
    {
        $token = $this->customerA->createToken('cart-view')->plainTextToken;

        // Add 1x Ube (₱750) and 1x Choco (₱850)
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.customer.cart.items.store'), [
                'product_id' => $this->cakeUbe->id,
                'quantity' => 1,
            ]);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.customer.cart.items.store'), [
                'product_id' => $this->cakeChoco->id,
                'quantity' => 1,
            ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.customer.cart.index'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'item_count' => 2,
                    'total_amount_cents' => 160000, // ₱1,600.00
                    'total_pesos' => '1,600.00',
                ],
            ]);
    }

    public function test_customer_can_update_cart_item_quantity(): void
    {
        $token = $this->customerA->createToken('cart-update')->plainTextToken;

        $addRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.customer.cart.items.store'), [
                'product_id' => $this->cakeUbe->id,
                'quantity' => 1,
            ]);

        $itemId = $addRes->json('data.added_item_id');

        // Update quantity to 4
        $updateRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson(route('api.customer.cart.items.update', $itemId), [
                'quantity' => 4,
            ]);

        $updateRes->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'item_count' => 4,
                    'total_amount_cents' => 300000, // 4 * 75000 = ₱3,000.00
                    'total_pesos' => '3,000.00',
                ],
            ]);
    }

    public function test_customer_can_remove_product_from_cart(): void
    {
        $token = $this->customerA->createToken('cart-remove')->plainTextToken;

        $addUbe = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.customer.cart.items.store'), [
                'product_id' => $this->cakeUbe->id,
                'quantity' => 1,
            ]);

        $addChoco = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.customer.cart.items.store'), [
                'product_id' => $this->cakeChoco->id,
                'quantity' => 1,
            ]);

        $ubeItemId = $addUbe->json('data.added_item_id');

        // Remove Ube item
        $delRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson(route('api.customer.cart.items.destroy', $ubeItemId));

        $delRes->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'item_count' => 1,
                    'total_amount_cents' => 85000, // Only Choco remains
                ],
            ]);

        $this->assertDatabaseMissing('order_items', ['id' => $ubeItemId]);
    }

    public function test_guest_can_add_to_cart_on_web_storefront_and_view_cart(): void
    {
        // 1. Guest visits storefront and adds 2x Classic Ube
        $addResponse = $this->post(route('finals.cart.items.store'), [
            'product_id' => $this->cakeUbe->id,
            'quantity' => 2,
        ]);

        $addResponse->assertRedirect();
        $this->assertTrue(session()->has('finals_cart'));

        // 2. Guest visits /finals/cart
        $cartViewResponse = $this->get(route('finals.cart'));

        $cartViewResponse->assertStatus(200);
        $cartViewResponse->assertSee('Classic Ube Delight');
        $cartViewResponse->assertSee('1,500.00'); // 2 * ₱750
        $cartViewResponse->assertSee('2 items');
    }

    public function test_guest_session_cart_is_merged_when_logging_in(): void
    {
        // 1. Add item as guest in session
        $this->post(route('finals.cart.items.store'), [
            'product_id' => $this->cakeUbe->id,
            'quantity' => 3,
        ]);

        // 2. Guest logs into existing customer account
        $loginResponse = $this->post(route('finals.login.post'), [
            'email' => 'maria@example.com',
            'password' => 'secret123',
        ]);

        $loginResponse->assertRedirect(route('finals.storefront'));

        // 3. Assert database order cart now contains the merged items
        $this->assertDatabaseHas('orders', [
            'customer_id' => $this->customerA->id,
            'status' => Order::CART_STATUS,
            'total_amount_cents' => 225000, // 3 * 75000 = ₱2,250.00
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->cakeUbe->id,
            'quantity' => 3,
            'unit_price_cents' => 75000,
            'line_total_cents' => 225000,
        ]);
    }

    // =========================================================================
    // 4. CHECKOUT AND ORDER PROCESSING MODULE (10 pts)
    // =========================================================================

    public function test_customer_can_checkout_with_accurate_computation_and_db_transaction(): void
    {
        Queue::fake();

        $token = $this->customerA->createToken('checkout-test')->plainTextToken;

        // Add 2x Ube to cart
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.customer.cart.items.store'), [
                'product_id' => $this->cakeUbe->id,
                'quantity' => 2,
            ]);

        // Submit checkout
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.customer.checkout'), [
                'notes' => 'Happy 21st Birthday Anna!',
                'scheduled_at' => now()->addDays(2)->toIso8601String(),
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Order placed successfully! A confirmation notice has been queued.',
                'data' => [
                    'status' => 'pending',
                    'total_amount_cents' => 150000,
                    'total_pesos' => '1,500.00',
                    'notes' => 'Happy 21st Birthday Anna!',
                ],
            ]);

        // Assert database transaction finalized order
        $this->assertDatabaseHas('orders', [
            'customer_id' => $this->customerA->id,
            'status' => 'pending',
            'total_amount_cents' => 150000,
            'notes' => 'Happy 21st Birthday Anna!',
        ]);

        // Assert asynchronous queue dispatch (Rubric 4)
        Queue::assertPushed(ProcessFinalOrderConfirmation::class, function ($job) {
            return $job->order->customer_id === $this->customerA->id &&
                   $job->order->total_amount_cents === 150000;
        });
    }

    public function test_queue_job_can_execute_handler_without_errors(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-999',
            'customer_id' => $this->customerA->id,
            'status' => 'pending',
            'total_amount_cents' => 75000,
        ]);

        $order->items()->create([
            'product_id' => $this->cakeUbe->id,
            'product_name_snapshot' => $this->cakeUbe->name,
            'quantity' => 1,
            'unit_price_cents' => 75000,
            'line_total_cents' => 75000,
        ]);

        $job = new ProcessFinalOrderConfirmation($order);

        // Execute job handle
        $job->handle();

        $this->assertTrue(true, 'Queue job handle completed successfully.');
    }

    // =========================================================================
    // 5. CUSTOMER ORDER MANAGEMENT MODULE (10 pts)
    // =========================================================================

    public function test_customer_can_view_their_own_order_history_and_details(): void
    {
        $token = $this->customerA->createToken('orders-history')->plainTextToken;

        // Create a placed order for Customer A
        $orderA = Order::create([
            'order_number' => 'ORD-MARIA-001',
            'customer_id' => $this->customerA->id,
            'status' => 'processing',
            'total_amount_cents' => 85000,
        ]);

        $orderA->items()->create([
            'product_id' => $this->cakeChoco->id,
            'product_name_snapshot' => $this->cakeChoco->name,
            'quantity' => 1,
            'unit_price_cents' => 85000,
            'line_total_cents' => 85000,
        ]);

        // Customer A list orders
        $resList = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.customer.orders.index'));

        $resList->assertStatus(200)
            ->assertJsonFragment(['order_number' => 'ORD-MARIA-001']);

        // Customer A view order detail
        $resDetail = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.customer.orders.show', $orderA->id));

        $resDetail->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'order_number' => 'ORD-MARIA-001',
                    'status' => 'processing',
                    'total_amount_cents' => 85000,
                ],
            ]);
    }

    public function test_customer_privacy_enforcement_blocks_access_to_other_customers_orders(): void
    {
        // Order belonging to Customer B
        $orderB = Order::create([
            'order_number' => 'ORD-IBARRA-SECRET',
            'customer_id' => $this->customerB->id,
            'status' => 'completed',
            'total_amount_cents' => 120000,
        ]);

        // Customer A attempts to view Customer B's order using their own token
        $tokenA = $this->customerA->createToken('intruder-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->getJson(route('api.customer.orders.show', $orderB->id));

        // Must strictly return 403 Forbidden to protect private records (Rubric 5)
        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Forbidden: You do not have permission to view another customer\'s order records.',
            ]);
    }

    public function test_web_storefront_protects_order_history_from_guests(): void
    {
        // Guest visits /finals/orders
        $response = $this->get(route('finals.orders'));

        // Redirects to customer login
        $response->assertRedirect(route('finals.login'));
    }

    /**
     * End-to-End full user journey test:
     * Guest browse -> Search -> Guest cart add -> Cart view -> Register ->
     * Checkout with DB transaction & Queue -> Order history & Tracking -> Privacy 403 protection.
     */
    public function test_complete_end_to_end_customer_journey(): void
    {
        Queue::fake();

        // 1. Guest visits storefront and searches for "Chocolate"
        $catalogResponse = $this->get(route('finals.storefront', ['search' => 'Chocolate']));
        $catalogResponse->assertStatus(200)
            ->assertSee('Belgian Chocolate Fudge')
            ->assertDontSee('Classic Ube Delight');

        // 2. Guest adds 2x Belgian Chocolate Fudge to guest cart
        $addResponse = $this->post(route('finals.cart.items.store'), [
            'product_id' => $this->cakeChoco->id,
            'quantity' => 2,
            'customization' => 'Dedication: Congratulations Grad!',
        ]);
        $addResponse->assertRedirect();
        $this->assertTrue(session()->has('finals_cart'));

        // 3. Guest views /finals/cart and verifies item and subtotal
        $cartResponse = $this->get(route('finals.cart'));
        $cartResponse->assertStatus(200)
            ->assertSee('Belgian Chocolate Fudge')
            ->assertSee('1,700.00'); // 2 * ₱850.00

        // 4. Guest registers a new account
        $registerResponse = $this->post(route('finals.register.post'), [
            'name' => 'Diego Silang',
            'email' => 'diego@example.com',
            'password' => 'hero2026',
            'password_confirmation' => 'hero2026',
            'phone' => '0917-999-0000',
            'address' => 'Candon City, Ilocos Sur',
        ]);
        $registerResponse->assertRedirect(route('finals.storefront'));

        $newCustomer = Customer::where('email', 'diego@example.com')->first();
        $this->assertNotNull($newCustomer);
        $this->assertAuthenticatedAs($newCustomer, 'customer');

        // 5. Verify the guest cart was merged into the database for Diego
        $this->assertDatabaseHas('orders', [
            'customer_id' => $newCustomer->id,
            'status' => Order::CART_STATUS,
            'total_amount_cents' => 170000,
        ]);

        // 6. Diego visits checkout and submits order
        $checkoutPageResponse = $this->get(route('finals.checkout'));
        $checkoutPageResponse->assertStatus(200)
            ->assertSee('Belgian Chocolate Fudge')
            ->assertSee('1,700.00');

        $checkoutSubmitResponse = $this->postJson(route('api.customer.checkout'), [
            'notes' => 'Happy Graduation Diego!',
            'scheduled_at' => now()->addDays(3)->toIso8601String(),
        ]);

        $checkoutSubmitResponse->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'status' => 'pending',
                    'total_amount_cents' => 170000,
                ],
            ]);

        // 7. Verify asynchronous queue dispatch
        Queue::assertPushed(ProcessFinalOrderConfirmation::class);

        // 8. Diego views order history and specific receipt
        $ordersResponse = $this->get(route('finals.orders'));
        $ordersResponse->assertStatus(200)
            ->assertSee('Belgian Chocolate Fudge')
            ->assertSee('1,700.00');

        $placedOrder = Order::where('customer_id', $newCustomer->id)->where('status', 'pending')->first();
        $this->assertNotNull($placedOrder);

        $receiptResponse = $this->get(route('finals.orders.show', $placedOrder->id));
        $receiptResponse->assertStatus(200)
            ->assertSee('Order #' . $placedOrder->order_number)
            ->assertSee('Belgian Chocolate Fudge');

        // 9. Diego logs out and test session is cleared
        $this->post(route('finals.logout'));
        $this->flushSession();
        $this->defaultCookies = [];
        $this->app['auth']->forgetGuards();

        // 10. Intruder customer (Customer B) attempts to inspect Diego's order using their token
        $intruderToken = $this->customerB->createToken('intruder')->plainTextToken;
        $privacyResponse = $this->withHeader('Authorization', 'Bearer ' . $intruderToken)
            ->getJson(route('api.customer.orders.show', $placedOrder->id));

        $privacyResponse->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Forbidden: You do not have permission to view another customer\'s order records.',
            ]);
    }
}
