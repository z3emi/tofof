# Tofof Project Knowledge Base

## 1) Project Snapshot
- Type: Laravel 11 e-commerce system (frontend store + admin panel + employee API).
- Backend: PHP 8.2, Laravel, Eloquent ORM, Sanctum authentication, Spatie Permission.
- Frontend: Blade templates + Vite (Bootstrap, Tailwind, Sass, Alpine).
- Languages: Arabic and English with runtime locale switching.

## 2) Important Entry Points
- `public/index.php`: Web entry point.
- `bootstrap/app.php`: App bootstrap, route registration, middleware wiring.
- `routes/web.php`: Main web routes (frontend, auth, admin, backup, blog, reviews, wallet).
- `routes/api.php`: Employee/API routes protected by Sanctum and permissions.
- `artisan`: CLI entry point for Laravel commands.

## 3) Core Architecture (Where Logic Lives)

### App Layer
- `app/Http/Controllers`: Request handlers.
  - `Admin/`: Admin panel CRUD and operations.
  - `Api/`: Employee/mobile API endpoints.
  - `Auth/`: Authentication, OTP, WhatsApp verification/reset flows.
  - Root controllers: Store pages, cart, checkout, product pages, blog, tracking.
- `app/Models`: Main domain entities (orders, products, categories, users, roles, wallet, blog, reviews, tasks, accounting-related models).
- `app/Services`: Business services (inventory, discounts, backups, wallet, WhatsApp, Telegram, review moderation).
- `app/Http/Middleware`: Cross-cutting behavior (locale, admin logging, maintenance, permission checks, backup trigger).
- `app/Policies`: Authorization policies.
- `app/Observers`: Model lifecycle hooks.
- `app/Notifications`: App notifications (database/push/other channels).

### View Layer
- `resources/views`: Blade templates.
  - `layouts/app.blade.php`: Main public layout (SEO defaults, locale/dir setup, menus, assets).
  - Admin and frontend page templates live under subfolders.
- `resources/js`, `resources/css`, `resources/sass`: Frontend sources compiled by Vite.

### Persistence Layer
- `database/migrations`: Schema changes.
- `database/seeders`: Seed data.
- `database/factories`: Test/dev data factories.
- `storage/`: Runtime files, generated files, and logs.

## 4) Route Topology

### Web Routes (`routes/web.php`)
- Public storefront:
  - Homepage/shop/product/search.
  - Cart and discount-code application.
  - Blog listing/details.
  - Contact pages and submission.
  - Order tracking form + results.
- Authentication flows:
  - Standard Laravel auth routes.
  - OTP and WhatsApp-based verification/reset endpoints.
- Authenticated customer area:
  - Checkout.
  - Wishlist.
  - Profile, addresses, order history, wallet, notifications.
- Admin area (`/admin`):
  - Dashboard, users/managers/roles.
  - Products/categories/primary categories/barcodes.
  - Orders, reports, imports, backups, settings, blog management, reviews.
  - Protected by `auth:admin` plus permission checks.

### API Routes (`routes/api.php`)
- Employee login/logout and identity endpoint.
- Protected endpoints for orders/products/customers/reports/notifications/settings.
- Access control via Sanctum + permission middleware.

## 5) Configuration Files You Should Know
- `config/app.php`: Global app config.
- `config/auth.php`: Guards/providers.
- `config/permissions.php`: Permission groups and labels (very important for admin feature access).
- `config/shop.php`: Shipping/threshold defaults.
- `config/tracking.php`: Order tracking related config.
- `config/backup.php`: Backup package behavior.
- `config/laravellocalization.php`: Localization settings.

## 6) Third-Party Packages in Active Use
- `spatie/laravel-permission`: Roles and permissions.
- `spatie/laravel-backup`: Backup/restore support.
- `laravel/sanctum`: API auth tokens.
- `maatwebsite/excel`: Import/export Excel.
- `mcamara/laravel-localization`: Localization.
- `barryvdh/laravel-dompdf`: PDF generation.
- `simplesoftwareio/simple-qrcode`: QR code generation.
- `laravel-notification-channels/webpush`: Web push notifications.

## 7) Frontend and Build Pipeline
- `package.json`: Vite scripts (`dev`, `build`) and frontend dependencies.
- `vite.config.js`: Vite/Laravel integration.
- `tailwind.config.js`, `postcss.config.js`: Styling build config.
- `public/`: Static assets, PWA files (`manifest.webmanifest`, `sw.js`, icons/flags/images).

## 8) Runtime Data and Logs
- `storage/logs`: Laravel logs.
- `cache/`, `bootstrap/cache`: Cached config/routes/views.
- `public/storage`: Public symlink for user-uploaded files.

## 9) Safety Rules for AI Agents (Very Important)
- Never edit `vendor/` directly.
- Never edit generated caches in `bootstrap/cache` manually.
- Prefer code changes in `app/`, `routes/`, `resources/`, `config/`, `database/`.
- For schema changes, always add migrations in `database/migrations` (do not patch production DB manually).
- Keep route permissions consistent with `config/permissions.php` and related middleware.
- Keep Arabic/UTF-8 text encoding intact when editing Blade and lang files.

## 10) Typical Development Commands
- Install PHP dependencies: `composer install`
- Install JS dependencies: `npm install`
- Run app + queue + vite (if configured): `composer run dev`
- Run Vite only: `npm run dev`
- Build assets: `npm run build`
- Run migrations: `php artisan migrate`
- Run tests: `php artisan test`

## 11) Fast Orientation for a New AI Session
1. Read `routes/web.php` and `routes/api.php` first.
2. Open target controller under `app/Http/Controllers`.
3. Track business logic in `app/Services` and model relationships in `app/Models`.
4. Check permission gates/middleware and `config/permissions.php`.
5. Inspect Blade views in `resources/views` for UI behavior.
6. Verify any related migration/seed before changing DB behavior.

## 12) Notes About This Repository
- The repository includes utility/debug scripts in project root and `tmp/`.
- Keep debug endpoints/scripts controlled and remove temporary ones after use.
- Current route file is large; when adding features, preserve middleware and route-order constraints.