# Event Booking System Backend (Laravel 12)

## Overview
API-only Event Booking System built with Laravel 12.
Core modules: Sanctum auth, RBAC, Events, Tickets, Bookings, Payments (mocked), caching, queued notifications, and tests.

## Setup
1. `composer install`
2. `cp .env.example .env`
3. `php artisan key:generate`
4. Configure database values in `.env` (local)
5. `php artisan migrate:fresh --seed`
6. `php artisan test`
7. `php artisan serve`

## API Endpoints (ALL under /api/v1)
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

## Roles & Permissions Summary
- `admin`
  - full access to events, tickets, bookings, and payments
- `organizer`
  - manage own events and tickets
  - forbidden for customer-scoped bookings/payments endpoints
- `customer`
  - create bookings, view own bookings, cancel own pending bookings
  - pay bookings (ownership enforced), view own payments

## Response Envelope
Controller responses use a consistent JSON envelope:
- success response:
  - `success: true`
  - `message: string`
  - `data: mixed`
  - `errors: null`
- error response:
  - `success: false`
  - `message: string`
  - `data: null`
  - `errors: mixed|null`

Status codes used:
- `200` success read/update/delete/logout/me
- `201` successful create/register/booking/payment
- `401` unauthenticated
- `403` forbidden
- `404` not found
- `409` conflict (inventory, duplicate payment, invalid state, double booking)
- `422` validation errors

## Caching Notes
- Events index cache is used only when query params contain only `page`.
- Cache key format: `events:index:v{version}:page:{page}`.
- TTL: `120` seconds.
- Version key invalidation strategy: `events:index:version` is incremented on event/ticket mutations.

## Design Decisions
- `role` is stored as a string for portability and constrained in app logic to: `admin|organizer|customer`.
- `User -> payments` relation is implemented as `hasManyThrough` via `bookings`.
- `tickets.quantity` is remaining inventory and is decremented only on successful payment.

## Queue & Notification Note
- Booking confirmation notification uses `ShouldQueue` and `database` channel.
- Notification payload uses primitive fields (`booking_id`, `event_title`, `ticket_type`, `quantity`).
- Tests assert notification behavior using `Notification::fake()`.
