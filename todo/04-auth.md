# 04 — Authentication System

The current auth uses NIM (student ID number) + password, not email. Laravel's default auth assumes email. We need a custom guard.

## Steps

### 1. Custom Auth Guard
In `config/auth.php`, change the default provider to use `nim`:
```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model'  => App\Models\User::class,
    ],
],
```

In `app/Models/User.php`, override the username field used by the default `LoginController`/`AuthController`:
```php
// If using Auth::attempt(), pass nim manually — no override needed.
// Auth::attempt(['nim' => $request->nim, 'password' => $request->password])
```

### 2. `AuthController`
File: `app/Http/Controllers/AuthController.php`

**Login (replaces `login.php`):**
```
POST /login
Input: nim (required, numeric), password (required)
Logic:
  1. Validate input
  2. Auth::attempt(['nim' => $nim, 'password' => $password])
  3. On success: regenerate session, create UserSession record, redirect to intended or /
  4. On fail: back with error 'Invalid NIM or password'
```
> Source: `login.php:21-46`

**Register (replaces `register.php`):**
```
POST /register
Input: nim (required, numeric, unique:users), name (required), password (required, min:6, confirmed)
Logic:
  1. Validate input
  2. User::create([...])
  3. Redirect to /login with success message
```
> Source: `register.php:21-47`

**Logout (replaces `logout.php`):**
```
POST /logout
Logic:
  1. Mark UserSession as inactive (session_id = Session::getId())
  2. Auth::logout()
  3. Session::invalidate() + regenerateToken()
  4. Redirect to /login
```
> Source: `logout.php`

### 3. Redirect after login
In `app/Http/Middleware/Authenticate.php`, set the redirect URL:
```php
protected function redirectTo(Request $request): ?string
{
    return $request->expectsJson() ? null : route('login');
}
```

### 4. Session Activity Middleware
Create `app/Http/Middleware/UpdateSessionActivity.php`:
```
- Runs on every authenticated request
- Calls UserSession::where('session_id', Session::getId())->update(['last_activity' => now()])
- Equivalent to current session.php auto-update on line 109-114
```
Register in `app/Http/Kernel.php` under `$middlewareGroups['web']`.

### 5. Login/Register Views
See `06-views.md`. These pages are standalone (no app layout):
- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`

Both pages use the same HTML structure as current `login.php` and `register.php` (the login box UI).

### 6. Remove email from validation
The default Laravel `RegisteredUserController` (if using Breeze) validates email. Either:
- Don't use Breeze — write `AuthController` from scratch (recommended for this project)
- Or customize Breeze's generated controller

**Recommendation**: Write `AuthController` from scratch. The auth logic is simple (50 lines total) and avoids fighting Breeze's assumptions.

## Checklist
- [ ] `AuthController` created with `login`, `register`, `logout` methods
- [ ] `Auth::attempt()` uses `nim` field
- [ ] `UserSession` record created on login, deactivated on logout
- [ ] Session activity middleware created and registered
- [ ] Session expiry (30 min inactivity) handled — Laravel's built-in `session.lifetime` handles this
- [ ] Login redirects to intended URL (`redirect()->intended('/')`)
- [ ] Auth middleware protecting all non-auth routes
