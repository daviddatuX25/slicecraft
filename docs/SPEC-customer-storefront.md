# SPEC: Final Exam — Customer Storefront & API System
**Document Version:** 1.0  
**Methodology:** Addy Osmani Agent Skills (`spec-driven-development`, `api-and-interface-design`, `frontend-ui-engineering`)  
**Scope:** Standalone Customer View for Online Shopping Website (Ilocos Sur Polytechnic State College)  
**Status:** Approved for Implementation  

---

## 1. Phase 0: Scope Check & Capability Map

The Final Exam requires 5 independently verifiable capabilities. To ensure zero cyclic dependencies, the capability map and strict build order are defined below:

| Module ID | Capability & Responsibility | Upstream Dependencies | Verification Gate |
|---|---|---|---|
| `auth-customer` | Customer Registration, Login, Logout, Session & Sanctum Tokens | None | API tests return 200/201 + valid Bearer token |
| `catalog-search` | Eloquent Product Catalog, Live Debounced Search, Category Filter, Product Detail | None (Public) | Query builder & API assert JSON array of active products |
| `cart-engine` | Add to Cart, Update Quantity, Remove Item, Subtotal Calculation | `catalog-search`, `auth-customer` | Cart service unit/feature test asserting line & grand totals |
| `checkout-queue` | Checkout Computation, `DB::transaction()` atomic persist, Laravel Queue confirmation | `cart-engine`, `auth-customer` | `Queue::assertPushed(ProcessFinalOrderConfirmation::class)` |
| `order-privacy` | Customer Order History, Live Status Tracker, `CustomerOrderPolicy` | `checkout-queue`, `auth-customer` | Cross-customer order access asserts `403 Forbidden` |

**Build Order:** `auth-customer` ➔ `catalog-search` ➔ `cart-engine` ➔ `checkout-queue` ➔ `order-privacy` ➔ `storefront-ui`

---

## 2. Phase 1: Explicit Engineering Assumptions

```text
ASSUMPTIONS & DESIGN CONSTRAINTS:
1. Complete Midterm Isolation: The existing admin module at /p3 and its 30 tests in MidtermActivityTest.php are frozen and untouched.
2. Architecture: Web Storefront lives at /finals and /p4; REST API lives under /api/v1/customer/.
3. Authentication: Customer web UI uses dedicated 'customer' guard in config/auth.php; API uses Laravel Sanctum Personal Access Tokens.
4. Database Queue Driver: Queue jobs use database driver (jobs table) so they are verifiable via `php artisan queue:listen` and automated test fakes.
5. Frontend Stack: Modern, clean Tailwind CSS + Vanilla JS/Alpine for live reactive search, cart badge counters, and modal triggers without full page reloads.
```

---

## 3. ASCII Wireframes & UI Hierarchy (`frontend-ui-engineering`)

### A. Storefront Header & Navigation Bar
```text
+---------------------------------------------------------------------------------------------------------+
| [SliceCraft Storefront]    [ 🔍 Search cakes, flavor, category... ]    [🍰 All] [🍫 Chocolate] [🍓 Fruit] |
|                                                                                                         |
| (Switch to Staff / Admin ➔)                       [🛒 Cart (2) - ₱1,450]   [👤 Maria Santos ▾ | Logout] |
+---------------------------------------------------------------------------------------------------------+
```

### B. Catalog & Live Search Grid (`/finals`)
```text
+---------------------------------------------------------------------------------------------------------+
|  BROWSE OUR HANDCRAFTED CAKES & DESSERTS                                            Filter: [ Popular ▾ ]|
+---------------------------------------------------------------------------------------------------------+
| +-------------------------+ +-------------------------+ +-------------------------+ +-----------------+ |
| | [ Product Image ]       | | [ Product Image ]       | | [ Product Image ]       | | ...             | |
| | Classic Ube Overload    | | Chocolate Fudge Deluxe  | | Mango Cream Dream       | |                 | |
| | Rich purple yam sponge  | | Decadent dark cocoa     | | Fresh Guimaras mangoes  | |                 | |
| |                         | |                         | |                         | |                 | |
| | ₱750.00   [Details]     | | ₱850.00   [Details]     | | ₱900.00   [Details]     | |                 | |
| | [+ Add to Cart]         | | [+ Add to Cart]         | | [+ Add to Cart]         | |                 | |
| +-------------------------+ +-------------------------+ +-------------------------+ +-----------------+ |
+---------------------------------------------------------------------------------------------------------+
```

### C. Slide-Over / Pop-up Shopping Cart (`/finals/cart`)
```text
+--------------------------------------------------------------------+
| SHOPPING CART (2 items)                                       [ X ]|
+--------------------------------------------------------------------+
| 1. Classic Ube Overload                                            |
|    Base: ₱750.00                                                   |
|    Qty: [-] [ 2 ] [+]                      Subtotal: ₱1,500.00     |
|    [🗑️ Remove]                                                     |
|--------------------------------------------------------------------|
| 2. Chocolate Fudge Deluxe                                          |
|    Base: ₱850.00                                                   |
|    Qty: [-] [ 1 ] [+]                      Subtotal:   ₱850.00     |
|    [🗑️ Remove]                                                     |
|====================================================================|
| Order Subtotal:                                      ₱2,350.00     |
| Delivery / Service Fee:                                  ₱0.00     |
| Estimated Total:                                     ₱2,350.00     |
|--------------------------------------------------------------------|
| [ Continue Shopping ]                    [ Proceed to Checkout ➔ ] |
+--------------------------------------------------------------------+
```

### D. Checkout & Order Review (`/finals/checkout`)
```text
+--------------------------------------------------------------------+
| CHECKOUT & ORDER REVIEW                                            |
+------------------------------------+-------------------------------+
| CUSTOMER & DELIVERY INFORMATION    | ORDER SUMMARY                 |
|                                    |                               |
| Full Name: Maria Santos            | • 2x Classic Ube   ₱1,500.00  |
| Email:     maria@example.com       | • 1x Choco Fudge     ₱850.00  |
| Contact:   0917-123-4567           | ----------------------------- |
| Address:   123 Bonifacio St, Vigan | Total:             ₱2,350.00  |
|                                    |                               |
| Special Cake Dedication / Notes:   | Asynchronous Queue Notice:    |
| [ Happy 21st Birthday Anna!      ] | ✉️ Order confirmation will be |
|                                    | dispatched to queue instantly.|
| Delivery Date / Time:              |                               |
| [ 2026-11-20 14:00               ] | [ ✔️ Confirm & Place Order ]  |
+------------------------------------+-------------------------------+
```

### E. "My Orders" History & Live Tracker (`/finals/orders`)
```text
+---------------------------------------------------------------------------------------------------------+
| MY ORDERS & TRACKING                                                                 [ Refresh Orders ] |
+---------------------------------------------------------------------------------------------------------+
| Order #ORD-2026-8812  •  Placed: Nov 12, 2026 2:45 PM  •  Total: ₱2,350.00  •  Status: [ Processing ]   |
| Items: 2x Classic Ube, 1x Choco Fudge                                                                   |
| [ Track Status ]  [ View Receipt ]                                                                      |
|---------------------------------------------------------------------------------------------------------|
| Order #ORD-2026-4401  •  Placed: Nov 05, 2026 10:15 AM •  Total: ₱750.00    •  Status: [ Completed ]    |
| Items: 1x Classic Ube                                                                                   |
| [ View Receipt ]                                                                                        |
+---------------------------------------------------------------------------------------------------------+
```

---

## 4. API Contract & Endpoint Design (`api-and-interface-design`)

All endpoints live under `/api/v1/customer/` and return standardized JSON:
```json
{
  "status": "success",
  "data": { ... },
  "message": "Human readable confirmation"
}
```

| Method | Endpoint | Auth | Purpose |
|---|---|---|---|
| `POST` | `/api/v1/customer/register` | Public | Register customer account, return Sanctum Bearer token |
| `POST` | `/api/v1/customer/login` | Public | Authenticate customer, return Sanctum token |
| `POST` | `/api/v1/customer/logout` | Bearer Token | Revoke active Sanctum token |
| `GET` | `/api/v1/customer/me` | Bearer Token | Get authenticated customer profile |
| `GET` | `/api/v1/customer/products` | Public | Search and list products (`?search=`, `?category=`) |
| `GET` | `/api/v1/customer/products/{id}` | Public | Detailed product data with preview images |
| `GET` | `/api/v1/customer/cart` | Optional/Auth | Current cart items and computed subtotals |
| `POST` | `/api/v1/customer/cart/items` | Optional/Auth | Add product to cart with quantity |
| `PATCH` | `/api/v1/customer/cart/items/{id}`| Optional/Auth | Update quantity |
| `DELETE`| `/api/v1/customer/cart/items/{id}`| Optional/Auth | Remove item from cart |
| `POST` | `/api/v1/customer/checkout` | Bearer Token | Atomic order placement (`DB::transaction` + Queue) |
| `GET` | `/api/v1/customer/orders` | Bearer Token | Customer order history |
| `GET` | `/api/v1/customer/orders/{id}` | Bearer Token | Order details (guarded by `CustomerOrderPolicy`) |

---

## 5. Verification & Testing Guardrails

1. **Unit & Service Layer**: Verify `CustomerCartService` calculates cents and formatted pesos without rounding errors.
2. **Transaction Test**: Verify if saving an order item throws an exception, the order record is automatically rolled back.
3. **Queue Test**: Verify `Queue::fake()` catches `ProcessFinalOrderConfirmation` upon successful checkout.
4. **Security Isolation Test**: Assert that a token belonging to Customer 1 requesting Customer 2's order ID returns HTTP 403.
5. **Midterm Regression Test**: All 30 existing tests in `MidtermActivityTest.php` must continue to pass 100%.
