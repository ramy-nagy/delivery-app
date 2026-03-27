# Delivery App — Mobile API Guide (v1)

This document describes the **REST JSON API** for iOS and Android clients. All paths are relative to the API base URL.

## Base URL

| Environment | Example |
|-------------|---------|
| Local (Laravel `artisan serve`) | `http://10.0.2.2:8000/api/v1` (Android emulator) or `http://localhost:8000/api/v1` (iOS simulator) |
| Staging / production | `https://<your-domain>/api/v1` |

Laravel mounts these routes under the **`/api`** prefix; version segment is **`v1`**.

## Conventions

- **Content-Type:** `application/json` for JSON bodies. `POST /upload` uses `multipart/form-data`.
- **Accept:** send `Accept: application/json` so validation errors return JSON.
- **Timestamps:** ISO-8601 strings where present (e.g. `2025-03-26T12:00:00+00:00`).
- **Money:** amounts are in **integer cents** in API responses (`total_cents`, `price_cents`, etc.). Some write endpoints accept **decimal major units** (e.g. menu `price` in dollars) and the server converts to cents—see each endpoint.
- **Pagination:** list endpoints that paginate return Laravel’s default shape: `data`, `links`, `meta` (current page, last page, etc.).

## Authentication (Laravel Sanctum)

1. **Register** or **login** (see below). The response includes a **`token`** string.
2. For protected routes, send:
   ```http
   Authorization: Bearer <token>
   ```
3. **Logout** deletes the current token on the server.

### Token storage (mobile)

- Store the token in the OS keychain / Keystore.
- Attach it to every authenticated request.
- On `401`, clear the token and send the user to login (unless refreshing token is implemented later).

## Roles

Each user has a **`role`** returned in `GET /auth/user`:

| Value | Meaning |
|-------|---------|
| `customer` | Browse restaurants, cart, orders, addresses, reviews, payments |
| `driver` | Profile, location, delivery orders (verified drivers only for order/location actions) |
| `restaurant` | Own restaurant profile, menu CRUD, incoming orders |

Calling an endpoint that requires another role returns **`403`** with a JSON `message`.

**Driver verification:** several driver routes use middleware `driver.verified`. If the driver profile is missing or not verified, those routes return **`403`**.

## Error responses

| HTTP | Typical cause |
|------|----------------|
| `401` | Missing or invalid `Authorization` bearer token |
| `403` | Wrong role or driver not verified |
| `404` | Resource not found |
| `422` | Validation error (`errors` object) or business rule (often `message` only) |
| `429` | Rate limit (e.g. login throttled to 10 requests per minute per key) |
| `501` | Not implemented (social auth placeholders) |

Validation errors (Laravel):

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

Business / domain errors often look like:

```json
{
  "message": "Restaurant is closed."
}
```

---

## Public endpoints (no token)

### Auth

#### `POST /auth/register`

Creates a user. For `role: restaurant`, **`business_name`** is required. A **restaurant** or **driver** profile may be created automatically.

**Body:**

| Field | Type | Required | Notes |
|-------|------|----------|--------|
| `name` | string | yes | |
| `email` | string | yes | unique |
| `password` | string | yes | min 8 |
| `password_confirmation` | string | yes | |
| `phone` | string | no | |
| `role` | string | yes | `customer`, `driver`, or `restaurant` |
| `business_name` | string | if restaurant | |
| `device_name` | string | no | stored as Sanctum token name (default `api`) |

**201** example:

```json
{
  "token": "1|xxxxxxxx",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Jane",
    "email": "jane@example.com",
    "phone": null,
    "role": "customer",
    "created_at": "..."
  }
}
```

#### `POST /auth/login`

**Body:** `email`, `password`, optional `device_name`.

**422** if credentials are invalid.

**200:** same token + `user` shape as register.

#### `POST /auth/forgot-password`

**Body:** `email`. Uses Laravel’s password reset notification (configure mail in `.env`).

#### `POST /auth/reset-password`

**Body:** `email`, `token`, `password`, `password_confirmation`.

#### `GET /auth/social/{provider}` / `POST /auth/social/{provider}/callback`

Returns **501** until OAuth is wired.

### Restaurants (browse)

#### `GET /restaurants`

Query:

| Param | Default | Description |
|-------|---------|-------------|
| `open_only` | `true` | `true` / `1` / `0` / `false` |
| `q` | — | Search name/description |
| `page` | 1 | Pagination |

#### `GET /restaurants/{restaurant}`

`{restaurant}` is numeric **id**. Includes available **menu_items** in the payload.

#### `GET /restaurants/{restaurant}/menu-items`

Only items with `is_available: true`, ordered by `sort_order`.

### Order tracking (guest / deep link)

#### `GET /track/{uuid}`

`uuid` is the order’s UUID (36-char). Returns a small public object: `status`, restaurant name/slug, optional `driver_location`.

### Webhooks (server-to-server, not for mobile)

- `POST /payments/webhooks/stripe`
- `POST /payments/webhooks/paymob`

These log payloads; your backend team integrates signature verification and payment state.

---

## Authenticated endpoints

Send header: `Authorization: Bearer <token>`.

### Auth (any logged-in user)

| Method | Path | Description |
|--------|------|-------------|
| POST | `/auth/logout` | Revokes current token |
| GET | `/auth/user` | Current user (`UserResource`) |

### Notifications

| Method | Path | Description |
|--------|------|-------------|
| GET | `/notifications` | Paginated in-app notifications |
| POST | `/notifications/{id}/read` | Mark one read |
| POST | `/notifications/read-all` | Mark all read |

### Upload

#### `POST /upload`

`multipart/form-data`:

| Field | Rules |
|-------|--------|
| `file` | required, max 10 MB, `jpg,jpeg,png,gif,webp,pdf` |
| `folder` | optional, slug-like subfolder under `storage/app/public` |

**201:** `path`, `url` (public URL via `php artisan storage:link`).

---

## Customer (`role: customer`)

Prefix: **`/customer`**.

### Cart

| Method | Path | Description |
|--------|------|-------------|
| GET | `/customer/cart` | Current cart (`restaurant_id`, `items`, optional `restaurant`) |
| PUT | `/customer/cart` | Replace cart (see body below) |
| DELETE | `/customer/cart` | Clear cart |

**PUT `/customer/cart` body:**

```json
{
  "restaurant_id": 1,
  "items": [
    { "menu_item_id": 10, "quantity": 2, "options": [] }
  ]
}
```

All `menu_item_id` values must belong to that restaurant and be available.

### Orders

| Method | Path | Description |
|--------|------|-------------|
| GET | `/customer/orders` | Paginated list (items, restaurant, driver) |
| POST | `/customer/orders` | Place order (direct body; restaurant must be **open**) |
| POST | `/customer/orders/checkout` | Place order from **saved cart**; cart cleared on success |
| GET | `/customer/orders/{order}` | Detail |
| POST | `/customer/orders/{order}/cancel` | Cancel if status still allows (optional `reason`) |

**POST `/customer/orders` body** (direct place):

- `restaurant_id`, `items[]` (`menu_item_id`, `quantity`, optional `options`)
- `delivery_location.latitude`, `delivery_location.longitude`
- `subtotal`, `delivery_fee`, `tax` (numbers; validated with domain rules)
- optional `notes`

**POST `/customer/orders/checkout` body:**

- `delivery_location` (same as above)
- optional `delivery_fee`, `tax`, `notes`
- Subtotal is computed **server-side** from the cart and menu prices.

### Addresses (REST)

| Method | Path |
|--------|------|
| GET | `/customer/addresses` |
| POST | `/customer/addresses` |
| GET | `/customer/addresses/{address}` |
| PUT/PATCH | `/customer/addresses/{address}` |
| DELETE | `/customer/addresses/{address}` |

**POST/PATCH fields:** `label`, `line1`, `line2`, `city`, `region`, `postal_code`, `country`, `latitude`, `longitude`, optional `is_default` (only one default per user when set).

### Reviews

#### `POST /customer/reviews`

**Body:** `order_id`, `rating` (1–5), optional `comment`. Order must be **delivered** and belong to the customer; one review per order.

### Payments

#### `POST /customer/payments/process`

**Body:** `order_id`, `method` — one of: `cash`, `card`, `wallet`.

Records a **paid** payment for the full order total (integration with real PSPs is backend work).

---

## Driver (`role: driver`)

Prefix: **`/driver`**.

### Profile (no verification required)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/driver/profile` | Driver + nested `user` |
| PUT | `/driver/profile` | `vehicle_type` (`bike`,`car`,`scooter`), `license_plate` |

### Location & availability (**verified** driver)

| Method | Path | Body |
|--------|------|------|
| PUT | `/driver/location` | `latitude`, `longitude` |
| PATCH | `/driver/status` | `status`: `available`, `on_delivery`, `offline` |

### Orders (**verified** driver)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/driver/orders/available` | Ready orders with **no** driver yet |
| GET | `/driver/orders` | Orders assigned to this driver |
| GET | `/driver/orders/{order}` | Assigned order, or **ready/unassigned** pool order |
| POST | `/driver/orders/{order}/claim` | Assign self to a ready, unassigned order |
| PATCH | `/driver/orders/{order}/status` | Body: `status` `picked_up` or `delivered` (must be assigned) |

---

## Restaurant (`role: restaurant`)

Prefix: **`/restaurant`**.

### Venue profile

| Method | Path | Description |
|--------|------|-------------|
| GET | `/restaurant/me` | Owned restaurant |
| PUT | `/restaurant/me` | `name`, `description`, `phone`, `is_open`, `minimum_order` (decimal major → stored as cents), `latitude`, `longitude` |

### Orders

| Method | Path | Description |
|--------|------|-------------|
| GET | `/restaurant/orders` | Paginated |
| GET | `/restaurant/orders/{order}` | Detail + customer |
| PATCH | `/restaurant/orders/{order}/status` | Body: `status` — `accepted`, `preparing`, `ready`, `cancelled` |

### Menu items (owner)

| Method | Path | Notes |
|--------|------|--------|
| GET | `/restaurant/menu-items` | Paginated |
| POST | `/restaurant/menu-items` | `name`, `description`, **`price` (decimal major)**, optional `is_available`, `sort_order` |
| PUT | `/restaurant/menu-items/{menuItem}` | Partial update; `price` in major units if sent |
| DELETE | `/restaurant/menu-items/{menuItem}` | |

---

## Enumerations (reference)

**Order status:** `pending`, `accepted`, `preparing`, `ready`, `picked_up`, `delivered`, `cancelled`

**Driver status:** `available`, `on_delivery`, `offline`

**Payment method:** `cash`, `card`, `wallet`

**Payment status (on `PaymentResource`):** `pending`, `paid`, `failed`, `refunded`

---

## Machine-readable spec

For codegen (Swift, Kotlin, Dart, etc.), use the OpenAPI file in the same folder:

- **`openapi.yaml`**

Import it into Postman, Insomnia, or your CI schema linter.

## Quick test checklist

1. `POST /auth/register` with `role: customer` → save `token`.
2. `GET /restaurants` without auth.
3. `GET /auth/user` with `Authorization: Bearer …`.
4. `PUT /customer/cart` then `POST /customer/orders/checkout` with delivery coordinates.

---

*Generated for DeliveryApp API v1. When v2 is introduced, expect a new base path (e.g. `/api/v2`) and a separate document.*
