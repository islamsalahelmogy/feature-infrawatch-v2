# Deployment Notes

1. Install PHP 8.2+, Composer, MySQL 8, Node.js (only if rebuilding frontend assets), and Python 3.11 for later worker phases.
2. Copy `.env.example` to `.env`; set `APP_KEY`, MySQL credentials, `APP_ENV=production`, `APP_DEBUG=false`, and a strong `INFRAWATCH_ADMIN_PASSWORD`.
3. Run `composer install --no-dev --optimize-autoloader`, `php artisan key:generate`, `php artisan migrate --seed`, and `php artisan config:cache`.
4. Configure the web root to `public/`. Queue workers and the Python monitoring service are not yet shipped; do not configure them as complete monitoring services.

## IP inventory import

The supplied spreadsheet contains two ranges and 51 records with a valid MAC address. It does not provide a verified gateway or subnet mask, so set `INFRAWATCH_MAIN_FACTORY_DEFAULT_GATEWAY` and `INFRAWATCH_MAIN_FACTORY_SUBNET_MASK` in `.env` before running:

```powershell
php artisan migrate
php artisan db:seed --class=IpRangesSeeder
```

The import creates the Main Factory and inserts only rows that include a valid MAC address. It intentionally does not import spreadsheet usernames or passwords.
