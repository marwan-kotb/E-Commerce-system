# E-Commerce system (Laravel + JWT + Vue.js Dashboard)

A small full-stack e-commerce demo consisting of:

- **Backend:** Laravel API with **JWT authentication**
- **Frontend:** Vue.js dashboard (Vue 3 + Vite) consuming the API

## Features

- User registration & login via **JWT**
- Products CRUD with stock management (`available` / `out_of_stock`)
- Simple **cart** per user
- **Order creation** from cart (with stock validation)
- Dashboard with stats + management UI (total products / total orders)
- Products & Cart page (manage products + add to cart + place order)
- Orders page (view all orders with items)

---

## 1. Tech Stack

### Backend

- PHP 8.3+
- Laravel 12.x
- MySQL / MariaDB
- [`php-open-source-saver/jwt-auth`](https://github.com/PHP-Open-Source-Saver/jwt-auth) for JWT

### Frontend

- Vue 3
- Vite
- Vue Router
- Axios

---

## Project Structure

Assuming two separate folders:

```text
ecommerce-api/         # Laravel backend
ecommerce-dashboard/   # Vue.js frontend
```

- repo: `https://github.com/marwan-kotb/E-Commerce-system`

---

## Backend Setup (Laravel + JWT)

### Clone & install dependencies

```bash
git clone https://github.com/marwan-kotb/E-Commerce-system.git
cd ecommerce-api

composer install
```

### Environment & database

Copy `.env`:

```bash
cp .env.example .env
```

Create a MySQL database, for example:

```sql
CREATE DATABASE ecommerce_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

In `.env` set:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_api
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

### Migrations

Run migrations:

```bash
php artisan migrate
```

This creates tables for:

- `users`
- `products`
- `cart_items`
- `orders`
- `order_items`

### Run the backend server

```bash
php artisan serve
```

By default: `http://127.0.0.1:8000`

---

## Backend API Overview

All protected endpoints use **JWT Bearer token**:

```http
Authorization: Bearer <access_token>
```

### Authentication (JWT)

**Register**

- `POST /api/auth/register`
- Body:

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123"
}
```

**Login**

- `POST /api/auth/login`
- Body:

```json
{
  "email": "john@example.com",
  "password": "secret123"
}
```

- Response:

```json
{
  "access_token": "JWT_TOKEN_HERE",
  "token_type": "bearer",
  "expires_in": 3600
}
```

**Get current user**

- `GET /api/auth/me`
- Headers: `Authorization: Bearer <access_token>`

**Logout**

- `POST /api/auth/logout`
- Headers: `Authorization: Bearer <access_token>`

---

### Products Module

Model fields:

- `id`
- `name`
- `description`
- `price` (decimal)
- `stock` (integer)
- `status` (`available` or `out_of_stock`)


**Endpoints (all require JWT):**

- `GET /api/products` – list all products
- `POST /api/products` – create a product
- `PUT /api/products/{product}` – update a product
- `DELETE /api/products/{product}` – delete a product

**Create example:**

```http
POST /api/products
Authorization: Bearer <token>
Content-Type: application/json
```

```json
{
  "name": "Phone",
  "description": "Test phone",
  "price": 200,
  "stock": 5
}
```

---

### Cart Module

Each user has their own cart stored in `cart_items`:

- `user_id`
- `product_id`
- `quantity`

**Endpoints (require JWT):**

- `GET /api/cart` – list current user’s cart items
- `POST /api/cart` – add/update item in cart
- `DELETE /api/cart/{cartItem}` – remove item from cart

**Add to cart example:**

```http
POST /api/cart
Authorization: Bearer <token>
Content-Type: application/json
```

```json
{
  "product_id": 1,
  "quantity": 2
}
```

The backend uses `updateOrCreate` so adding the same product again overwrites quantity for that user.

---

### Orders Module

Tables:

- `orders`: `user_id`, `order_number`, `address`, `phone`, `total`, `status`
- `order_items`: `order_id`, `product_id`, `quantity`, `price`, `subtotal`

**Endpoints (require JWT):**

- `GET /api/orders` – list user’s orders (with items)
- `GET /api/orders/{order}` – show single order details
- `POST /api/orders` – create order from current user’s cart

**Order creation flow (`POST /api/orders`):**

- Request body:

```json
{
  "address": "Cairo, some street",
  "phone": "01000000000"
}
```

- Backend steps:
  1. Fetch all cart items for the authenticated user.
  2. **Validate stock** for each product:
     - If `product.stock < cart_item.quantity`, return error.
  3. Create an `Order` with a unique `order_number` (e.g. `ORD-XXXX`).
  4. For each cart item:
     - Create an `OrderItem` with `quantity`, `price` (snapshot), `subtotal`.
     - Decrease product `stock` by quantity.
     - Update product `status` → `out_of_stock` if stock reaches 0.
  5. Save `order.total` as sum of all subtotals.
  6. Clear the user’s cart (`cart_items`).

- Response example:

```json
{
  "order_number": "ORD-ABCD1234",
  "total": 400,
  "items": [
    {
      "product_id": 1,
      "product_name": "Phone",
      "quantity": 2,
      "price": 200,
      "subtotal": 400
    }
  ]
}
```

---

## Frontend Setup (Vue 3 Dashboard)

The frontend is a small admin dashboard that talks to the Laravel API.

### Clone & install

```bash
git clone https://github.com/marwan-kotb/E-Commerce-system.git
cd ecommerce-dashboard

npm install
```

### API base URL

Adjust `baseURL` if your backend runs on a different host/port.

### Run the frontend

```bash
npm run dev
```

By default: `http://127.0.0.1:5173`

Make sure:

- Backend is running: `php artisan serve` (port 8000)
- Frontend is running: `npm run dev` (port 5173)

---

## Frontend Features

### Authentication Pages

**Login Page**

- Sends `POST /api/auth/login`.
- Stores received `access_token` in `localStorage` as `token`.
- After login: redirects to Dashboard.

**Register Page**

- Sends `POST /api/auth/register`.
- On success: redirects to Login.

**Route Guards**

A simple router guard checks `localStorage.token`:

- Routes with `meta.requiresAuth` redirect to `/login` if unauthenticated.
- `/login` and `/register` redirect to `/` if already logged in.

---

### 6.3. Dashboard Home

**Route:** `/`

- Fetches:
  - `GET /api/products`
  - `GET /api/orders`
- Displays:
  - Total number of products
  - Total number of orders
- Quick navigation cards:
  - “Manage Products & Cart” (link to `/products`)
  - “View Orders” (link to `/orders`)

---

### Products & Cart Page

**Route:** `/products`

Contains three main sections:

1. **Product Form**
   - Create new product or edit existing.
   - Uses:
     - `POST /api/products`
     - `PUT /api/products/{id}`

2. **Products Table**
   - Lists all products via `GET /api/products`.
   - Shows:
     - `id`, `name`, `price`, `stock`, `status (available / out_of_stock)`
   - Actions:
     - **Edit** → fills form.
     - **Delete** → `DELETE /api/products/{id}`.
     - **Add to cart**:
       - Quantity input (default 1)
       - `POST /api/cart` with `product_id` & `quantity`
       - Disabled if product stock is 0.

3. **Cart & Place Order Panel**
   - Shows cart items via `GET /api/cart`.
   - Displays:
     - Product name, quantity, price, subtotal.
     - Total cart amount (computed on frontend).
     - Remove item: `DELETE /api/cart/{cartItem}`.
   - Place order:
     - Inputs: `address`, `phone`
     - Sends `POST /api/orders`
     - On success:
       - Shows order summary (order number, total)
       - Clears local cart view by refetching from `/api/cart`
       - Refetches `/api/products` to show updated stocks.

---

### Orders Page

**Route:** `/orders`

- Fetches `GET /api/orders`.
- Shows table with:
  - `id`, `order_number`, `total`, `status`, `created_at`
- “Show” button expands order details:
  - Items table: `product name`, `quantity`, `price`, `subtotal`.

---

## Authentication Flow Summary

1. **Register** via `/api/auth/register`.
2. **Login** via `/api/auth/login` → receive `access_token`.
3. Frontend stores token in `localStorage`:
   - `localStorage.setItem('token', access_token)`
4. Axios interceptor attaches `Authorization: Bearer <token>` to all API calls.
5. Backend protects routes with `auth:api` (JWT guard).
6. Logout:
   - Frontend: calls `/api/auth/logout`, clears `localStorage.token`.
   - Backend: invalidates token (depending on configuration).

---

## 8. How to Run Everything Locally (Quick Steps)

1. **Backend**
   - `git clone https://github.com/marwan-kotb/E-Commerce-system.git`
   - `cd ecommerce-api`
   - `composer install`
   - Set up `.env` (DB + `JWT_SECRET`)
   - `php artisan migrate`
   - `php artisan serve` → `http://127.0.0.1:8000`

2. **Frontend**
   - `git clone https://github.com/marwan-kotb/E-Commerce-system.git`
   - `cd ecommerce-dashboard`
   - Update `src/api.js` `baseURL` if needed
   - `npm install`
   - `npm run dev` → `http://127.0.0.1:5173`

3. **Use the app**
   - Visit `http://127.0.0.1:5173`
   - Register a user, then login.
   - Create products.
   - Add products to cart.
   - Place order.
   - Check Orders page and Dashboard stats.

## Demo API && front end
![1](img/1.png)
![2](img/2.png)
![3](img/3.png)
![4](img/4.png)
![5](img/5.png)
![6](img/6.png)
![7](img/7.png)
![8](img/8.png)
![9](img/9.png)
![10](img/10.png)
![11](img/11.png)
![12](img/12.png)
![13](img/13.png)
![14](img/14.png)
![15](img/15.png)
![16](img/16.png)
![17](img/17.png)
![18](img/18.png)


