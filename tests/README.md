# Backend tests

The project uses PHPUnit on a **dedicated MySQL test database** (`vibeer_testing`), so your development data is never touched.

1. Start MySQL (for example, from the XAMPP control panel).
2. Create the test database and user once:
    ```sql
    CREATE DATABASE vibeer_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    CREATE USER 'vibeer_test'@'localhost' IDENTIFIED BY 'vibeer_test';
    GRANT ALL PRIVILEGES ON vibeer_testing.* TO 'vibeer_test'@'localhost';
    ```
3. Copy `.env.testing.example` to `.env.testing` and fill in your credentials, then run the suite:
    ```bash
    composer test
    ```
    You can also run a specific suite or file:
    ```bash
    composer test -- --filter=AuthorizationTest
    php artisan test --testsuite=Feature
    php artisan test tests/Feature/Security/AuthorizationTest.php
    ```

The connection is configured in `.env.testing` (`DB_DATABASE=vibeer_testing`, user `vibeer_test`). Adjust `DB_USERNAME` / `DB_PASSWORD` there if your local MySQL uses different credentials.

> Each test run refreshes the test database (`RefreshDatabase`). Never point `DB_DATABASE` in `.env.testing` to the database that contains real data.

The current suite covers the authorization smoke tests:

- Guests receive `401` on protected routes.
- Clients receive `403` on admin routes.
- Admins can access the admin dashboard.
