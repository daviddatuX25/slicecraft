# Midterm Exam Guide — SliceCraft Cake Shop

## 1. Ano ang project?

Ang SliceCraft ay cake shop admin page sa existing Laravel app.

May login, cake preview, products, customers, cart, at order history.
Ginamit ang existing sidebar at layout para hindi gumawa ng second admin app.

Main page: `/p3`

```text
Existing app
├── Dashboard
├── Movies / Page 1
├── Books / Page 2
└── Cake Shop / Page 3
    ├── Custom Cake Studio
    ├── Products
    ├── Customers
    ├── Cart
    └── Order History
```

Layman explanation: Parang online admin notebook ito. Dito minamanage ng staff ang cakes, customers, at orders.

Seeded cake bases:

```text
Classic Ube
Chocolate Fudge
Red Velvet
Mango Celebration
```

Kung wala pang generated preview image, base cake image muna ang ipinapakita.

---

## 2. Rubric: saan makikita?

| Requirement | Simpleng explanation | Main files |
|---|---|---|
| Login | May valid at invalid username/password | `AuthController`, login view, `users` table |
| Manage Product | Add, view, edit, delete cakes | `ProductController`, Product views |
| Manage Customer | View customers at palitan ang status | `CustomerController`, Customer views |
| Manage Cart | Tingnan, baguhin ang quantity, at mag-delete ng isang cart item | `CartController`, Cart views |
| Question and Answer | Maipaliwanag ang MVC, routes, database, at flow | This guide |

---

## 3. File map

### Login

```text
routes/web.php
```
Nagmamapa ng login, logout, at protected Page 3 routes.

```text
app/Http/Controllers/AuthController.php
```
Tumatanggap ng login form at gumagamit ng `Auth::attempt()`.

```text
app/Models/User.php
```
Model ng users table. Kasama rito ang username.

```text
resources/views/auth/login.blade.php
resources/views/layouts/guest.blade.php
```
Ito ang login page at simple guest layout.

### Cake shop

```text
app/Http/Controllers/CakeShopController.php
```
Kumukuha ng active cakes at preview images.

```text
app/Models/Product.php
app/Models/CakePreview.php
```
Models para sa cakes at cake preview mappings.

```text
config/cake.php
```
Listahan ng sizes, fillings, frostings, toppings, at decorations.

```text
resources/views/pages/page3.blade.php
```
Main cake studio page.

```text
resources/js/app.js
```
Maliit na Alpine component para sa browser preview at estimate.

```text
resources/css/app.css
```
Tailwind styles para sa Page 3.

### Products

```text
database/migrations/*_create_products_table.php
database/factories/ProductFactory.php
database/seeders/ProductSeeder.php
app/Http/Controllers/ProductController.php
resources/views/pages/products/
```

Migration = design ng table.
Factory = fake sample data.
Seeder = naglalagay ng sample data.
Controller = CRUD logic.
Views = mga page na nakikita ng user.

### Customers

```text
database/migrations/*_create_customers_table.php
database/factories/CustomerFactory.php
database/seeders/CustomerSeeder.php
app/Http/Controllers/CustomerController.php
resources/views/pages/customers/
```

### Cart at orders

```text
database/migrations/*_create_orders_table.php
database/migrations/*_create_order_items_table.php
app/Http/Controllers/CartController.php
app/Http/Controllers/OrderController.php
resources/views/pages/cart/
resources/views/pages/orders/
```

Ang `cart` ay editable draft. Ang placed orders ay read-only history.

### Tests

```text
tests/Feature/MidtermActivityTest.php
```
Tinetest nito ang login, routes, CRUD, validation, cart deletion, uploads, at existing pages.

---

## 4. Simple MVC explanation

```text
Browser
   |
   v
Route
   |
   v
Controller
   |
   +--> Model --> Database
   |
   v
Blade View
   |
   v
HTML page sa browser
```

Layman explanation:

- Route = receptionist. Sinasabi kung saan pupunta ang request.
- Controller = worker. Gumagawa ng actual instructions.
- Model = translator. Kumokonekta sa database.
- Database = storage ng records.
- View = screen na nakikita ng user.

### Example: open `/p3`

```text
GET /p3
  -> routes/web.php
  -> auth middleware checks kung naka-login
  -> CakeShopController@index
  -> Product model gets cakes and previews
  -> page3.blade.php displays the studio
```

### Example: add product

```text
POST /p3/products
  -> ProductController@store
  -> validate input
  -> convert price to cents
  -> save Product record
  -> redirect sa product list
```

Layman explanation: Pinapasok ang form sa controller, chine-check ang data, sine-save sa database, tapos bumabalik sa listahan.

---

## 5. Database design

```text
users
  login accounts

products
  cake bases and prices

cake_previews
  image mapping ng cake combinations

customers
  customer information and status

orders
  cart or placed order header

order_items
  individual products inside an order
```

Ang `order_items` ay hiwalay para isang item lang ang puwedeng i-delete sa cart.

Ang money ay integer cents para walang decimal rounding problem.
Halimbawa: PHP 1,250.00 = `125000` sa database.

Ang bagong tables ay additive. Hindi binura o pinalitan ang existing Books table.

---

## 6. Routes

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

Lahat ng `/p3/*` routes ay may `auth` middleware.
Ibig sabihin, kailangan munang mag-login bago ma-access ang Page 3.

Forms use `@csrf` para hindi basta-basta ma-submit ng ibang website.

---

## 7. Recommended demo order

1. Start Laragon at siguraduhing running ang MySQL.
2. Open `/login`.
3. Magpakita ng isang failed login.
4. Gamitin ang seeded account:

```text
Username: slicecraft-admin
Password: slicecraft-demo
```

5. Ipakita na napupunta sa `/p3` pagkatapos ng successful login.
6. I-switch ang four cake bases at ilang cake options.
7. Open Products, mag-search ng `Ube`.
8. Add temporary product, edit ito, tapos delete.
9. Open Customers at palitan ang status ni `Juan Dela Cruz`.
10. Open Cart at ipakita ang `SC-1001`.
11. Delete ang isang cart item. Ipakita na natira ang sibling item at nagbago ang total.
12. Open Order History at ipakitang read-only ang placed order `SC-1002`.
13. Logout at ipakitang hindi na mabubuksan ang `/p3` nang walang login.
14. Ipaliwanag ang MVC flow gamit ang diagram sa guide.

---

## 8. Short defense answers

### Q: Bakit `/p3` ang cake shop?

A: Existing activity shell na ang gamit. Reuse lang ang sidebar at layout para consistent ang application.

### Q: Bakit hiwalay ang Product at Book?

A: Ang Books ay para sa Worksheet 2.4. Ang Product ay sariling cake catalog ng midterm para hindi masira ang previous activity.

### Q: Ano ang migration?

A: Blueprint ito ng database table. Dito sinasabi kung anong columns ang kailangan.

### Q: Ano ang model?

A: PHP representation ng table. Ito ang ginagamit para makipag-usap sa database.

### Q: Ano ang controller?

A: Dito nilalagay ang request logic, validation, saving, updating, at deleting.

### Q: Ano ang `validate()`?

A: Chine-check nito kung tama at complete ang submitted form bago mag-save.

### Q: Bakit cents ang price?

A: Integer cents avoids floating-point rounding errors. PHP formatting lang ang ginagawa sa display.

### Q: Bakit database-backed ang login?

A: `Auth::attempt()` hinahanap ang user at chine-check ang hashed password sa database.

### Q: Bakit nireregenerate ang session pagkatapos mag-login?

A: Security measure ito laban sa session fixation.

### Q: Bakit hiwalay ang Cart at Order History?

A: Ang cart ay editable draft. Ang placed order ay record na dapat manatiling read-only.

### Q: Bakit hiwalay ang `order_items`?

A: Para isang product lang ang ma-delete nang hindi binubura ang buong cart.

### Q: Bakit chine-check kung kabilang ang item sa order?

A: Para hindi ma-delete ang item mula sa ibang order gamit ang maling URL IDs.

### Q: Ano ang Blade?

A: Laravel templating system. Pinagsasama nito ang layout at page content para maging HTML.

### Q: Bakit Alpine at hindi React/Vue?

A: Small browser interactions lang ang kailangan. Mas simple at bagay sa Laravel MVC activity ang Alpine.

### Q: Trusted ba ang browser price?

A: Hindi. Preview estimate lang ito. Ang tunay na order price dapat i-recalculate sa Laravel server.

### Q: Ano ang mangyayari kapag walang preview image?

A: Gagamit muna ng cake base image bilang fallback para hindi blank ang preview.

---

## 9. Image asset notes

Expected path:

```text
public/images/cakes/<base-slug>-<combination-key>.png
```

Examples:

```text
public/images/cakes/classic-ube-minimal.png
public/images/cakes/classic-ube-bento.png
public/images/cakes/chocolate-fudge-fruit.png
```

Kapag may bagong image:

1. Panatilihin ang exact filename.
2. Huwag ilagay ang reference photos sa final app assets.
3. Reload ang `/p3`.
4. Kapag wala pa ang file, automatic na gagamit ng base image.

---

## 10. Verification commands

Run from the project root:

```text
php artisan view:clear
php artisan migrate:status
php artisan route:list --except-vendor --path=p3
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run build
```

Start the app:

```text
php artisan serve --host=127.0.0.1 --port=8001
```

Open:

```text
http://127.0.0.1:8001/login
```

Stop with `Ctrl+C`.

Kung naiwan ang PHP process sa Windows:

```text
netstat -ano | grep ':8001'
taskkill /T /F /PID <PID>
```

---

## 11. Scope ng project

Intermediate-level at rubric-first ang implementation.

Hindi kasama ang payment processing, email, queues, external APIs, customer storefront, o checkout system.

Ang main goal ay maipakita nang malinaw ang Laravel MVC, login, CRUD, database relationships, cart deletion, at Blade frontend.
