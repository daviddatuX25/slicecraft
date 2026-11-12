# Final Exam Implementation Plan & Student Reviewer Guide
**Course:** Prof Elective 5 – Advanced Web Development: Back-end  
**Institution:** Ilocos Sur Polytechnic State College (ISPSC) – College of Arts and Sciences  
**Program:** Bachelor of Science in Information Technology (1st Semester AY 2026-2027)  
**Project:** Online Shopping Website — Final Exam Customer View  
**Reference Document:** `docs/pelec5-final2026lab.pdf`  

---

## 1. Strict Boundary & Separation: Midterm vs. Final Exam

> [!IMPORTANT]
> **Strict Architectural Separation**:
> The Midterm project is **completed and frozen**. All Midterm controllers, views, routes (`/p3`), seeders, and tests (`MidtermActivityTest.php`) remain untouched and operational.
> The **Final Exam** is implemented as a standalone **Customer View module** with dedicated namespaces, routes, views, controllers, API endpoints, policies, and test suites.

### Comparison & Boundary Matrix

| Dimension | Midterm Exam (Completed & Frozen) | Final Exam (Customer View Upgrade) |
|---|---|---|
| **Role / Persona** | Admin / Staff Notebook | Customer / End-User Shopper |
| **Primary Route Prefix** | `/p3` (SliceCraft Admin) | `/finals` (Customer Storefront) & `/p4` |
| **API Endpoints** | None (Classic Web MVC) | `/api/v1/customer/...` (RESTful JSON APIs) |
| **Authentication** | Admin Session Login (`/login`) | Customer Web Auth + Sanctum Bearer Token Auth |
| **Controllers** | `App\Http\Controllers\CakeShopController`<br>`ProductController`<br>`CartController`<br>`CustomerController`<br>`OrderController` | `App\Http\Controllers\Finals\CustomerAuthViewController`<br>`App\Http\Controllers\Finals\CustomerStorefrontController`<br>`App\Http\Controllers\Api\Finals\*` |
| **Views** | `resources/views/pages/page3.blade.php`<br>`resources/views/pages/{products,customers,cart,orders}/` | `resources/views/finals/storefront.blade.php`<br>`resources/views/finals/auth/`<br>`resources/views/finals/cart/`<br>`resources/views/finals/orders/` |
| **Order Processing** | Admin manual cart submission | Customer Checkout with `DB::transaction()` + Laravel Queues (`ProcessOrderConfirmation`) |
| **Data Privacy Policy** | Admin sees all customers & orders | `OrderPolicy`: Customers can ONLY access their own orders (`403 Forbidden` for others) |
| **Test Suites** | `tests/Feature/MidtermActivityTest.php`<br>`tests/Feature/MidtermLabExamSeederTest.php` | `tests/Feature/FinalExamCustomerTest.php` |

---

## 2. Final Exam Rubric Mapping (50 Points Total)

| Rubric Criterion | Points | Technical Requirement | Architectural Implementation |
|---|---|---|---|
| **1. Customer Account Module** | 10 pts | Customer registration, login, logout, credential validation, API integration & token auth. | `app/Http/Controllers/Api/Finals/CustomerAuthApiController.php`<br>`app/Http/Controllers/Finals/CustomerAuthViewController.php`<br>`app/Http/Requests/Finals/CustomerRegisterRequest.php`<br>`app/Http/Requests/Finals/CustomerLoginRequest.php` |
| **2. Product Browsing Module** | 10 pts | Dynamic product catalog via Eloquent ORM / Query Builder, live search, category filtering, product detail API & view. | `app/Http/Controllers/Api/Finals/ProductApiController.php`<br>`app/Http/Controllers/Finals/CustomerStorefrontController.php`<br>`resources/views/finals/storefront.blade.php` |
| **3. Shopping Cart Module** | 10 pts | Add, view, update quantities, remove items. Accurate computation of subtotals and totals, API endpoints + database/session persistence. | `app/Http/Controllers/Api/Finals/CartApiController.php`<br>`app/Services/Finals/CustomerCartService.php`<br>`resources/views/finals/cart/` |
| **4. Checkout & Order Processing** | 10 pts | Checkout computation, order placement, atomic `DB::transaction()`, asynchronous order confirmation using Laravel Queues. | `app/Http/Controllers/Api/Finals/CheckoutApiController.php`<br>`app/Jobs/ProcessFinalOrderConfirmation.php`<br>`database/migrations/0001_01_01_000002_create_jobs_table.php` |
| **5. Customer Order Management** | 10 pts | Customer order history, tracking status & details, customer privacy enforcement (`OrderPolicy`), automated test suite. | `app/Http/Controllers/Api/Finals/OrderApiController.php`<br>`app/Policies/CustomerOrderPolicy.php`<br>`tests/Feature/FinalExamCustomerTest.php` |

---

## 3. High-Level Architecture (Customer Flow)

```mermaid
graph TD
    subgraph Final Exam Module [/finals & /api/v1/customer]
        direction TB
        C_User["Customer / Browser"]
        C_User -->|Browse / Search| C_Store["Storefront View (/finals)"]
        C_User -->|Register / Login| C_Auth["Customer Auth (/finals/auth)"]
        C_User -->|Cart Actions| C_Cart["Cart Management (/finals/cart)"]
        C_User -->|Checkout Submit| C_Check["Checkout (/finals/checkout)"]
        C_User -->|View History| C_Orders["Order History (/finals/orders)"]
    end

    subgraph Backend Services & Queue
        C_Check -->|Wrap in DB::transaction| DB_Save["Save Order & OrderItems"]
        C_Check -->|Dispatch Asynchronous Job| QueueJob["ProcessFinalOrderConfirmation (ShouldQueue)"]
        QueueJob -->|Handled by worker| QueueWorker["Queue Worker: php artisan queue:work"]
        C_Orders -->|Enforce Policy| PolicyCheck{"CustomerOrderPolicy: Owns order?"}
        PolicyCheck -->|Yes| DB_Orders[(Database)]
        PolicyCheck -->|No| Reject["403 Forbidden"]
    end
```

---

## 4. Phased Execution Roadmap

### Phase 1: Models & Authentication Separation
- Create dedicated `CustomerUser` relation or customer authentication guard so customer logins do not conflict with midterm admin accounts.
- Enable Sanctum personal access tokens for customer API authentication.

### Phase 2: RESTful API Layer (`/api/v1/customer/...`)
- Implement standalone controllers under `App\Http\Controllers\Api\Finals\`:
  - `CustomerAuthApiController` (`register`, `login`, `logout`, `user`)
  - `ProductApiController` (`index` with search, `show`)
  - `CartApiController` (`index`, `store`, `update`, `destroy`)
  - `CheckoutApiController` (`checkout`)
  - `OrderApiController` (`index`, `show`)

### Phase 3: Database Transactions & Queue Worker (`ProcessFinalOrderConfirmation`)
- Implement `DB::transaction()` inside `CheckoutApiController` to guarantee atomic order creation, item copying, and cart clearing.
- Create queue job `app/Jobs/ProcessFinalOrderConfirmation.php` implementing `ShouldQueue`.
- Configure `database` queue driver so queued jobs are visible in the `jobs` table and executed via `php artisan queue:listen`.

### Phase 4: Customer Storefront Interface (`/finals` & `/p4`)
- Create dedicated customer views under `resources/views/finals/`:
  - `storefront.blade.php`: Modern cake and bakery catalog with real-time product search and filter.
  - `cart.blade.php`: Customer cart interface with dynamic quantity adjustments and subtotal math.
  - `checkout.blade.php`: Order review, notes, scheduled pickup/delivery, and payment mock.
  - `orders.blade.php`: "My Orders" customer tracking list and detail cards.
- Update sidebar navigation to cleanly distinguish:
  - `SliceCraft Admin (Midterm)` -> `/p3`
  - `Customer Storefront (Final Exam)` -> `/finals` (and `/p4`)

### Phase 5: Automated Testing Suite (`FinalExamCustomerTest.php`)
- Dedicated Feature test in `tests/Feature/FinalExamCustomerTest.php` asserting all 5 rubric items.
- Ensure all 30 existing midterm tests continue to pass with zero regressions.

### Phase 6: Student Reviewer & Defense Documentation
- Comprehensive Defense Guide (`docs/FINAL-EXAM-DEFENSE-GUIDE.md`) explaining MVC, APIs, Queue worker setup, and answers to common instructor oral examination questions.
