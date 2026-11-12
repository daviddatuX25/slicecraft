# Final Exam Defense Guide & Rubric-to-Code Reference
**Course:** Prof Elective 5 – Advanced Web Development: Back-end  
**Institution:** Ilocos Sur Polytechnic State College (ISPSC) – College of Arts and Sciences  
**Program:** Bachelor of Science in Information Technology (1st Semester AY 2026-2027)  
**Project:** Online Shopping Website (Customer View Upgrade) — SliceCraft Storefront  
**Instructor Rubric Weight:** 50 Points Total (5 Modules × 10 Points)  
**Reference Document:** `docs/pelec5-final2026lab.pdf`  

---

## 1. Quick Verification & Demonstration Commands

Before defending in front of your instructor, open two terminal windows:

### Terminal 1: Run the Queue Worker (Rubric 4)
```powershell
cd C:\Users\sarmi\ProfElec5\system-for-activities
php artisan queue:listen --tries=1
```
> *When you place an order on the storefront or via API, you will see `Processing: App\Jobs\ProcessFinalOrderConfirmation` and `Processed` appear here in real time!*

### Terminal 2: Run the Complete Test Suite
```powershell
cd C:\Users\sarmi\ProfElec5\system-for-activities
php artisan test
```
> *Result: **52 passed tests (339 assertions)** across Midterm and Final Exam with 0 failures.*

To run only the Final Exam tests:
```powershell
php artisan test --filter=FinalExamCustomerTest
```
> *Result: **22 passed tests (126 assertions)** covering all 5 rubric modules end-to-end.*

### Start the Local Development Server
```powershell
php artisan serve
```
- **Customer Storefront:** `http://127.0.0.1:8000/finals` (or `http://127.0.0.1:8000/p4`)
- **Midterm Admin View:** `http://127.0.0.1:8000/p3`
- **Customer Demo Login:** `customer@slicecraft.test` / `password123`
- **Midterm Admin Login:** `slicecraft-admin` / `slicecraft-demo`

---

## 2. Rubric-to-Code Line Mapping (What the Teacher Will Check)

### Criterion 1: Customer Account Module (10 Points)
> *"Successfully implements customer registration, login, and logout. Credentials are validated and authenticated against the database. API integration and appropriate authentication are implemented."*

| Feature | Where it is in the Code | How it Works |
|---|---|---|
| **API Registration** | `app/Http/Controllers/Api/Finals/CustomerAuthApiController.php` (lines 19–58) | Validates name, email, password; creates customer in database; returns HTTP 201 with Sanctum Bearer token. |
| **API Login** | `app/Http/Controllers/Api/Finals/CustomerAuthApiController.php` (lines 63–99) | Queries `Customer` by email; verifies password with `Hash::check()`; returns token. |
| **API Logout & Me** | `app/Http/Controllers/Api/Finals/CustomerAuthApiController.php` (lines 104–131) | Revokes active Sanctum token; returns authenticated customer profile. |
| **Web Storefront Auth** | `app/Http/Controllers/Finals/CustomerAuthViewController.php` | Uses dedicated `'customer'` session guard to avoid kicking out the admin session. |
| **Database Model** | `app/Models/Customer.php` | Uses `HasApiTokens`, `HasFactory`, `Notifiable`; casts `'password' => 'hashed'`. |
| **Auth Config** | `config/auth.php` (lines 40–74) & `config/sanctum.php` | Configures dedicated `customer` guard with `customers` Eloquent provider. |

---

### Criterion 2: Product Browsing Module (10 Points)
> *"Successfully displays products retrieved using Query Builder or Eloquent ORM. Implements product searching and viewing of product details through functional API endpoints."*

| Feature | Where it is in the Code | How it Works |
|---|---|---|
| **Eloquent Query & Search** | `app/Http/Controllers/Api/Finals/ProductApiController.php` (lines 17–59) | Queries `Product::where('is_active', true)->where('name', 'like', "%{$search}%")`; zero hardcoded data. |
| **Product Details API** | `app/Http/Controllers/Api/Finals/ProductApiController.php` (lines 64–95) | Returns single product with related `cakePreviews` relation. |
| **Storefront Blade View** | `resources/views/finals/storefront.blade.php` | Responsive grid with live search bar, price tags in ₱, and AJAX "Add to Cart" integration. |

---

### Criterion 3: Shopping Cart Module (10 Points)
> *"Successfully implements adding, viewing, updating, and removing cart items. Cart operations work correctly using appropriate API endpoints and database or session management."*

| Feature | Where it is in the Code | How it Works |
|---|---|---|
| **Add to Cart** | `app/Http/Controllers/Finals/CustomerStorefrontController.php` & `CartApiController.php` | Supports both guest session carts and database-persisted carts for logged-in customers. Automatically merges guest items into the customer's account upon login. |
| **View Cart & Totals** | `app/Services/Finals/CustomerCartService.php` (lines 37–68) | Computes item sum, accurate line totals, and grand total in cents and formatted pesos. |
| **Update Quantity** | `app/Http/Controllers/Api/Finals/CartApiController.php` (lines 92–109) | `PATCH /api/v1/customer/cart/items/{id}`; recalculates line totals and cart total. |
| **Remove Product** | `app/Http/Controllers/Api/Finals/CartApiController.php` (lines 114–128) | `DELETE /api/v1/customer/cart/items/{id}`; removes single item without deleting sibling cart items. |
| **Cart Blade View** | `resources/views/finals/cart.blade.php` | Interactive UI with `[-] [qty] [+]` controls, item removal buttons, and live subtotal math. |

---

### Criterion 4: Checkout and Order Processing Module (10 Points)
> *"Successfully implements checkout, accurate order computation, and order submission. Saves order and order details using Eloquent or Query Builder with proper transaction handling. Implements asynchronous order confirmation or notification using Laravel Queues."*

| Feature | Where it is in the Code | How it Works |
|---|---|---|
| **Atomic Transaction** | `app/Http/Controllers/Api/Finals/CheckoutApiController.php` (lines 78–122) | Wraps order update in `DB::transaction()`. If any step fails, the entire transaction rolls back cleanly (ACID). |
| **Status Transition** | `CheckoutApiController.php` (lines 98–105) | Transitions order from `'cart'` to `'pending'`; locks in final calculated totals and notes. |
| **Laravel Queue Job** | `app/Jobs/ProcessFinalOrderConfirmation.php` | Implements `ShouldQueue`. Executes asynchronously in the background via queue worker. |
| **Queue Dispatch** | `CheckoutApiController.php` (line 109) | `ProcessFinalOrderConfirmation::dispatch($placedOrder);` dispatches job immediately upon commit. |
| **Checkout Blade View** | `resources/views/finals/checkout.blade.php` | Delivery schedule picker, dedication notes, and instant submit button. |

---

### Criterion 5: Customer Order Management Module (10 Points)
> *"Successfully displays customer order history, order details, and status. Uses appropriate database queries and API endpoints, enforces customer data privacy, and demonstrates appropriate testing of application functionality."*

| Feature | Where it is in the Code | How it Works |
|---|---|---|
| **Order History Query** | `app/Http/Controllers/Api/Finals/OrderApiController.php` (lines 39–77) | Queries `Order::placed()->where('customer_id', $customer->id)`; customer sees only their records. |
| **Data Privacy Policy** | `app/Policies/CustomerOrderPolicy.php` & `OrderApiController.php` (lines 92–100) | Strictly checks `(int) $order->customer_id === (int) $actor->id`. Returns **`HTTP 403 Forbidden`** if Customer A attempts to open Customer B's order. |
| **Order Detail Tracking** | `resources/views/finals/orders/show.blade.php` | 3-step progress bar (Order Placed ➔ Baking & Prep ➔ Completed) with itemized receipt. |
| **Automated Test Suite** | `tests/Feature/FinalExamCustomerTest.php` | 22 automated tests verifying registration, login, Eloquent queries, guest shopping cart, auto-merge on login, transactions, queue fakes, and 403 privacy blocks. |

---

## 3. Oral Defense Cheat Sheet (Teacher Questions & Answers)

### Q1: "Bakit hiwalay ang guard ng Customer sa Admin?"
> **Sagot:** *"Ginamit po natin ang dedicated `customer` guard sa `config/auth.php` para hindi mag-collide ang session ng Admin (`/p3`) at Customer (`/finals`). Dahil dito, pwedeng mag-login ang admin at customer sa magkaibang tab nang hindi nagki-kick out o nag-ooverwrite ng credentials."*

### Q2: "Bakit Laravel Sanctum ang ginamit ninyo sa API imbes na JWT plugin?"
> **Sagot:** *"Ang Laravel Sanctum po ang official at native standard ng Laravel framework para sa API token authentication. Naka-store ang mga tokens sa database table na `personal_access_tokens` gamit ang SHA-256 hash. Mas matatag po ito sa Laravel 12 kaysa sa mga 3rd-party JWT packages na madalas magkaroon ng version incompatibility."*

### Q3: "Bakit mahalaga ang `DB::transaction()` sa Checkout?"
> **Sagot:** *"Ang `DB::transaction()` po ay nagsisiguro ng ACID compliance. Kapag nag-submit ang customer ng checkout, sabay-sabay nating ina-update ang order status, kinakalkula ang total, at sine-save ang order details. Kung magkaroon man ng system failure o database error sa gitna ng proseso, magro-rollback ang database para walang maiwang sira o half-saved na order."*

### Q4: "Paano gumagana ang Laravel Queues sa order confirmation ninyo?"
> **Sagot:** *"Gumawa po tayo ng `ProcessFinalOrderConfirmation` job na nag-iimplement ng `ShouldQueue` interface. Pagkatapos ma-commit ng database transaction sa checkout, dinidispatch ang job sa `jobs` table gamit ang `database` queue driver. Ang queue worker (`php artisan queue:listen`) ang nagpoproseso nito sa background nang hindi pinaghihintay ang customer sa browser."*

### Q5: "Paano ninyo pinoprotektahan ang Customer Data Privacy?"
> **Sagot:** *"Ipinatupad po natin ito gamit ang `CustomerOrderPolicy` at authorization checks sa `OrderApiController`. Kapag may nag-request sa `/api/v1/customer/orders/{id}`, tinitingnan kung ang `customer_id` ng order ay tumutugma sa authenticated customer. Kung tinangka ng Customer A na buksan ang order ng Customer B gamit ang token niya, agad magbabalik ang server ng `403 Forbidden`."*

### Q6: "Paano gumagana ang Shopping Cart kapag Guest pa lang ang bisita?"
> **Sagot:** *"Sinusuportahan po ng ating `CustomerCartService` ang dalawang modes: Session cart (`finals_cart`) para sa guest visitors, at Database cart (`orders` table na may status = 'cart') para sa logged-in customers. Kapag nag-browse at nag-add to cart ang guest, naiimbak ang items sa encrypted session. Kapag nag-login o nag-register na siya, awtomatikong tinatawag ang `mergeSessionCartIntoCustomer()` para ilipat ang lahat ng items sa kanyang persistent database cart nang hindi nawawala ang pinili niyang cakes."*
