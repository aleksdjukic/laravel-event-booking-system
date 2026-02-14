# Event Booking System Backend (Laravel 12)

## Overview
API-only Event Booking System built with Laravel 12. It includes Sanctum authentication, role-based access control (RBAC), events, tickets, bookings, mocked payments, caching, queued notifications, and automated tests.

## Setup
1. `composer install`
2. `cp .env.example .env`
3. `touch database/database.sqlite`
4. `php artisan key:generate`
5. `php artisan migrate:fresh --seed`
6. `php artisan test`
7. `php artisan serve`

SQLite file note:
- macOS/Linux: `touch database/database.sqlite`
- Windows: create `database/database.sqlite` manually

## Demo Credentials
- `admin@example.com` / `password123`
- `organizer@example.com` / `password123`
- `customer@example.com` / `password123`

Seeders also create the full dataset for skill-test review (users, events, tickets, bookings, and payments).

## API Endpoints (`/api/v1`)
- `GET /api/v1/ping`
- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/user/me`
- `GET /api/v1/events`
- `GET /api/v1/events/{id}`
- `POST /api/v1/events`
- `PUT /api/v1/events/{id}`
- `DELETE /api/v1/events/{id}`
- `POST /api/v1/events/{event_id}/tickets`
- `PUT /api/v1/tickets/{id}`
- `DELETE /api/v1/tickets/{id}`
- `POST /api/v1/tickets/{id}/bookings`
- `GET /api/v1/bookings`
- `PUT /api/v1/bookings/{id}/cancel`
- `POST /api/v1/bookings/{id}/payment`
- `GET /api/v1/payments/{id}`

## Roles and Permissions
- `admin`: full access to events, tickets, bookings, and payments.
- `organizer`: manages only own events and tickets; cannot use customer-scoped booking/payment flows.
- `customer`: creates bookings, views own bookings, cancels own pending bookings, pays own bookings, views own payments.

## Response Envelope and Status Codes
Every API response follows:
- `success: bool`
- `message: string`
- `data: mixed|null`
- `errors: mixed|null`

Status codes:
- `200`: successful read/update/delete/logout/me
- `201`: successful create/register/booking/payment
- `401`: unauthenticated
- `403`: forbidden
- `404`: not found
- `409`: conflict (inventory, duplicate payment, invalid state, double booking)
- `422`: validation errors

## Caching
- Events index response is cached only when query params contain only `page`.
- Cache key format: `events:index:v{version}:page:{page}`
- TTL: `120` seconds
- Invalidations use version bumping via `events:index:version` on event/ticket mutations.

## Design Decisions
- `users.role` is stored as string in DB for portability and cast to `App\Enums\Role` in the `User` model.
- `User -> payments` is implemented with `hasManyThrough` via `bookings`.
- `tickets.quantity` is treated as remaining inventory and is decremented only after successful payment.

## Queue and Notifications
- Booking confirmation notification implements `ShouldQueue`.
- Notification channel: `database`.
- Payload uses primitive fields (`booking_id`, `event_title`, `ticket_type`, `quantity`).
- Tests assert queue/notification behavior with `Queue::fake()` and `Notification::fake()`.

## Postman Usage
- Include `Accept: application/json` on all requests.
- Include `Authorization: Bearer {{token}}` on authenticated requests.
- Optional payment idempotency: include `Idempotency-Key: <unique-key>` for `POST /api/v1/bookings/{id}/payment`.
- `postman_collection.json` is provided in repo root.
- Collection includes login requests for admin/organizer/customer users.
- Login request test script stores token for follow-up protected calls.

## OpenAPI / Swagger
- OpenAPI spec is available at `openapi/openapi.yaml`.
- You can import it into Swagger UI / Postman for contract browsing.

## Coverage (Optional)
If Xdebug is installed:
- `XDEBUG_MODE=coverage php artisan test --coverage-text`
