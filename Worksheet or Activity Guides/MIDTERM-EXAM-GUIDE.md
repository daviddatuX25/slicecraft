# Midterm Exam Implementation and Defense Guide

This guide follows `Midterm-Exam.md` and describes the completed Page 3 implementation.
The application is a small admin-side cake shop. The main generated cake assets are intentionally deferred because they are being produced separately from `art-reference-pipeline/`.

## 1. What the examiner should see

The cake shop is inside the existing application shell:

```text
Existing app shell
├── Dashboard
├── Movies (Page 1)
├── Books (Page 2)
└── Cake Shop (Page 3)
    ├── Custom Cake Studio / live preview
    ├── Manage Product
    ├── Manage Customer
    ├── Manage Cart (rubric)
    │   ├── View Cart Products
    │   └── Delete Cart Products
    └── Order History (read-only support view)
```

There is only one sidebar. Page 3 adds its own compact links for Studio, Products, Customers, Cart, and Orders, but it does not create a second admin shell.

The four seeded cake bases are:

```text
1. Classic Ube
2. Chocolate Fudge
3. Red Velvet
4. Mango Celebration
```

The current reviewed preview set is intentionally sparse while image generation is in progress:

```text
minimal finish
coconut or fruit garnish
premium drip or topping
floral or celebration decoration
Bento-size presentation
```

There are currently 13 deployed preview images: 5 for Classic Ube, 6 for Chocolate Fudge, 2 for Red Velvet, and none yet for Mango Celebration. The builder reads each product's available `CakePreview` rows, shows only reviewed combinations, and uses the product base image when a future preview is absent.

## 2. Rubric map

| Rubric row | Working evidence | Main files |
|---|---|---|
| Login - 10 points | Username/password are checked against `users`; failed login stays out; successful login reaches `/p3` | `AuthController`, users migration, login view, routes |
| Manage Product - 20 points | Search/view, add, update, and delete use persisted Product records | `Product`, `ProductController`, product views, products migration |
| Manage Customer - 10 points | Customer list/detail is visible and status changes persist | `Customer`, `CustomerController`, customer views |
| Manage Cart - 10 points | A draft cart shows two cart products; one cart item can be deleted while the cart and sibling remain. Placed orders are shown separately as read-only history. | `Order`, `OrderItem`, `CartController`, `OrderController`, cart/order views |
| Question and Answer - 10 points | The request flow, MVC roles, validation, database, and frontend boundary can be explained | This guide |

## 3. File map

Authentication:

```text
routes/web.php
  Maps GET /login, POST /login, POST /logout, and protected /p3 routes.

app/Http/Controllers/AuthController.php
  Validates username/password, calls Auth::attempt(), regenerates the session,
  and invalidates the session on logout.

app/Models/User.php
  Keeps the existing Laravel user model and adds username to fillable fields.

database/migrations/*_add_username_to_users_table.php
  Adds a nullable, unique username and backfills existing users safely.

resources/views/layouts/guest.blade.php
resources/views/auth/login.blade.php
  Render the login form without adding the application sidebar.
```

Cake shop and preview foundation:

```text
app/Http/Controllers/CakeShopController.php
  Loads active products and their available preview mappings for Page 3.

app/Models/Product.php
app/Models/CakePreview.php
  Represent the cake bases and their curated image mappings.

config/cake.php
  Stores the small, readable option catalog: sizes, fillings, frostings,
  toppings, and decorations.

resources/views/pages/page3.blade.php
  Keeps the preview as the main visual surface and renders the options.

resources/js/app.js
  Registers the small Alpine cakeBuilder component. It changes selected
  products, combinations, options, and the browser-side estimate only.

resources/css/app.css
  Loads Tailwind theme/utilities without global preflight so the earlier
  Bootstrap/INSPINIA worksheet pages are not reset.
```

Product module:

```text
database/migrations/*_create_products_table.php
database/factories/ProductFactory.php
database/seeders/ProductSeeder.php
app/Http/Controllers/ProductController.php
resources/views/pages/products/index.blade.php
resources/views/pages/products/create.blade.php
resources/views/pages/products/edit.blade.php
resources/views/pages/products/show.blade.php
resources/views/pages/products/_form.blade.php
```

The shared Product form also accepts an optional base image and one optional upload for each configured combination key. Files are validated and stored on Laravel's public disk; an omitted slot is not inserted as a fake preview, so the seeded presentation reflects only reviewed combinations.

Customer module:

```text
database/migrations/*_create_customers_table.php
database/factories/CustomerFactory.php
database/seeders/CustomerSeeder.php
app/Http/Controllers/CustomerController.php
resources/views/pages/customers/index.blade.php
resources/views/pages/customers/show.blade.php
```

Cart and order-history module:

```text
database/migrations/*_create_orders_table.php
database/migrations/*_create_order_items_table.php
database/factories/OrderFactory.php
database/factories/OrderItemFactory.php
database/seeders/OrderSeeder.php
app/Http/Controllers/CartController.php
app/Http/Controllers/OrderController.php
resources/views/pages/cart/index.blade.php
resources/views/pages/cart/show.blade.php
resources/views/pages/orders/index.blade.php
resources/views/pages/orders/show.blade.php
```

Testing:

```text
tests/Feature/MidtermActivityTest.php
  Covers guest protection, login, logout, preview rendering, product CRUD,
  validation, customer status validation, order-item deletion, boundaries,
  the reviewed preview set, fallback behavior, image uploads, and the existing /p2 page.
```

## 4. Database design

```text
users
  id, name, username, email, password, timestamps

products
  id, name, slug, description, category, base_price_cents,
  image_path, is_active, timestamps

cake_previews
  id, product_id, selection_key, image_path, alt_text, timestamps
  unique(product_id, selection_key)

customers
  id, name, email, phone, address, status, timestamps

orders
  id, order_number, customer_id, status, total_amount_cents,
  notes, scheduled_at, timestamps

order_items
  id, order_id, product_id, product_name_snapshot, quantity,
  unit_price_cents, line_total_cents, customization_summary, timestamps
```

The new migrations are additive. The existing Books table is not repurposed or removed.
Money is stored as integer cents. For example, PHP 1,250.00 is stored as `125000`.

The active presentation database is the existing Laragon MySQL database. PHPUnit uses its clean test database configuration. Do not run `migrate:fresh` against the presentation database.

## 5. Request flow to explain

A normal Page 3 request follows this path:

```text
Browser: GET /p3
        |
        v
routes/web.php
        |
        | auth middleware checks the session
        v
CakeShopController@index
        |
        | Product::query()->with('cakePreviews')
        v
MySQL: products + cake_previews
        |
        v
resources/views/pages/page3.blade.php
        |
        | Blade prints product/preview data into Alpine's initial state
        v
Browser displays the preview-first interface
```

A Product CRUD request follows this path:

```text
Browser submits POST /p3/products
        |
        v
ProductController@store
        |
        | $request->validate(...)
        | server creates slug and converts pesos to cents
        v
Product::create(...)
        |
        v
products table
        |
        v
redirect back to the Product list with a flash message
```

When image files are included in Product Create or Edit, `ProductController` validates the files, stores them under `storage/app/public/cakes/products/<product-slug>/`, saves the public `storage/...` path, and maintains the five related `cake_previews` rows.

An individual cart product deletion follows this path:

```text
DELETE /p3/cart/{cart}/items/{orderItem}
        |
        v
CartController@destroyItem
        |
        | confirms the item belongs to the requested order
        | deletes only that order_items row
        | recalculates the draft cart total
        v
redirect to the same cart detail page
```

## 6. Exact routes

```text
GET|HEAD  /login
POST      /login
POST      /logout

GET|HEAD  /p3
GET|HEAD  /p3/products
POST      /p3/products
GET|HEAD  /p3/products/create
GET|HEAD  /p3/products/{product}
GET|HEAD  /p3/products/{product}/edit
PUT|PATCH /p3/products/{product}
DELETE    /p3/products/{product}

GET|HEAD  /p3/customers
GET|HEAD  /p3/customers/{customer}
PATCH     /p3/customers/{customer}/status

GET|HEAD  /p3/cart
GET|HEAD  /p3/cart/{cart}
DELETE    /p3/cart/{cart}/items/{orderItem}

GET|HEAD  /p3/orders
GET|HEAD  /p3/orders/{order}
```

All `/p3/*` routes use `auth` middleware. Login routes use `guest` middleware. Forms include CSRF tokens, and state changes use POST, PUT/PATCH, or DELETE rather than GET.

The examination does not state that authentication must be applied to every page in the existing application. Therefore authentication is deliberately scoped to the new Page 3 admin module. The earlier `/p1`, `/p2`, and `/dashboard` pages remain public so the previous worksheet activities continue to work.

## 7. Presentation sequence

Use this order so the examiner sees every required module without depending on image generation.

1. Start Laragon and confirm MySQL is running.
2. Open `/login`.
3. Submit one failed login to show the generic validation error.
4. Use the seeded local credentials:

```text
Username: slicecraft-admin
Password: slicecraft-demo
```

5. Show that successful login opens `/p3`.
6. On the large preview, switch between all four bases and the available reviewed combinations. Explain that unfinished products show their base image until more preview files are added through the seeder.
7. Change Bento, filling, frosting, toppings, decoration, and message. Explain that Alpine updates the visual summary and estimate in the browser; server-side persistence remains Laravel's responsibility.
8. Open Products. Search for `Ube`.
9. Add a temporary fifth product, refresh, edit its name/price, refresh, then delete it. Refresh again to prove it is gone.
10. Open Customers. View `Juan Dela Cruz`, review the customer overview/current-cart/order-history sections, change status from Pending to Active, refresh, and show that the value remains.
11. Open Cart. Open draft cart `SC-1001` and show its two cart products.
12. Delete only the Classic Ube item from Cart. Confirm the draft cart and the other item remain and the total is recalculated.
13. Open Order History. Open placed order `SC-1002` and show that it is read-only.
14. Log out and show that `/p3` now returns to the login screen.
15. Explain the request flow using the diagrams in this guide.

## 8. Short defense answers

Q: Why did you keep the cake shop on Page 3?

A: The existing application shell is part of the activity context. Keeping the feature at `/p3` reuses the provided sidebar and avoids creating a second admin application.

Q: Why is Product separate from Book?

A: Books are the existing Worksheet 2.4 data. A dedicated Product model keeps the prior worksheet working and gives the midterm its own cake catalog without changing the meaning of the Books table.

Q: What does a migration do?

A: It describes a repeatable database schema change. The new migrations add the cake shop tables and the username field without deleting the existing Books data.

Q: What does the model do?

A: The Eloquent model is the PHP representation of a table. It defines fillable fields, casts, and relationships such as Product to CakePreview and Order to OrderItem.

Q: Why use `base_price_cents` instead of a float?

A: Integer cents avoid floating-point rounding problems. The views format the integer as Philippine peso currency only when displaying it.

Q: What does `$request->validate()` do?

A: It checks submitted input before the controller writes to the database. Invalid input returns to the form with errors; valid input continues to the create or update operation.

Q: Why is the login database-backed?

A: `Auth::attempt()` receives the submitted username and password, looks up the user through the configured User provider, and compares the password with the hashed database value.

Q: Why regenerate the session after login?

A: Session regeneration reduces session-fixation risk. Logout invalidates the session and creates a new CSRF token.

Q: Why are Cart and Order History separate in the interface?

A: The existing `orders` table uses a `cart` status for the editable draft and other statuses for placed history. This keeps the rubric's Cart actions clear while avoiding a second schema table or an unnecessary checkout system. Cart items can be deleted; placed order items are preserved as read-only history.

Q: Why are the order items separate from their parent record?

A: Each product is an order item inside a cart or placed order. This lets the required delete operation remove one cart product without deleting unrelated sibling products or the parent cart.

Q: Why check that an order item belongs to the order?

A: The URL contains both IDs. The controller verifies their relationship before deleting, so a user cannot submit one order ID with another order's item ID.

Q: What does `@csrf` do?

A: It adds a token that Laravel checks on state-changing forms so another site cannot silently submit the user's session.

Q: Why use Blade instead of React or Vue?

A: The activity is a Laravel MVC exercise. Blade keeps the request, controller, model, and view flow visible and easy to defend. Alpine is limited to small preview interactions instead of replacing the application with a SPA.

Q: What is Tailwind doing here?

A: Tailwind supplies the consistent Page 3 visual system: spacing, responsive grids, colors, cards, buttons, focus states, and the large preview surface.

Q: What happens when a generated preview image is absent?

A: The database row still maps the planned combination filename, but `hasAvailableImage()` checks the local public path. When it is absent, the controller sends a null exact-image URL and Alpine displays the product's local base image instead.

Q: Did the browser price become trusted database data?

A: No. The current preview estimate is for presentation only. Any future order-creation action must recalculate prices on the Laravel server from trusted product and option values rather than trusting a hidden browser total.

## 9. Asset handoff

The art pipeline remains separate from the application implementation. When a generated file is ready, place it at the stable path named in `art-reference-pipeline/prompts/filename-map.csv`.

The expected filename pattern is:

```text
public/images/cakes/<base-slug>-<combination-key>.png
```

Examples:

```text
public/images/cakes/classic-ube-minimal.png
public/images/cakes/classic-ube-bento.png
public/images/cakes/chocolate-fudge-fruit.png
```

After adding files:

1. Keep the filename and combination key unchanged.
2. Do not copy the Cake2Go reference photographs into final application assets.
3. Reload `/p3`; no controller or migration change is required.
4. Confirm that `Generated combination image pending` disappears for the new file's selected state.
5. Keep the local fallback for every state whose file is not ready.

## 10. Verification commands

From the project root:

```text
php artisan view:clear
php artisan migrate:status
php artisan route:list --except-vendor --path=p3
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run build
```

The current verified result is:

```text
22 tests passed after the cart/order distinction, shell bug fixes, image-upload extension, and sparse asset seeder
140 assertions passed after the cart/order distinction, shell bug fixes, image-upload extension, and sparse asset seeder
npm run build passed
composer.json is valid
4 products, 13 reviewed previews, 3 customers, 2 order records, 3 order items in the presentation database
```

To start the local server:

```text
php artisan serve --host=127.0.0.1 --port=8001
```

Open:

```text
http://127.0.0.1:8001/login
```

Stop it with Ctrl+C. If the PHP child process remains on Windows, identify and stop the process by port from Git Bash:

```text
netstat -ano | grep ':8001'
taskkill /T /F /PID <PID>
```

## 11. Scope notes for the defense

The completed path is deliberately intermediate-level and rubric-first. It does not add payment processing, email, queues, external APIs, a customer storefront, a second frontend framework, or an option-pivot schema.

The main generated art is not a blocker. The database preview contract, image filenames, fallback behavior, and UI are already prepared. The remaining asset work can be done independently without changing the Laravel routes or data model.
