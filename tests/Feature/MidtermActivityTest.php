<?php

namespace Tests\Feature;

use App\Models\CakePreview;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CakePreviewSeeder;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\OrderSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MidtermActivityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Test Admin',
            'username' => 'test-admin',
            'password' => Hash::make('secret-password'),
        ]);
    }

    /**
     * Guests cannot open the Page 3 cake shop.
     */
    public function test_guests_are_redirected_from_page_three(): void
    {
        $response = $this->get(route('cake.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_log_in_with_a_database_username(): void
    {
        $response = $this->post(route('login.authenticate'), [
            'username' => 'test-admin',
            'password' => 'secret-password',
        ]);

        $response->assertRedirect(route('cake.dashboard'));
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_invalid_login_does_not_authenticate(): void
    {
        $response = $this->from(route('login'))->post(route('login.authenticate'), [
            'username' => 'test-admin',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('auth');
        $this->assertGuest();
    }

    /**
     * NOTE (change NOT in the worksheet instructions): This regression check keeps one failed-login message from being rendered twice.
     */
    public function test_invalid_login_message_is_rendered_once(): void
    {
        $response = $this->from(route('login'))
            ->followingRedirects()
            ->post(route('login.authenticate'), [
                'username' => 'test-admin',
                'password' => 'wrong-password',
            ]);

        $response->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), 'These credentials do not match our records.'));
    }

    public function test_logout_invalidates_the_authenticated_session(): void
    {
        $response = $this->actingAs($this->admin)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_authenticated_admin_can_view_the_preview_first_page(): void
    {
        $this->seedCakeShop();

        $response = $this->actingAs($this->admin)->get(route('cake.dashboard'));

        $response->assertOk();
        $response->assertSee('SliceCraft · Custom Cake Shop');
        $response->assertSee('Classic Ube');
        $response->assertSee('Size / servings');
        $response->assertSee('Fresh Fruit');
        $response->assertSee('Chocolate Drip');
        $response->assertSee('Message on cake');
        $response->assertDontSee('Curated combinations');
        $response->assertDontSee('Only reviewed preview images are shown');
    }

    /**
     * NOTE (change NOT in the worksheet instructions): These assertions keep the optional SliceCraft management navigation consistent across the rubric-facing pages.
     */
    public function test_slicecraft_uses_consistent_owner_navigation(): void
    {
        $this->seedCakeShop();

        foreach ([
            route('products.index'),
            route('customers.index'),
            route('cart.index'),
            route('orders.index'),
        ] as $url) {
            $this->actingAs($this->admin)
                ->get($url)
                ->assertOk()
                ->assertSee('Products')
                ->assertSee('Customers')
                ->assertSee('Current Cart')
                ->assertSee('Placed Orders')
                ->assertDontSee('Studio');
        }
    }

    /**
     * NOTE (change NOT in the worksheet instructions): These assertions protect the compact visual shell and split-pane hooks added during UI refinement.
     */
    public function test_page_three_uses_the_compact_shell_and_split_panes(): void
    {
        $this->seedCakeShop();

        $this->actingAs($this->admin)
            ->get(route('cake.dashboard'))
            ->assertOk()
            ->assertSee('fixed-sidebar')
            ->assertSee('app-shell-header')
            ->assertSee('cake-page-header')
            ->assertSee('cake-options-pane')
            ->assertSee('cake-preview-pane')
            ->assertSee('app-shell-footer')
            ->assertDontSee('→');

        $this->actingAs($this->admin)
            ->get(route('customers.index'))
            ->assertOk()
            ->assertSee('admin-table')
            ->assertDontSee('View customer →');
    }

    /**
     * NOTE (change NOT in the worksheet instructions): These assertions keep the optional order-history UX distinct from the scored editable cart flow.
     */
    public function test_cart_and_order_history_are_distinct_surfaces(): void
    {
        $this->seedCakeShop();

        $cart = Order::where('status', 'cart')->where('order_number', 'SC-1001')->firstOrFail();
        $placedOrder = Order::where('status', 'pending')->where('order_number', 'SC-1002')->firstOrFail();

        $this->actingAs($this->admin)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Current cart')
            ->assertSee($cart->order_number)
            ->assertSee('View cart products')
            ->assertDontSee('Submit order');

        $this->actingAs($this->admin)
            ->get(route('cart.show', $cart))
            ->assertOk()
            ->assertDontSee('Submit order');

        $this->actingAs($this->admin)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Placed Orders')
            ->assertSee($placedOrder->order_number)
            ->assertDontSee($cart->order_number);

        $this->actingAs($this->admin)
            ->get(route('orders.show', $placedOrder))
            ->assertOk()
            ->assertSee('Placed order')
            ->assertDontSee('Remove item');
    }

    public function test_product_search_and_crud_are_persisted(): void
    {
        $this->seedCakeShop();

        $this->actingAs($this->admin)
            ->get(route('products.index', ['search' => 'Ube']))
            ->assertOk()
            ->assertSee('Classic Ube')
            ->assertDontSee('Red Velvet');

        $this->actingAs($this->admin)
            ->post(route('products.store'), [
                'name' => 'Demo Celebration Cake',
                'description' => 'Temporary product for the CRUD demonstration.',
                'category' => 'Classic Cakes',
                'base_price' => 1250,
                'is_active' => 1,
            ])
            ->assertRedirect(route('products.index'));

        $product = Product::where('slug', 'demo-celebration-cake')->firstOrFail();
        $this->assertSame(125000, $product->base_price_cents);

        $this->actingAs($this->admin)
            ->put(route('products.update', $product), [
                'name' => 'Updated Celebration Cake',
                'description' => 'Updated description.',
                'category' => 'Classic Cakes',
                'base_price' => 1350,
                'is_active' => 1,
            ])
            ->assertRedirect(route('products.show', $product));

        $product->refresh();
        $this->assertSame('updated-celebration-cake', $product->slug);
        $this->assertSame(135000, $product->base_price_cents);

        $this->actingAs($this->admin)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /**
     * NOTE (change NOT in the worksheet instructions): These checks keep the optional SliceCraft preview path connected to the existing editable cart flow without adding checkout behavior.
     */
    public function test_products_use_real_base_images_and_can_be_added_to_the_current_cart(): void
    {
        $this->seedCakeShop();

        $product = Product::where('slug', 'chocolate-fudge')->firstOrFail();
        $cart = Order::where('status', 'cart')->firstOrFail();

        $this->actingAs($this->admin)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('chocolate-fudge-minimal.png');

        $this->actingAs($this->admin)
            ->get(route('cake.dashboard'))
            ->assertOk()
            ->assertSee('Customer for this cart')
            ->assertSee('name="customer_id"', false);

        $this->actingAs($this->admin)
            ->post(route('cart-items.store'), [
                'product_id' => $product->id,
                'customer_id' => $cart->customer_id,
                'quantity' => 1,
                'size' => 'one-kg',
                'filling' => 'strawberry',
                'frosting' => 'buttercream',
                'decoration' => 'minimal',
                'toppings' => 'chocolate-drip',
                'message' => 'Happy birthday',
            ])
            ->assertRedirect(route('cart.show', $cart));

        $this->assertDatabaseHas('order_items', [
            'order_id' => $cart->id,
            'product_id' => $product->id,
            'customization_summary' => '1 kg (10–12 servings) · Fresh Strawberry · Buttercream · Minimal Celebration · Chocolate Drip · Message: Happy birthday',
        ]);
    }

    /**
     * NOTE (change NOT in the worksheet instructions): Product CRUD now accepts one base image and five fixed combination images while preserving fallback paths for omitted files.
     */
    public function test_product_create_shows_and_stores_the_base_and_combination_images(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)
            ->get(route('products.create'))
            ->assertOk()
            ->assertSee('name="base_image"', false)
            ->assertSee('name="preview_images[minimal]"', false)
            ->assertSee('name="preview_images[rosettes]"', false)
            ->assertSee('name="preview_images[coconut]"', false)
            ->assertSee('name="preview_images[fruit]"', false)
            ->assertSee('name="preview_images[web-glaze]"', false)
            ->assertSee('name="preview_images[drip]"', false)
            ->assertSee('name="preview_images[berries]"', false)
            ->assertSee('name="preview_images[macarons]"', false)
            ->assertSee('name="preview_images[floral]"', false)
            ->assertSee('name="preview_images[bento]"', false);

        $this->actingAs($this->admin)
            ->post(route('products.store'), [
                'name' => 'Uploaded Celebration Cake',
                'description' => 'A product with uploaded preview images.',
                'category' => 'Classic Cakes',
                'base_price' => 1450,
                'is_active' => 1,
                'base_image' => UploadedFile::fake()->image('base.jpg'),
                'preview_images' => [
                    'minimal' => UploadedFile::fake()->image('minimal.jpg'),
                    'rosettes' => UploadedFile::fake()->image('rosettes.jpg'),
                    'coconut' => UploadedFile::fake()->image('coconut.jpg'),
                    'fruit' => UploadedFile::fake()->image('fruit.jpg'),
                    'web-glaze' => UploadedFile::fake()->image('web-glaze.jpg'),
                    'drip' => UploadedFile::fake()->image('drip.jpg'),
                    'berries' => UploadedFile::fake()->image('berries.jpg'),
                    'macarons' => UploadedFile::fake()->image('macarons.jpg'),
                    'floral' => UploadedFile::fake()->image('floral.jpg'),
                    'bento' => UploadedFile::fake()->image('bento.jpg'),
                ],
            ])
            ->assertRedirect(route('products.index'));

        $product = Product::where('slug', 'uploaded-celebration-cake')->firstOrFail();

        $this->assertStringStartsWith('storage/cakes/products/uploaded-celebration-cake/', $product->image_path);
        $this->assertCount(10, $product->cakePreviews);
        $this->assertTrue(Storage::disk('public')->exists(substr($product->image_path, 8)));

        foreach ($product->cakePreviews as $preview) {
            $this->assertStringStartsWith('storage/cakes/products/uploaded-celebration-cake/', $preview->image_path);
            $this->assertTrue(Storage::disk('public')->exists(substr($preview->image_path, 8)));
        }
    }

    /**
     * NOTE (change NOT in the worksheet instructions): Product updates reuse the same fixed five-image contract so Create and Edit do not diverge.
     */
    public function test_product_update_can_replace_a_combination_image(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        $preview = $product->cakePreviews()->create([
            'selection_key' => 'minimal',
            'image_path' => 'images/cakes/'.$product->slug.'-minimal.png',
            'alt_text' => $product->name.' minimal preview',
        ]);

        $this->actingAs($this->admin)
            ->put(route('products.update', $product), [
                'name' => $product->name,
                'description' => $product->description,
                'category' => $product->category,
                'base_price' => 1200,
                'is_active' => 1,
                'preview_images' => [
                    'minimal' => UploadedFile::fake()->image('replacement.jpg'),
                ],
            ])
            ->assertRedirect(route('products.show', $product));

        $product->refresh();
        $preview->refresh();
        $this->assertStringStartsWith('storage/cakes/products/'.$product->slug.'/', $preview->image_path);
        $this->assertTrue(Storage::disk('public')->exists(substr($preview->image_path, 8)));
    }

    public function test_product_validation_rejects_missing_required_values(): void
    {
        $response = $this->actingAs($this->admin)->post(route('products.store'), []);

        $response->assertSessionHasErrors(['name', 'category', 'base_price']);
    }

    public function test_customer_can_be_viewed_and_status_can_be_updated(): void
    {
        $this->seedCakeShop();
        $customer = Customer::where('email', 'juan@slicecraft.test')->firstOrFail();

        $this->actingAs($this->admin)
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee('Juan Dela Cruz')
            ->assertSee('Customer overview')
            ->assertSee('Current cart')
            ->assertSee('Order history');

        $this->actingAs($this->admin)
            ->patch(route('customers.status.update', $customer), ['status' => 'active'])
            ->assertRedirect(route('customers.show', $customer));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'status' => 'active',
        ]);
    }

    public function test_customer_status_rejects_unknown_values(): void
    {
        $this->seedCakeShop();
        $customer = Customer::where('email', 'juan@slicecraft.test')->firstOrFail();

        $this->actingAs($this->admin)
            ->from(route('customers.show', $customer))
            ->patch(route('customers.status.update', $customer), ['status' => 'archived'])
            ->assertRedirect(route('customers.show', $customer))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_delete_one_cart_product_without_deleting_the_order(): void
    {
        $this->seedCakeShop();
        $order = Order::where('order_number', 'SC-1001')->firstOrFail();
        $itemToDelete = $order->items()->whereHas('product', function ($query): void {
            $query->where('slug', 'classic-ube');
        })->firstOrFail();

        $this->actingAs($this->admin)
            ->delete(route('cart-items.destroy', [$order, $itemToDelete]))
            ->assertRedirect(route('cart.show', $order));

        $this->assertDatabaseMissing('order_items', ['id' => $itemToDelete->id]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'order_number' => 'SC-1001']);
        $this->assertSame(1, $order->fresh()->items()->count());
        $this->assertSame(110000, $order->fresh()->total_amount_cents);
    }

    public function test_admin_can_submit_a_cart_as_a_placed_order(): void
    {
        $this->seedCakeShop();
        $cart = Order::where('order_number', 'SC-1001')->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('cart.submit', $cart))
            ->assertRedirect(route('orders.show', $cart));

        $this->assertDatabaseHas('orders', [
            'id' => $cart->id,
            'status' => 'pending',
        ]);
        $this->assertTrue($cart->fresh()->isPlaced());
    }

    public function test_cart_item_delete_cannot_cross_order_boundaries(): void
    {
        $this->seedCakeShop();
        $order = Order::where('order_number', 'SC-1001')->firstOrFail();
        $item = $order->items()->firstOrFail();
        $otherOrder = Order::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('cart-items.destroy', [$otherOrder, $item]))
            ->assertNotFound();

        $this->assertDatabaseHas('order_items', ['id' => $item->id]);
    }

    /**
     * NOTE (change NOT in the worksheet instructions): Quantity editing is a small cart improvement that makes the editable cart easier to manage.
     */
    public function test_admin_can_update_cart_item_quantity_and_totals(): void
    {
        $this->seedCakeShop();
        $cart = Order::where('order_number', 'SC-1001')->firstOrFail();
        $item = $cart->items()->whereHas('product', function ($query): void {
            $query->where('slug', 'classic-ube');
        })->firstOrFail();

        $this->actingAs($this->admin)
            ->patch(route('cart-items.quantity.update', [$cart, $item]), ['quantity' => 3])
            ->assertRedirect(route('cart.show', $cart));

        $this->assertSame(3, $item->fresh()->quantity);
        $this->assertSame(357000, $item->fresh()->line_total_cents);
        $this->assertSame(467000, $cart->fresh()->total_amount_cents);
    }

    public function test_cart_quantity_update_cannot_change_a_placed_order(): void
    {
        $this->seedCakeShop();
        $order = Order::where('order_number', 'SC-1002')->firstOrFail();
        $item = $order->items()->firstOrFail();

        $this->actingAs($this->admin)
            ->patch(route('cart-items.quantity.update', [$order, $item]), ['quantity' => 3])
            ->assertNotFound();

        $this->assertSame(1, $item->fresh()->quantity);
    }

    public function test_the_seeded_preview_set_has_thirty_records(): void
    {
        $this->seedCakeShop();

        $this->assertSame(4, Product::count());
        $this->assertSame(30, CakePreview::whereHas('product', function ($query): void {
            $query->whereIn('slug', ['classic-ube', 'chocolate-fudge', 'red-velvet', 'mango-celebration']);
        })->count());
        $this->assertSame(8, Product::where('slug', 'classic-ube')->firstOrFail()->cakePreviews()->count());
        $this->assertSame(8, Product::where('slug', 'chocolate-fudge')->firstOrFail()->cakePreviews()->count());
        $this->assertSame(8, Product::where('slug', 'red-velvet')->firstOrFail()->cakePreviews()->count());
        $this->assertSame(6, Product::where('slug', 'mango-celebration')->firstOrFail()->cakePreviews()->count());
        $this->assertDatabaseHas('cake_previews', [
            'selection_key' => 'rosettes',
            'image_path' => 'images/cakes/classic-ube-rosettes.png',
        ]);
        $this->assertDatabaseHas('cake_previews', [
            'selection_key' => 'web-glaze',
            'image_path' => 'images/cakes/chocolate-fudge-web-glaze.png',
        ]);
    }

    public function test_missing_combination_assets_use_the_local_unavailable_artwork(): void
    {
        $this->seedCakeShop();

        $product = Product::where('slug', 'mango-celebration')->firstOrFail();
        $preview = $product->cakePreviews()->create([
            'selection_key' => 'future',
            'image_path' => 'images/cakes/mango-celebration-future.png',
            'alt_text' => 'Future Mango Celebration preview',
        ]);

        $this->assertStringStartsNotWith('http', $preview->image_path);
        $this->assertFalse($preview->hasAvailableImage());

        $this->actingAs($this->admin)
            ->get(route('cake.dashboard'))
            ->assertOk()
            ->assertSee('preview-unavailable.svg');
    }

    public function test_the_existing_books_page_still_renders(): void
    {
        $this->get('/p2')->assertOk();
    }

    public function test_previous_worksheet_pages_remain_public(): void
    {
        $this->get('/p1')->assertOk();
        $this->get('/p2')->assertOk();
        $this->get('/dashboard')->assertOk();
    }

    private function seedCakeShop(): void
    {
        $this->seed([
            ProductSeeder::class,
            CakePreviewSeeder::class,
            CustomerSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
