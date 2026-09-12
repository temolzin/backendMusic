## Welcome to the Vibeer Repository 🎵

## Vibeer
A platform for hiring musical artists that helps artists promote their services and allows clients to book them easily, with secure payments through OpenPay.

## Preview
![Login](https://img.shields.io/badge/Preview-Login-blue)
![Dashboard](https://img.shields.io/badge/Preview-Dashboard-green)

### Prerequisites 📋
To run this project, you will need:

- PHP 8.1+ server
- PostgreSQL 14
- [Composer](https://getcomposer.org/) (dependency manager)
- [kool](https://kool.dev/) (for Docker) or run locally

> **Windows users**: kool runs Linux-based Docker containers, so it requires WSL with a Linux distribution installed, plus Docker Desktop with WSL2 backend.

### Docker Installation (kool) 🐳🔧

1. Clone the repository:
    ```bash
    git clone <repo-url> backendMusic
    ```

2. Enter the project folder:
    ```bash
    cd backendMusic
    ```

3. Copy the environment file:
    ```bash
    cp .env.example .env
    ```

4. Run the full setup — this starts containers, installs deps, generates keys, migrates and seeds:
    ```bash
    kool run setup
    ```

### Local Installation 💻🔧

1. Clone the repository:
    ```bash
    git clone <repo-url> backendMusic
    ```

2. Enter the project folder:
    ```bash
    cd backendMusic
    ```

3. Install PHP dependencies:
    ```bash
    composer install
    ```

4. Copy `.env.example` and rename it to `.env`:
    ```bash
    cp .env.example .env
    ```

5. Generate the application key:
    ```bash
    php artisan key:generate
    ```

6. Generate the JWT secret:
    ```bash
    php artisan jwt:secret
    ```

7. Create a database in PostgreSQL with the same name as in your `.env` file.

8. Run migrations and seeders:
    ```bash
    php artisan migrate:fresh --seed
    ```

9. For **Windows users**: create the storage link manually:
    ```bash
    # Run PowerShell as administrator
    mklink /J public\storage ..\storage\app\public
    ```

10. Start the development server:
    ```bash
    php artisan serve
    ```

    Open the URL shown in the console (usually http://127.0.0.1:8000).

### Environment Configuration 🔐

Configure these variables in your `.env` file:

- **Google Maps API** (required for distance calculation):
    ```
    GOOGLE_MAPS_API_KEY=YOUR_API_KEY
    ```
    Enable Distance Matrix API and Maps JavaScript API in Google Cloud Console.

- **Google OAuth** (login with Google):
    ```
    GOOGLE_OAUTH_ID=YOUR_ID
    GOOGLE_OAUTH_KEY=YOUR_KEY
    ```
    Redirect URI: `http://localhost:8080/authorize/google/callback`

- **Facebook OAuth** (login with Facebook):
    ```
    FACEBOOK_CLIENT_ID=YOUR_ID
    FACEBOOK_CLIENT_SECRET=YOUR_SECRET
    FACEBOOK_REDIRECT_URL=http://localhost:8080/authorize/facebook/callback
    ```

- **SMTP Mail** (for emails):
    ```
    MAIL_MAILER=smtp
    MAIL_HOST=sandbox.smtp.mailtrap.io
    MAIL_PORT=2525
    MAIL_USERNAME=your_user
    MAIL_PASSWORD=your_password
    MAIL_ENCRYPTION=tls
    MAIL_FROM_ADDRESS=no-reply@vibeer.com
    MAIL_FROM_NAME="Vibeer"
    ```

- **OpenPay** (payment gateway): Configured from the admin panel (stored in database), not in `.env`.

### Additional Commands

- **Reset Database**:
    ```bash
    kool run artisan migrate:fresh --seed   # Docker
    php artisan migrate:fresh --seed        # Local
    ```
- **Run Queue Worker** (for background jobs):
    ```bash
    kool run artisan queue:work   # Docker
    php artisan queue:work        # Local
    ```
- **Clear Cache**:
    ```bash
    kool run artisan cache:clear   # Docker
    php artisan cache:clear        # Local
    ```
- **Interactive Console**:
    ```bash
    kool run artisan tinker   # Docker
    php artisan tinker        # Local
    ```

© Vibeer
