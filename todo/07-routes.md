# 07 — Routes

File: `routes/web.php`

## Auth Routes (guest only)
```php
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
```

## Protected Routes (auth required)
```php
Route::middleware('auth')->group(function () {
    // Main pages
    Route::get('/',         [FoodController::class, 'index'])->name('foods.index');
    Route::get('/comparison',[ComparisonController::class, 'index'])->name('comparison');
    Route::get('/about',    [AboutController::class, 'index'])->name('about');
    Route::get('/profile',  [ProfileController::class, 'index'])->name('profile');

    // Nutrition analysis — food name in URL (must be URL-encoded)
    Route::get('/foods/{name}/nutrition', [NutritionController::class, 'show'])
         ->name('nutrition.show')
         ->where('name', '.*');   // allow slashes and special chars in food name
});
```

## API Routes (auth required, JSON responses)
File: `routes/api.php` OR add to `web.php` under `/api` prefix

```php
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/foods',         [Api\FoodController::class, 'search'])->name('api.foods.search');
    Route::get('/active-users',  [Api\UserController::class, 'activeUsers'])->name('api.active-users');
});
```

> **Why web.php for API?** The current AJAX calls rely on the session cookie for auth (not API tokens). Keeping them in `web.php` under `auth` middleware makes this work without changes to the frontend JS.

## Route Notes

### Food name in URL
Current: `nutrition.php?food=Nasi+Goreng`
Laravel: `GET /foods/Nasi%20Goreng/nutrition`

In the `Analyze` button link:
```blade
{{ route('nutrition.show', ['name' => urlencode($food->menu)]) }}
```

In `NutritionController@show`:
```php
$name = urldecode($name);
$food = Food::where('menu', $name)->firstOrFail();
```

### Comparison foods in URL
Current: `comparison.php?foods=Food+A,Food+B`
Laravel: `GET /comparison?foods=Food%20A,Food%20B`

No route change needed — `?foods=` stays as query param, same as current.

### Pagination
Current: `index.php?page=2&search=nasi`
Laravel: `/?page=2&search=nasi` — identical, Laravel's `paginate()` reads `?page` automatically.

## Checklist
- [ ] Auth routes (login, register, logout) defined
- [ ] Protected routes defined in `auth` middleware group
- [ ] `/foods/{name}/nutrition` uses `->where('name', '.*')` for special chars
- [ ] API routes defined (in web.php under auth + /api prefix)
- [ ] Route names match what views use in `route()` calls
