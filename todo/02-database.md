# 02 — Database Migrations & Seeders

## Migrations to Create

### 1. Modify default users migration
File: `database/migrations/xxxx_create_users_table.php`

The current `users` table has `nim` instead of `email`. Replace the default Laravel users migration:
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('nim', 50)->unique();
    $table->string('name');
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
});
```
> Note: No `email` column — auth is NIM-based. Remove `email_verified_at` too.

---

### 2. Create user_sessions migration
File: `database/migrations/xxxx_create_user_sessions_table.php`

```php
Schema::create('user_sessions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('nim', 50);
    $table->string('name');
    $table->string('session_id')->unique();
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamp('login_time')->useCurrent();
    $table->timestamp('last_activity')->useCurrent()->useCurrentOnUpdate();
    $table->boolean('is_active')->default(true);

    $table->index('session_id');
    $table->index('is_active');
    $table->index('user_id');
});
```
> **Do NOT** use the built-in Laravel `sessions` table — this is a custom tracking table, not PHP session storage.

---

### 3. Create foods migration
File: `database/migrations/xxxx_create_foods_table.php`

```php
Schema::create('foods', function (Blueprint $table) {
    $table->id();
    $table->string('menu')->unique();
    $table->decimal('energy_kj', 10, 2)->nullable();
    $table->decimal('protein_g', 10, 2)->nullable();
    $table->decimal('fat_g', 10, 2)->nullable();
    $table->decimal('carbohydrates_g', 10, 2)->nullable();
    $table->decimal('dietary_fiber_g', 10, 2)->nullable();
    $table->decimal('pufa_g', 10, 2)->nullable();
    $table->decimal('cholesterol_mg', 10, 2)->nullable();
    $table->decimal('vitamin_a_mg', 10, 2)->nullable();
    $table->decimal('vitamin_e_mg', 10, 2)->nullable();
    $table->decimal('vitamin_b1_mg', 10, 2)->nullable();
    $table->decimal('vitamin_b2_mg', 10, 2)->nullable();
    $table->decimal('vitamin_b6_mg', 10, 2)->nullable();
    $table->decimal('total_folic_acid_ug', 10, 2)->nullable();
    $table->decimal('vitamin_c_mg', 10, 2)->nullable();
    $table->decimal('sodium_mg', 10, 2)->nullable();
    $table->decimal('potassium_mg', 10, 2)->nullable();
    $table->decimal('calcium_mg', 10, 2)->nullable();
    $table->decimal('magnesium_mg', 10, 2)->nullable();
    $table->decimal('phosphorus_mg', 10, 2)->nullable();
    $table->decimal('iron_mg', 10, 2)->nullable();
    $table->decimal('zinc_mg', 10, 2)->nullable();
    $table->timestamps();

    $table->index('menu');
});
```

---

### 4. Create standards migration
File: `database/migrations/xxxx_create_standards_table.php`

```php
Schema::create('standards', function (Blueprint $table) {
    $table->id();
    $table->string('nutrisi')->unique();
    $table->decimal('minimum', 10, 2)->nullable();
    $table->decimal('maximum', 10, 2)->nullable();
    $table->text('rekomendasi_harian')->nullable();
    $table->text('fungsi_zat')->nullable();
    $table->text('dampak_kelebihan')->nullable();
    $table->text('dampak_kekurangan')->nullable();
    $table->timestamps();
});
```

---

## Seeders to Create

### 1. `DatabaseSeeder.php`
Call all seeders in order:
```php
$this->call([
    FoodsSeeder::class,
    StandardsSeeder::class,
    UsersSeeder::class,
]);
```

### 2. `FoodsSeeder`
File: `database/seeders/FoodsSeeder.php`

- Read `foods.csv` (copy it to `database/data/foods.csv`)
- Parse rows (columns 1–22, skip column 0 which is the row index)
- Use `Food::insertOrIgnore()` in chunks of 100 for performance
- Column mapping (CSV index → DB column):
  - `[1]` → `menu`
  - `[2]` → `energy_kj`
  - `[3]` → `protein_g`
  - `[4]` → `fat_g`
  - `[5]` → `carbohydrates_g`
  - `[6]` → `dietary_fiber_g`
  - `[7]` → `pufa_g`
  - `[8]` → `cholesterol_mg`
  - `[9]` → `vitamin_a_mg`
  - `[10]` → `vitamin_e_mg`
  - `[11]` → `vitamin_b1_mg`
  - `[12]` → `vitamin_b2_mg`
  - `[13]` → `vitamin_b6_mg`
  - `[14]` → `total_folic_acid_ug`
  - `[15]` → `vitamin_c_mg`
  - `[16]` → `sodium_mg`
  - `[17]` → `potassium_mg`
  - `[18]` → `calcium_mg`
  - `[19]` → `magnesium_mg`
  - `[20]` → `phosphorus_mg`
  - `[21]` → `iron_mg`
  - `[22]` → `zinc_mg`

### 3. `StandardsSeeder`
File: `database/seeders/StandardsSeeder.php`

- Read `standard-nutrition.csv` (copy to `database/data/standard-nutrition.csv`)
- Parse rows (columns 1–7, skip column 0)
- Column mapping:
  - `[1]` → `nutrisi`
  - `[2]` → `minimum`
  - `[3]` → `maximum`
  - `[4]` → `rekomendasi_harian`
  - `[5]` → `fungsi_zat`
  - `[6]` → `dampak_kelebihan`
  - `[7]` → `dampak_kekurangan`

### 4. `UsersSeeder`
File: `database/seeders/UsersSeeder.php`

Create the 3 demo users from `User::createDemoUsers()`:
```php
User::insertOrIgnore([
    ['nim' => '123456789', 'name' => 'Demo User 1',  'password' => bcrypt('password123')],
    ['nim' => '987654321', 'name' => 'Demo User 2',  'password' => bcrypt('password123')],
    ['nim' => '111222333', 'name' => 'Admin User',   'password' => bcrypt('admin123')],
]);
```

## Run Commands
```bash
php artisan migrate
php artisan db:seed
```

## Checklist
- [ ] Users migration modified (nim instead of email, no email_verified_at)
- [ ] UserSessions migration created
- [ ] Foods migration created
- [ ] Standards migration created
- [ ] CSV files copied to `database/data/`
- [ ] FoodsSeeder created
- [ ] StandardsSeeder created
- [ ] UsersSeeder created
- [ ] DatabaseSeeder updated
- [ ] `php artisan migrate --seed` runs without errors
