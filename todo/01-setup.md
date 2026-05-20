# 01 — Laravel Project Setup & Config

## Steps

### 1. Create `config/nutrition.php`
Laravel config file to replace the constants from `config/config.php`:
```php
return [
    'items_per_page'     => 10,
    'max_comparison'     => 5,
    'cache_duration'     => 3600,    // seconds
];
```
Usage in code: `config('nutrition.items_per_page')`

### 2. Session config
In `config/session.php` (already exists in Laravel), set:
```php
'lifetime'  => 30,       // matches 1800s in current config
'same_site' => 'lax',
'http_only' => true,
```

### 3. Timezone
In `config/app.php`:
```php
'timezone' => 'Asia/Jakarta',
```

### 4. Remove unused scaffolding
- Delete `resources/views/welcome.blade.php`
- Delete default auth stubs if not using Laravel Breeze/Fortify

### 5. Docker (optional — keep existing `docker-compose.yml`)
The current `docker/` folder and `docker-compose.yml` can stay as-is for MySQL.
Update `docker/apache-config.conf` to point to `public/` instead of project root.

## Checklist
- [ ] `config/nutrition.php` created
- [ ] Timezone set to Asia/Jakarta
- [ ] Session lifetime set to 30 minutes
