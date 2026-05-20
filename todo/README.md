# Laravel Migration Todo

Migration of the **Food Nutrition Analyzer** from plain PHP to Laravel.

## Project Summary (Current Stack)
- Plain PHP 8.1 with manual MVC structure
- MySQLi for database (MySQL)
- jQuery 3.7.1 + Chart.js 3.9.1 + Font Awesome 6.4.0
- Session-based auth with NIM + password
- File-based cache system
- Docker setup with MySQL

## Pages to Migrate
| Current File | Laravel Route | Auth Required |
|---|---|---|
| `index.php` | `GET /` | Yes |
| `nutrition.php` | `GET /foods/{name}/nutrition` | Yes |
| `comparison.php` | `GET /comparison` | Yes |
| `about.php` | `GET /about` | Yes |
| `profile.php` | `GET /profile` | Yes |
| `login.php` | `GET /login` `POST /login` | No |
| `register.php` | `GET /register` `POST /register` | No |
| `logout.php` | `POST /logout` | Yes |
| `api/search_foods.php` | `GET /api/foods` | Yes |
| `api/active_users.php` | `GET /api/active-users` | Yes |

## Migration Order
1. [01-setup.md](01-setup.md) — Laravel project setup & config
2. [02-database.md](02-database.md) — Migrations & seeders
3. [03-models.md](03-models.md) — Eloquent models
4. [04-auth.md](04-auth.md) — Authentication system
5. [05-controllers.md](05-controllers.md) — Controllers
6. [06-views.md](06-views.md) — Blade views & layout
7. [07-routes.md](07-routes.md) — Route definitions
8. [08-api.md](08-api.md) — API endpoints
9. [09-services.md](09-services.md) — Service classes
10. [10-assets.md](10-assets.md) — CSS/JS assets
