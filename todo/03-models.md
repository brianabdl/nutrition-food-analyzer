# 03 — Eloquent Models

## Models to Create

### 1. `app/Models/User.php`
Extend Laravel's default `User` model. Key changes:
- Replace `email` with `nim` as the unique identifier
- Set `$fillable` to `['nim', 'name', 'password']`
- Override `getAuthIdentifierName()` and `getAuthPassword()` if using custom guard
- Remove `email`, `email_verified_at` from fillable/casts
- Add `hasMany(UserSession::class)` relationship

```php
protected $fillable = ['nim', 'name', 'password'];
protected $hidden   = ['password', 'remember_token'];
```

---

### 2. `app/Models/UserSession.php`
```php
protected $fillable = [
    'user_id', 'nim', 'name', 'session_id',
    'ip_address', 'user_agent', 'is_active',
];

// Cast last_activity and login_time as datetime
protected $casts = [
    'login_time'    => 'datetime',
    'last_activity' => 'datetime',
    'is_active'     => 'boolean',
];

public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

**Scopes to add** (replaces logic in `User.php` model):
```php
// Active users active in last 30 minutes
public function scopeActive(Builder $query): Builder
{
    return $query->where('is_active', true)
                 ->where('last_activity', '>=', now()->subMinutes(30));
}
```

---

### 3. `app/Models/Food.php`
```php
protected $fillable = [
    'menu', 'energy_kj', 'protein_g', 'fat_g', 'carbohydrates_g',
    'dietary_fiber_g', 'pufa_g', 'cholesterol_mg', 'vitamin_a_mg',
    'vitamin_e_mg', 'vitamin_b1_mg', 'vitamin_b2_mg', 'vitamin_b6_mg',
    'total_folic_acid_ug', 'vitamin_c_mg', 'sodium_mg', 'potassium_mg',
    'calcium_mg', 'magnesium_mg', 'phosphorus_mg', 'iron_mg', 'zinc_mg',
];
```

**Nutrient mapping** (move from `Food.php` model to a constant or service):
```php
// Maps display name → db column
public const NUTRIENT_MAP = [
    'Energy (kJ)'          => 'energy_kj',
    'Protein (g)'          => 'protein_g',
    'Fat (g)'              => 'fat_g',
    'Carbohydrates (g)'    => 'carbohydrates_g',
    'Dietary Fiber (g)'    => 'dietary_fiber_g',
    'PUFA (g)'             => 'pufa_g',
    'Cholesterol (mg)'     => 'cholesterol_mg',
    'Vitamin A (mg)'       => 'vitamin_a_mg',
    'Vitamin E (eq.) (mg)' => 'vitamin_e_mg',
    'Vitamin B1 (mg)'      => 'vitamin_b1_mg',
    'Vitamin B2 (mg)'      => 'vitamin_b2_mg',
    'Vitamin B6 (mg)'      => 'vitamin_b6_mg',
    'Total Folic Acid (µg)'=> 'total_folic_acid_ug',
    'Vitamin C (mg)'       => 'vitamin_c_mg',
    'Sodium (mg)'          => 'sodium_mg',
    'Potassium (mg)'       => 'potassium_mg',
    'Calcium (mg)'         => 'calcium_mg',
    'Magnesium (mg)'       => 'magnesium_mg',
    'Phosphorus (mg)'      => 'phosphorus_mg',
    'Iron (mg)'            => 'iron_mg',
    'Zinc (mg)'            => 'zinc_mg',
];
```

**Method to keep on the model** (or move to FoodService):
- `formatFoodName(string $name): string` — the regex-based formatting from `models/Food.php:43`

**Scopes to add**:
```php
public function scopeSearch(Builder $query, string $term): Builder
{
    return $query->where('menu', 'like', "%{$term}%");
}
```

---

### 4. `app/Models/Standard.php`
```php
protected $fillable = [
    'nutrisi', 'minimum', 'maximum',
    'rekomendasi_harian', 'fungsi_zat',
    'dampak_kelebihan', 'dampak_kekurangan',
];
```

No relationships needed. Accessed mostly via `Standard::all()` cached.

---

## Notes on Caching
The current project uses a manual file cache (`CACHE_PATH/*.cache`).
In Laravel, replace with `Cache::remember()`:
```php
// Example in FoodService
return Cache::remember("foods_all_{$page}_{$search}", 3600, fn() => ...);
```
Uses whatever cache driver is configured in `.env` (`CACHE_DRIVER=file` works as drop-in replacement).

## Checklist
- [ ] `User` model updated (nim field, no email)
- [ ] `UserSession` model created with `scopeActive`
- [ ] `Food` model created with `NUTRIENT_MAP` constant and `scopeSearch`
- [ ] `Standard` model created
- [ ] `formatFoodName()` method ported to `Food` model or `FoodService`
