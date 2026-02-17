# Event Booking System API

[![PHP](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Sanctum](https://img.shields.io/badge/Auth-Sanctum-0EA5E9)](https://laravel.com/docs/12.x/sanctum)
[![CI](https://github.com/aleksdjukic/laravel-event-booking-system/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/aleksdjukic/laravel-event-booking-system/actions/workflows/ci.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-passing-16A34A)](https://phpstan.org/)
[![Tests](https://img.shields.io/badge/Tests-passing-16A34A)](./tests)
[![API](https://img.shields.io/badge/API-Versioned%20(v1)-2563EB)](./routes/api.php)

Production-grade Laravel backend for event booking built as a modular monolith (DDD-style), with strong API contracts, RBAC, idempotent payments, caching, and queue-backed notifications.

## Highlights
- Modular monolith architecture with explicit module boundaries and layered design.
- Versioned REST API (`/api/v1`) with uniform response envelope.
- Sanctum authentication (register/login/logout/me).
- Role-based authorization (`admin`, `organizer`, `customer`) via policies + middleware.
- Event, ticket, booking, payment domains with explicit state rules.
- Payment idempotency via `Idempotency-Key` and persisted idempotency records.
- Queue-based booking confirmation notifications.
- Cached event listing with version-based invalidation.
- Cross-DB booking invariants (active booking uniqueness).
- Full automated quality gates (`phpstan`, feature + unit tests).

## Tech Stack
- PHP 8.4+
- Laravel 12
- Laravel Sanctum
- PHPUnit
- PHPStan + Larastan
- SQLite/MySQL/PostgreSQL compatible schema constraints strategy

## Domain Model
Core entities:
- `User`
- `Event`
- `Ticket`
- `Booking`
- `Payment`
- `PaymentIdempotencyKey`

Key invariants:
- Register always creates `customer` role.
- One active booking per `(user, ticket)` pair.
- Payment allowed only for payable booking states.
- One payment per booking.
- Idempotency key cannot be reused for another booking.

## Architecture Snapshot
Architecture style:
- Modular monolith, domain-first (DDD-style).
- Each module owns `Domain`, `Application`, `Infrastructure`, and `Presentation` layers.

Core modules:
- `Auth`
- `User`
- `Event`
- `Ticket`
- `Booking`
- `Payment`
- `Health`
- `Shared`

- `app/Modules/*/Domain/*`: models, enums, policies, repository interfaces, domain guards.
- `app/Modules/*/Application/*`: use-case actions, DTOs, application services.
- `app/Modules/*/Infrastructure/*`: Eloquent repositories, observers, payment gateway, notifications.
- `app/Modules/*/Presentation/*`: routes, controllers, requests, resources, middleware.
- `app/Modules/Shared/*`: shared domain errors, API responder, cross-module presentation/support utilities.

Design direction:
- Thin controllers
- Validation in Form Requests
- Use-case oriented application layer
- Domain-first contracts + explicit invariants

## Architecture & Principles
- Domain-first modular monolith with clear layer boundaries: `Domain`, `Application`, `Infrastructure`, `Presentation`.
- Modules are isolated by responsibility and interact through contracts/use-cases, not ad-hoc cross-module calls.
- Controllers are thin; validation lives in Form Requests; use-cases are in application actions/services.
- Domain rules are explicit via enums, transition guards, policies, and DB-backed invariants.
- Interfaces define boundaries while Eloquent/integration details stay in infrastructure.
- API contract is stabilized through centralized responder + exception mapping.

## API Contract
Base path: `/api/v1`

Uniform envelope for every response:
```json
{
  "success": true,
  "message": "OK",
  "data": {},
  "errors": null
}
```

Standard statuses:
- `200` success read/update/delete
- `201` created
- `401` unauthenticated
- `403` forbidden
- `404` not found
- `409` domain conflict
- `422` validation error
- `500` unexpected server error

## Endpoint Overview
Primary endpoint groups:
- Auth: register/login/logout/me
- Events and Tickets: event CRUD + ticket management
- Bookings and Payments: booking flow, cancel flow, idempotent payment flow
- Health: ping endpoint

Source of truth:
- OpenAPI spec: `openapi/openapi.yaml`
- Live routes: `php artisan route:list`

Auth:
- `POST /auth/register`
- `POST /auth/login`
- `POST /auth/logout`
- `GET /user/me`

Events:
- `GET /events`
- `GET /events/{event}`
- `POST /events`
- `PUT /events/{event}`
- `DELETE /events/{event}`

Tickets:
- `POST /events/{event}/tickets`
- `PUT /tickets/{ticket}`
- `DELETE /tickets/{ticket}`

Bookings:
- `POST /tickets/{ticket}/bookings`
- `GET /bookings`
- `PUT /bookings/{booking}/cancel`

Payments:
- `POST /bookings/{booking}/payment`
- `GET /payments/{payment}`

Health:
- `GET /ping`

## RBAC Matrix
- `admin`: full access across events/tickets/bookings/payments.
- `organizer`: can manage only own events/tickets.
- `customer`: booking/payment flows for own records.

## Caching
Event index caching:
- Cache key: `events:index:v{version}:page:{page}`
- TTL: `120s`
- Invalidation: version bump on event mutations.

## Queues & Notifications
- Booking confirmation notification implements `ShouldQueue`.
- Notification channel: `database`.
- Triggered only for successful payment transitions.

## Idempotent Payment Flow
`POST /bookings/{booking}/payment` supports header:
- `Idempotency-Key: <key>`

Behavior:
- Same user + same key + same booking => returns existing payment response.
- Same user + same key + different booking => `409` conflict.

## Database Setup
### Quick start
1. `composer install`
2. `cp .env.example .env`
3. Configure DB connection:
   - SQLite: `touch database/database.sqlite` and set `DB_CONNECTION=sqlite`
   - MySQL/PostgreSQL: set `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
4. `php artisan key:generate`
5. `php artisan migrate:fresh --seed`
6. `php artisan serve`

### Demo credentials
- `admin@example.com` / `password123`
- `organizer@example.com` / `password123`
- `customer@example.com` / `password123`

Seeded review dataset includes:
- 2 admins
- 3 organizers
- 10 customers
- 5 events
- 15 tickets
- 20 bookings
- related payments

## Quality Gates
Static analysis:
```bash
vendor/bin/phpstan analyse --memory-limit=1G --no-progress
```

Test suite:
```bash
php artisan test
```

Coverage (optional if Xdebug enabled):
```bash
XDEBUG_MODE=coverage php artisan test --coverage-text
```

## API Tooling
- OpenAPI spec: `openapi/openapi.yaml`
- Postman collection: `postman_collection.json`

## Operational Notes
- API responses are centralized via `ApiResponder`.
- Domain errors are mapped in `bootstrap/app.php` to consistent HTTP contracts.
- Booking active uniqueness uses `active_booking_key` as canonical cross-DB strategy.

## License
MIT
