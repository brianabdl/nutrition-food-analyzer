# Nutrition Food Analyzer
Web application for browsing, comparing, and analyzing the nutritional content of foods against daily dietary standards.


## Getting Started
```bash
# Install dependencies, generate app key, run migrations, build assets
composer run setup

# Start dev server (Laravel + queue + log tail + Vite HMR)
composer run dev
```

Then open `http://localhost:8000` and log in with a seeded account.

## Database Seeding

```bash
php artisan db:seed   # runs FoodsSeeder, StandardsSeeder, UsersSeeder
```

Seeders parse CSV files from `database/data/` (`foods.csv`, `standard-nutrition.csv`). CSV column headers must stay in sync with `Food::NUTRIENT_MAP`.

## Common Commands

```bash
# Run all tests
composer run test

# Run a single test file
php artisan test --filter ExampleTest

# Lint PHP with Pint
./vendor/bin/pint

# Database
php artisan migrate
php artisan tinker
```

## Configuration

App-specific settings live in `config/nutrition.php`:

| Key | Default | Description |
|-----|---------|-------------|
| `items_per_page` | 10 | Foods per page on the catalog |
| `max_comparison` | 5 | Maximum foods selectable for comparison |
| `cache_duration` | 3600 | Cache TTL in seconds |

Reference these via `config('nutrition.*')` — do not hard-code values.

## Architecture Overview

```
app/
  Http/Controllers/
    AuthController.php        # nim-based login/register/forgot-password
    FoodController.php        # food catalog (index)
    NutritionController.php   # per-food nutrition detail
    ComparisonController.php  # multi-food comparison + radar chart
    ProfileController.php
    AboutController.php
    Api/FoodController.php    # GET /api/foods (JSON, paginated)
  Models/
    Food.php                  # NUTRIENT_MAP — single source of truth for columns
  Services/
    NutritionService.php      # buildComparisons() + buildInsights()

database/data/
  foods.csv
  standard-nutrition.csv

resources/views/
  layouts/      # app shell
  foods/        # catalog + nutrition detail
  partials/     # reusable components
  auth/         # login, register, forgot-password
  about.blade.php
  profile.blade.php
```

`NutritionService` is the core business-logic layer:
- `buildComparisons(Food, Collection<Standard>)` — returns per-nutrient status used by the nutrition detail page
- `buildInsights(Collection<Food>)` — returns energy/protein highlights used by the comparison page

## License

MIT
