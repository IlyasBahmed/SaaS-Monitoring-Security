# DevSecOps Security Platform

A Laravel-based security operations platform for monitoring client projects, WordPress assets, Cloudflare protection, alerts, incidents, and security reports from one dashboard.

The application is built as a PFE/DevSecOps project and includes both an admin/SOC workspace and a client portal.

## Features

- Role-based dashboards for administrators, SOC analysts, and clients.
- Client and project management with project health, agent status, and security scoring.
- Cloudflare integration for zone/project protection actions and status tracking.
- Alert, incident, vulnerability, audit log, and health report models.
- Client-facing project views with recent alerts, incidents, Cloudflare coverage, and security score.
- Global and client security report request workflows.
- User invitation and role management for platform operators.
- Laravel Fortify/Breeze authentication with Sanctum support.
- DevSecOps pipeline with tests, static analysis, dependency audit, secret scanning, container scanning, DAST, and deployment.

## Tech Stack

- PHP 8.3
- Laravel 13
- Laravel Fortify, Breeze, Sanctum
- PostgreSQL for Docker deployments
- SQLite for simple local development
- MongoDB integration through `mongodb/laravel-mongodb`
- Vite, Tailwind CSS, Alpine.js
- Chart.js, GSAP, Three.js
- Docker, Nginx, GitHub Actions

## Requirements

- PHP 8.3+
- Composer
- Node.js 22+ and npm
- SQLite, PostgreSQL, or another Laravel-supported database
- Docker and Docker Compose for containerized development

## Local Setup

1. Install backend dependencies:

   ```bash
   composer install
   ```

2. Install frontend dependencies:

   ```bash
   npm install
   ```

3. Create the environment file:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   PowerShell alternative:

   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```

4. Configure your database in `.env`.

   For a quick SQLite setup:

   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   ```

   Then create the SQLite file:

   ```bash
   touch database/database.sqlite
   ```

   PowerShell alternative:

   ```powershell
   New-Item database/database.sqlite -ItemType File -Force
   ```

5. Run migrations:

   ```bash
   php artisan migrate
   ```

6. Build frontend assets:

   ```bash
   npm run build
   ```

7. Start the application:

   ```bash
   php artisan serve
   npm run dev
   ```

   Run these in two terminals, or use the one-command development script below.

The app will usually be available at `http://127.0.0.1:8000`.

## One-command Development

The Composer `dev` script starts the Laravel server, queue listener, logs, and Vite together:

```bash
composer run dev
```

## Docker Setup

The repository includes a Docker stack with:

- Laravel app container
- Nginx
- PostgreSQL
- MongoDB
- Queue worker

Start the stack:

```bash
docker compose up -d --build
```

Run migrations inside the app container:

```bash
docker compose exec app php artisan migrate --force
```

The Nginx container exposes the application on port `80`.

## Environment Variables

Important variables from `.env.example` and Docker configuration:

```env
APP_NAME="DevSecOps Security Platform"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="${APP_NAME}"

WPSCAN_API_TOKEN=
CLOUDFLARE_API_BASE_URL=https://api.cloudflare.com/client/v4
CLOUDFLARE_API_TOKEN=
```

Docker deployments also use PostgreSQL, MongoDB, Cloudflare, WPScan, mail, and optional AI provider variables such as `GROQ_API_KEY` and `GROQ_MODEL`.

## Useful Commands

```bash
# Run tests
php artisan test

# Run the Composer test script
composer run test

# Static analysis
vendor/bin/phpstan analyse --memory-limit=1G

# Format PHP code
vendor/bin/pint

# Build frontend assets
npm run build

# Start Vite only
npm run dev

# Clear Laravel config cache
php artisan config:clear
```

## Main Application Areas

- `/dashboard` - admin/SOC overview.
- `/client-dashboard` - client security overview.
- `/client-projects` - client project inventory and posture.
- `/projects` - project management.
- `/clients` - client management.
- `/alerts` - security alert triage.
- `/reports` - report templates and report requests.
- `/users-roles` - platform user and role management.
- `/profile` - authenticated user profile settings.

Most routes require authentication and verified access.

## DevSecOps Pipeline

The GitHub Actions workflow in `.github/workflows/devsecops.yml` runs:

- Composer dependency installation
- npm dependency installation
- Vite build
- Laravel migrations
- PHPStan static analysis
- PHPUnit/Laravel tests
- Composer audit
- Gitleaks secret scanning
- Trivy Docker image scanning
- OWASP ZAP baseline DAST scan
- SSH-based deployment to a VM

Required deployment and DAST secrets include:

- `STAGING_URL`
- `VM_IP`
- `SSH_PRIVATE_KEY`
- `GITHUB_TOKEN` is provided by GitHub Actions

## Project Structure

```text
app/                  Laravel application code
app/Http/Controllers  Dashboard, project, Cloudflare, agent, profile, and user controllers
app/Models            Users, clients, projects, alerts, incidents, agents, reports, and security data
database/migrations   Database schema
resources/views       Blade pages and layouts
routes                Web and authentication routes
docker                Container support files
nginx                 Nginx configuration
terraform             Infrastructure files
ansible               Configuration/deployment automation
.github/workflows     CI/CD and DevSecOps pipeline
```

## Notes

- Keep `.env` private and never commit real API tokens or credentials.
- Use `.env.example` as the source of truth for required local configuration.
- When using external integrations, configure Cloudflare and WPScan tokens before running related workflows.
- Queue-backed features require a queue worker, either through `composer run dev` or the Docker `queue` service.
