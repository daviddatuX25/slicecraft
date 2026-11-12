<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\MidtermLabExamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MidtermLabExamSeederTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'SliceCraft Admin',
            'username' => 'slicecraft-admin',
            'password' => Hash::make('slicecraft-demo'),
        ]);
    }

    public function test_midterm_lab_exam_seeder_populates_products_and_assigns_ten_and_two_products_to_customers(): void
    {
        $this->seed(MidtermLabExamSeeder::class);

        $this->assertGreaterThanOrEqual(10, Product::count());
        $this->assertSame(12, Product::count());

        $customer1 = Customer::where('email', 'maria@slicecraft.test')->firstOrFail();
        $customer2 = Customer::where('email', 'juan@slicecraft.test')->firstOrFail();

        // Customer 1: Maria Santos has 10 products
        $cart1 = $customer1->orders()->cart()->firstOrFail();
        $this->assertSame(10, $cart1->items()->count());
        $this->assertSame(10, $cart1->items()->sum('quantity'));
        $this->assertSame(10, $customer1->products->count());

        $placed1 = $customer1->orders()->placed()->firstOrFail();
        $this->assertSame(10, $placed1->items()->count());
        $this->assertSame(10, $placed1->items()->sum('quantity'));

        // Customer 2: Juan Dela Cruz has 2 products
        $cart2 = $customer2->orders()->cart()->firstOrFail();
        $this->assertSame(2, $cart2->items()->count());
        $this->assertSame(2, $cart2->items()->sum('quantity'));
        $this->assertSame(2, $customer2->products->count());

        $placed2 = $customer2->orders()->placed()->firstOrFail();
        $this->assertSame(2, $placed2->items()->count());
        $this->assertSame(2, $placed2->items()->sum('quantity'));
    }

    public function test_customer_views_show_ten_and_two_products_in_cart_and_orders(): void
    {
        $this->seed(MidtermLabExamSeeder::class);

        $customer1 = Customer::where('email', 'maria@slicecraft.test')->firstOrFail();
        $customer2 = Customer::where('email', 'juan@slicecraft.test')->firstOrFail();

        // Customer 1 page renders with 10 cart products
        $response1 = $this->actingAs($this->admin)->get(route('customers.show', $customer1));
        $response1->assertOk();
        $response1->assertSee('Maria Santos');
        $response1->assertSee('10');

        // Customer 2 page renders with 2 cart products
        $response2 = $this->actingAs($this->admin)->get(route('customers.show', $customer2));
        $response2->assertOk();
        $response2->assertSee('Juan Dela Cruz');
        $response2->assertSee('2');
    }

    public function test_seeder_can_be_invoked_via_lowercase_alias(): void
    {
        $this->seed('Database\Seeders\midtermlabexamseeder');

        $this->assertSame(12, Product::count());
        $this->assertDatabaseHas('customers', ['email' => 'maria@slicecraft.test']);
        $this->assertDatabaseHas('customers', ['email' => 'juan@slicecraft.test']);
    }
}
