# Local Services Marketplace

A Laravel-based marketplace where customers can find and book local service providers (plumbers, tutors, cleaners, electricians, etc.).

[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://render.com/deploy?repo=https://github.com/maheen-raza86/taskly-marketplace)

---

## Tech Stack

| Layer      | Technology                        |
|------------|-----------------------------------|
| Backend    | PHP 8.2 + Laravel 11              |
| Database   | MySQL 8 via XAMPP (localhost)     |
| Auth       | Laravel built-in session auth     |
| Mail       | Log driver (local dev)            |
| Frontend   | Blade templates (to be added)     |

---

## User Roles

| Role       | Description                                              |
|------------|----------------------------------------------------------|
| `customer` | Browses services, makes bookings, leaves reviews         |
| `provider` | Lists services, manages availability, confirms bookings  |
| `admin`    | Approves providers, manages categories, views all data   |

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── HomeController.php
│   │   ├── ServiceController.php
│   │   ├── BookingController.php
│   │   ├── ReviewController.php
│   │   ├── ProviderController.php
│   │   ├── AdminController.php
│   │   └── NotificationController.php
│   ├── Middleware/
│   │   └── CheckRole.php
│   └── Kernel.php
├── Models/
│   ├── User.php
│   ├── ProviderProfile.php
│   ├── Category.php
│   ├── Service.php
│   ├── Availability.php
│   ├── Booking.php
│   ├── Review.php
│   └── Notification.php
database/
├── migrations/          (8 migration files)
└── seeders/
    ├── DatabaseSeeder.php
    ├── AdminSeeder.php
    ├── CategorySeeder.php
    └── DemoUserSeeder.php
routes/
└── web.php
```

---

## Setup Instructions

### Prerequisites

- XAMPP running (Apache + MySQL)
- PHP 8.2+ on PATH (add `C:\xampp\php` to your system PATH)
- Composer installed globally

### 1. Install Dependencies

```bash
composer install
```

### 2. Configure Environment

Copy the example env file (already provided as `.env`):

```bash
cp .env.example .env   # or just edit .env directly
```

Generate the application key:

```bash
php artisan key:generate
```

### 3. Create the Database

Open **phpMyAdmin** at `http://localhost/phpmyadmin` and create a new database:

```sql
CREATE DATABASE services_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Or via MySQL CLI:

```bash
mysql -u root -p -e "CREATE DATABASE services_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Seed the Database

```bash
php artisan db:seed
```

This creates:
- **Admin**: `admin@marketplace.com` / `password`
- **Providers**: `provider1@marketplace.com`, `provider2@marketplace.com` / `password`
- **Customers**: `customer1@marketplace.com`, `customer2@marketplace.com` / `password`
- **6 Categories**: Plumbing, Electrical, Cleaning, Tutoring, Carpentry, Painting
- **4 Demo Services** with availability slots

### 6. Start the Development Server

```bash
php artisan serve
```

Visit: `http://localhost:8000`

---

## Route Overview

### Public
| Method | URI                        | Description                        |
|--------|----------------------------|------------------------------------|
| GET    | `/`                        | Home — search/filter services      |
| GET    | `/services`                | All active services (with filters) |
| GET    | `/services/{id}`           | Single service detail              |
| GET    | `/providers/{id}`          | Provider public profile            |
| GET    | `/register`                | Registration form                  |
| POST   | `/register`                | Submit registration                |
| GET    | `/login`                   | Login form                         |
| POST   | `/login`                   | Submit login                       |
| POST   | `/logout`                  | Logout                             |

### Customer (`/customer/*`)
| Method | URI                                    | Description                  |
|--------|----------------------------------------|------------------------------|
| GET    | `/customer/bookings`                   | My bookings                  |
| POST   | `/customer/bookings`                   | Create a booking             |
| POST   | `/customer/reviews`                    | Submit a review              |
| GET    | `/customer/notifications`              | My notifications             |
| POST   | `/customer/notifications/{id}/read`    | Mark notification read       |
| POST   | `/customer/notifications/read-all`     | Mark all read                |

### Provider (`/provider/*`)
| Method | URI                                    | Description                  |
|--------|----------------------------------------|------------------------------|
| GET    | `/provider/dashboard`                  | Provider dashboard           |
| POST   | `/provider/services`                   | Create a service             |
| PUT    | `/provider/services/{id}`              | Update a service             |
| DELETE | `/provider/services/{id}`              | Delete a service             |
| GET    | `/provider/bookings`                   | Incoming booking requests    |
| PATCH  | `/provider/bookings/{id}/status`       | Confirm or cancel booking    |
| PATCH  | `/provider/bookings/{id}/complete`     | Mark booking as completed    |
| POST   | `/provider/availability`               | Set weekly availability      |

### Admin (`/admin/*`)
| Method | URI                                    | Description                  |
|--------|----------------------------------------|------------------------------|
| GET    | `/admin/dashboard`                     | Stats overview               |
| GET    | `/admin/providers`                     | Pending provider approvals   |
| POST   | `/admin/providers/{id}/approve`        | Approve/reject provider      |
| GET    | `/admin/categories`                    | Manage categories            |
| POST   | `/admin/categories`                    | Create category              |
| PUT    | `/admin/categories/{id}`               | Update category              |
| DELETE | `/admin/categories/{id}`               | Delete category              |
| GET    | `/admin/bookings`                      | All bookings                 |
| PATCH  | `/admin/bookings/{id}/status`          | Update booking status        |

---

## Booking Logic

When a customer creates a booking (`POST /customer/bookings`):

1. The requested `booking_date` is converted to a day-of-week string.
2. The provider's `availability` table is checked — the provider must have a slot for that day, and the `time_slot` must fall within `start_time` and `end_time`.
3. The `bookings` table is checked for any existing `pending` or `confirmed` booking for the same provider, date, and time slot (using `lockForUpdate()` to prevent race conditions).
4. Both checks and the insert are wrapped in `DB::transaction()`.
5. On success, a `Notification` is created for the provider.

---

## Review Logic

- Only the customer who made the booking can review it.
- The booking must have `status = 'completed'`.
- One review per booking is enforced (unique constraint on `booking_id` in the `reviews` table).
- After saving, `provider_profiles.avg_rating` and `total_reviews` are recalculated from all reviews for that provider.

---

## Middleware

`CheckRole` middleware accepts a role parameter:

```php
// In routes/web.php
Route::middleware(['auth', 'role:provider'])->group(function () { ... });
```

Returns HTTP 403 if the authenticated user's role doesn't match.

---

## Demo Credentials

| Role     | Email                          | Password   |
|----------|--------------------------------|------------|
| Admin    | admin@marketplace.com          | password   |
| Provider | provider1@marketplace.com      | password   |
| Provider | provider2@marketplace.com      | password   |
| Customer | customer1@marketplace.com      | password   |
| Customer | customer2@marketplace.com      | password   |

---

## Deploying to Render.com

### Prerequisites

- A free [Render account](https://render.com)
- This repository pushed to GitHub

### One-click Deploy

Click the **Deploy to Render** button at the top of this README, or follow the manual steps below.

> The button links to `https://github.com/maheen-raza86/taskly-marketplace`.

### Manual Deploy Steps

1. **Push to GitHub**
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git remote add origin https://github.com/maheen-raza86/taskly-marketplace.git
   git push -u origin main
   ```

2. **Create services on Render**
   - Go to [dashboard.render.com](https://dashboard.render.com) → New → Blueprint
   - Connect your GitHub repo — Render detects `render.yaml` automatically
   - This creates both the PostgreSQL database (`taskly-db`) and the web service (`taskly-app`)

3. **Set the `APP_KEY`**
   Render auto-generates `APP_KEY` via `generateValue: true` in `render.yaml`. No manual step needed.

4. **First deploy**
   The build command runs automatically:
   ```
   composer install --no-dev --optimize-autoloader
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan migrate --force
   php artisan db:seed --force
   ```

5. **Access your app** at `https://taskly-app.onrender.com`

### Environment Variables on Render

All variables are defined in `render.yaml`. The database connection string is injected automatically from the linked `taskly-db` PostgreSQL service via `DATABASE_URL`.

| Variable | Value |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `DB_CONNECTION` | `pgsql` |
| `DATABASE_URL` | auto-injected from `taskly-db` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `LOG_CHANNEL` | `stderr` |

### Notes on Free Tier

- Render's free web services **spin down after 15 minutes of inactivity** — first request after sleep takes ~30s.
- The free PostgreSQL instance is limited to 1 GB and expires after 90 days.
- Uploaded files are lost on redeploy (ephemeral disk) — integrate S3 or Cloudflare R2 for persistent storage.
