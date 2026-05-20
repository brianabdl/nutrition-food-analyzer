# 08 — API Endpoints & Resources

## API Resource Classes (optional but recommended)

### `FoodResource`
File: `app/Http/Resources/FoodResource.php`

Transforms `Food` Eloquent model into the display-key format the frontend JS expects.
The frontend JS uses keys like `food['Energy (kJ)']`, not `food.energy_kj`.

```php
public function toArray(Request $request): array
{
    $data = ['Menu' => $this->menu];
    foreach (Food::NUTRIENT_MAP as $displayName => $column) {
        $data[$displayName] = $this->$column;
    }
    return $data;
}
```

Usage in `Api/FoodController`:
```php
return response()->json([
    'success' => true,
    'data' => [
        'foods'      => FoodResource::collection($foods),
        'pagination' => [
            'currentPage'  => $foods->currentPage(),
            'totalPages'   => $foods->lastPage(),
            'totalCount'   => $foods->total(),
            'itemsPerPage' => $foods->perPage(),
            'showingStart' => $foods->firstItem() ?? 0,
            'showingEnd'   => $foods->lastItem()  ?? 0,
        ],
    ],
]);
```

## API Response Shape (must match current frontend JS exactly)

### `GET /api/foods`
Response used by `index.php` AJAX in `updateFoodTable()`:
```json
{
  "success": true,
  "data": {
    "foods": [
      {
        "Menu": "Nasi Goreng",
        "Energy (kJ)": "839.00",
        "Protein (g)": "4.50",
        ...
      }
    ],
    "pagination": {
      "currentPage": 1,
      "totalPages": 120,
      "totalCount": 1200,
      "itemsPerPage": 10,
      "showingStart": 1,
      "showingEnd": 10
    }
  }
}
```
> Source: `api/search_foods.php:33-47` and `index.php:373-380` (JS consumption)

### `GET /api/active-users`
Response used by `header.php` in `fetchActiveUsers()`:
```json
{
  "success": true,
  "data": {
    "count": 3,
    "users": [
      {
        "nim": "123456789",
        "name": "Demo User 1",
        "login_time": "2026-05-20 10:00:00",
        "last_activity": "2026-05-20 10:05:00",
        "idle_seconds": 12
      }
    ],
    "timestamp": "2026-05-20 10:05:12"
  }
}
```
> Source: `api/active_users.php:29-38` and `header.php:158-178` (JS consumption)

**Computing `idle_seconds` in Laravel:**
```php
$users->map(function ($session) {
    $session->idle_seconds = now()->diffInSeconds($session->last_activity);
    return $session;
});
```

## Error Response Shape
On exception, return:
```json
{ "success": false, "error": "Failed to fetch food data" }
```
Use Laravel's exception handler or a try/catch in the controller.

## Checklist
- [ ] `FoodResource` created — maps DB columns to display-name keys
- [ ] `Api/FoodController@search` returns exact JSON shape frontend expects
- [ ] `Api/UserController@activeUsers` returns exact JSON shape frontend expects
- [ ] `idle_seconds` computed from `last_activity` in activeUsers response
- [ ] Error responses return `{ success: false, error: "..." }`
