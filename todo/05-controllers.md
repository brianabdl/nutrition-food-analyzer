# 05 — Controllers

## Controllers to Create

### 1. `FoodController`
File: `app/Http/Controllers/FoodController.php`

**`index(Request $request)` — replaces `index.php`**
```
GET /
Query params: search (optional string), page (optional int)
Logic:
  1. Get $search = $request->input('search', '')
  2. $foods = Food::query()->search($search)->orderBy('menu')->paginate(config('nutrition.items_per_page'))
  3. Pass to view: foods, search, totalCount ($foods->total())
  4. $foods->appends(['search' => $search]) to keep search in pagination links
Return: view('foods.index', compact('foods', 'search'))
```
> Source: `index.php:17-25`

---

### 2. `NutritionController`
File: `app/Http/Controllers/NutritionController.php`

**`show(string $name)` — replaces `nutrition.php`**
```
GET /foods/{name}/nutrition
Route param: name (URL-decoded food name)
Logic:
  1. $food = Food::where('menu', $name)->firstOrFail() → 404 if not found
  2. $standards = Standard::all()
  3. Build $comparisons array (same logic as nutrition.php:40-98)
  4. Calculate $safeNutrients, $totalNutrients, $safetyPercentage
Return: view('foods.nutrition', compact('food', 'comparisons', 'safetyPercentage', 'totalNutrients', 'safeNutrients'))
```
> Source: `nutrition.php:27-99`

**Comparison logic to port (nutrition.php:40-98):**
For each standard:
- Get food value for that nutrient using `Food::NUTRIENT_MAP`
- Check against min/max ranges
- Return status: `normal`, `excess`, `deficiency`, `no-standard`

---

### 3. `ComparisonController`
File: `app/Http/Controllers/ComparisonController.php`

**`index(Request $request)` — replaces `comparison.php`**
```
GET /comparison
Query param: foods (comma-separated URL-encoded food names)
Logic:
  1. Parse foods from ?foods= param (explode ',', urldecode each)
  2. Validate: at least 2, max config('nutrition.max_comparison') items
  3. $foods = Food::whereIn('menu', $names)->get()
  4. $standards = Standard::all()->keyBy('nutrisi') for easy lookup
  5. Calculate energy/protein insights (comparison.php:122-155)
Return: view('foods.comparison', compact('foods', 'standards', 'insights'))
```
> Source: `comparison.php:38-155`

---

### 4. `ProfileController`
File: `app/Http/Controllers/ProfileController.php`

**`index()` — replaces `profile.php`**
```
GET /profile
Logic: Just pass auth()->user() to view (already available as $currentUser)
Return: view('profile')
```
> Source: `profile.php` (mostly view-only)

---

### 5. `AboutController`
File: `app/Http/Controllers/AboutController.php`

**`index()` — replaces `about.php`**
```
GET /about
Logic: No data needed
Return: view('about')
```
> Source: `about.php`

---

### 6. API Controllers

#### `Api/FoodController`
File: `app/Http/Controllers/Api/FoodController.php`

**`search(Request $request)` — replaces `api/search_foods.php`**
```
GET /api/foods
Query params: search (string), page (int)
Logic:
  1. $foods = Food::search($search)->orderBy('menu')->paginate(config('nutrition.items_per_page'))
  2. Return JSON:
     {
       success: true,
       data: {
         foods: [...],
         pagination: { currentPage, totalPages, totalCount, itemsPerPage, showingStart, showingEnd }
       }
     }
```
> Source: `api/search_foods.php`

Note: The food data in JSON must use the display-name format (e.g. `"Energy (kJ)"` key, not `energy_kj`). Use `Food::NUTRIENT_MAP` to re-key the data. Best done in a **Resource class** — see `08-api.md`.

#### `Api/UserController`
File: `app/Http/Controllers/Api/UserController.php`

**`activeUsers()` — replaces `api/active_users.php`**
```
GET /api/active-users
Logic:
  1. Update current user's session last_activity (or let middleware handle it)
  2. Mark expired sessions inactive: UserSession::where('is_active', true)->where('last_activity', '<', now()->subMinutes(30))->update(['is_active' => false])
  3. $users = UserSession::active()->select('nim','name','login_time','last_activity')->get()
  4. Add computed idle_seconds field
Return: JSON { success: true, data: { count, users, timestamp } }
```
> Source: `api/active_users.php`

---

## Checklist
- [ ] `FoodController@index` with paginated + searchable food list
- [ ] `NutritionController@show` with full comparison logic
- [ ] `ComparisonController@index` with multi-food comparison
- [ ] `ProfileController@index`
- [ ] `AboutController@index`
- [ ] `Api/FoodController@search` returning paginated JSON
- [ ] `Api/UserController@activeUsers` returning active user list
