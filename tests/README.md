# Backend tests

## Requirements

- PHP 8.1+ (the project targets PHP 8.1; PHP 8.5 works but prints deprecations)
- MySQL 8 or MariaDB 10.4+
- A dedicated test database. Never point the tests at the development database.

## One-time setup

Create the test database and user:

```sql
CREATE DATABASE vibeer_testing
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'vibeer_test'@'localhost' IDENTIFIED BY 'vibeer_test';
GRANT ALL PRIVILEGES ON vibeer_testing.* TO 'vibeer_test'@'localhost';
```

Create `backendMusic/.env.testing` (it is git-ignored):

```dotenv
APP_ENV=testing
APP_KEY=<same APP_KEY as .env>
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vibeer_testing
DB_USERNAME=vibeer_test
DB_PASSWORD=vibeer_test

BROADCAST_DRIVER=log
CACHE_DRIVER=array
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
SESSION_LIFETIME=120

MAIL_MAILER=array
BCRYPT_ROUNDS=4

JWT_SECRET=<same JWT_SECRET as .env>
JWT_TTL=60
```

If you use the Docker MySQL service instead of a local MySQL, set `DB_PORT=3307`.

Run the migrations once:

```bash
php artisan migrate --env=testing --force
```

`RefreshDatabase` recreates and rolls back the test schema automatically on every test run.

## Running the tests

```bash
composer test
composer test -- --testdox
composer test -- --filter=AuthorizationTest
```

Or directly with PHPUnit:

```bash
php vendor/bin/phpunit --testdox
```

## Conventions

- Feature tests use `Illuminate\Foundation\Testing\RefreshDatabase`.
- Authenticate API requests with `$this->actingAs($user, 'api')`.
- Use the `Tests\Concerns\CreatesUsers` trait to create a user with a role:

```php
$client = $this->userWithRole('cliente');
$artist = $this->userWithRole('artista');
$admin  = $this->userWithRole('administrador');
```

- Fake external services instead of calling them:
  - `Mail::fake()`, `Notification::fake()`, `Queue::fake()`
  - `Http::fake()` for Google Maps or the CFDI provider
  - `Storage::fake()` for uploaded files
  - `Event::fake()` for broadcast events
  - `Carbon::setTestNow()` for time-based rules
- Prefer one behavior per test and use data providers for pricing, penalties and discount rules.
- Factories still to be created: `ArtistFactory`, `ArtistSaleFactory`, `OfferFactory`,
  `QuotationFactory`, `SupportTicketFactory`, `EventTypeFactory`.

## Current suite

- `tests/Feature/Security/AuthorizationTest.php` — guests get 401 and clients get 403 on admin routes.
- `tests/Feature/ExampleTest.php` and `tests/Unit/ExampleTest.php` — framework examples.

<!-- demo PR: template preview -->
