# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# First-time setup (install deps, generate key, migrate, build assets)
composer run setup

# Start all dev processes (Laravel server + queue + log tail + Vite HMR)
composer run dev

# Run tests
composer run test

# Run a single test file
php artisan test --filter ExampleTest

# Lint PHP with Pint
./vendor/bin/pint

# Database operations
php artisan migrate
php artisan db:seed          # runs FoodsSeeder, StandardsSeeder, UsersSeeder
php artisan tinker
```

## Architecture

**Stack:** Laravel 13 / PHP 8.3, SQLite (default), Tailwind CSS v4, Vite 8.

**Authentication:** Uses `nim` (student ID number) instead of email. `AuthController` logs in via `Auth::attempt(['nim' => ..., 'password' => ...])`. Every login creates a `UserSession` record; logout marks it inactive. `UpdateSessionActivity` middleware keeps `last_activity` current on each request.

**Core data flow:**
1. `foods` table — 21 nutrient columns (all nullable decimals). `Food::NUTRIENT_MAP` maps human-readable nutrient names to column names and is the single source of truth used by views, seeders, and `NutritionService`.
2. `standards` table — per-nutrient min/max thresholds plus advisory text (`rekomendasi_harian`, `fungsi_zat`, `dampak_kelebihan`, `dampak_kekurangan`).
3. `NutritionService` — two methods: `buildComparisons(Food, Collection<Standard>)` returns per-nutrient status (normal / excess / deficiency / no-data / no-standard) used by the nutrition detail page; `buildInsights(Collection<Food>)` returns energy/protein highlights used by the comparison page.

**Routes:** All routes require `auth` except login/register. The `/api/foods` endpoint uses the same session-based auth (not token auth) and returns `FoodResource` JSON with pagination metadata.

**App config:** `config/nutrition.php` holds `items_per_page` (10), `max_comparison` (5), and `cache_duration` (3600). Reference these with `config('nutrition.*')` rather than hard-coding values.

**Seeding:** `FoodsSeeder` and `StandardsSeeder` parse CSV files from `database/data/`. Column headers in the CSV must stay in sync with `Food::NUTRIENT_MAP`.

**Frontend:** Blade templates under `resources/views/`. No JS framework — vanilla JS for the comparison page's food-selector and Chart.js for the radar chart. Assets built with Vite; entry points are `resources/css/app.css` and `resources/js/app.js`.
