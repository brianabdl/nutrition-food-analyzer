# 06 — Blade Views & Layout

## Layout

### `resources/views/layouts/app.blade.php`
The main layout replacing `includes/header.php` + `includes/footer.php`.

Structure:
```
<!DOCTYPE html>
<html>
<head>
  @yield('title')
  <link style.css>
  <link font-awesome>
  @stack('head-scripts')   ← Chart.js loaded here per-page
</head>
<body>
  @include('partials.header')
  @yield('content')
  @include('partials.footer')
  @stack('scripts')        ← page-level JS goes here
</body>
</html>
```

**Pass to layout via `@section`:**
- `title` — page title
- `header_title`, `header_subtitle`, `header_icon` — header config vars
- Use Blade `@props` or pass via controller

---

### `resources/views/partials/header.blade.php`
Port of `includes/header.php`:
- Show user info block if `auth()->check()`
- Show back link if `$showBackLink` is set
- Show active users widget if `$showActiveUsers` is set
- Active users modal + JS polling script (keep jQuery AJAX — see `10-assets.md`)
- AJAX calls should use `route('api.active-users')` instead of hardcoded URL

---

### `resources/views/partials/footer.blade.php`
Port of `includes/footer.php`:
- Kaggle data attribution link
- About Team link (use `route('about')`)

---

## Auth Views (standalone — no app layout)

### `resources/views/auth/login.blade.php`
Port of `login.php` HTML section. Uses `action="{{ route('login') }}"`.
Show `$errors->first()` instead of PHP `$error` variable.
Show success message from session flash: `session('success')`.

### `resources/views/auth/register.blade.php`
Port of `register.php` HTML section. Uses `action="{{ route('register') }}"`.
Same error/success handling as login.

---

## App Views (use `layouts/app.blade.php`)

### `resources/views/foods/index.blade.php`
Port of `index.php` HTML section (lines 42-249):
- Search form → `route('foods.index')` with GET
- Food table with checkboxes
- Pagination: use `$foods->links()` for Laravel pagination, but since AJAX updates it dynamically, render initial pagination server-side and replace via JS
- Comparison toolbar
- Inline JS: port the entire `<script>` block from `index.php:252-598`
  - Replace `api/search_foods.php` URL with `{{ route('api.foods.search') }}`
  - Replace `comparison.php?foods=` with `{{ route('comparison') }}?foods=`

---

### `resources/views/foods/nutrition.blade.php`
Port of `nutrition.php` HTML section (lines 112-405):
- Summary cards (total, safe range, safety %)
- Chart canvas (`#nutritionChart`)
- Detailed comparison table
- Nutrient info cards (first 6 with details)
- Inline JS: port Chart.js setup + tooltip JS from `nutrition.php:307-403`
  - Data passed as: `const comparisons = @json($comparisons);`
  - `const food = @json($food);`

---

### `resources/views/foods/comparison.blade.php`
Port of `comparison.php` HTML section (lines 158-411):
- Selected foods summary cards
- Multi-food chart canvas (`#multiComparisonChart`)
- Detailed multi-column comparison table
- Insights/recommendations section
- Inline JS: port Chart.js setup from `comparison.php:339-409`
  - `const chartData = @json($chartData);` (pre-build in controller)

---

### `resources/views/profile.blade.php`
Port of `profile.php` HTML section:
- Display `auth()->user()->name`, `nim`
- Session info (last activity from UserSession)
- Logout button → `<form method="POST" action="{{ route('logout') }}">@csrf`

> Note: `profile.php:69` references `$currentUser['email']` and `$currentUser['created_at']` — these don't exist in the current `getCurrentUser()`. In Laravel, `auth()->user()` returns the full User model so `created_at` will be available. Remove the email field display.

---

### `resources/views/about.blade.php`
Port of `about.php` HTML section. Static content — no dynamic data needed.

---

## Blade Tips for This Project

**Replace PHP session/auth helpers:**
```blade
{{-- instead of getCurrentUser() --}}
{{ auth()->user()->name }}
{{ auth()->user()->nim }}

{{-- instead of isLoggedIn() --}}
@auth ... @endauth

{{-- CSRF for forms --}}
@csrf

{{-- Route URLs --}}
{{ route('foods.index') }}
{{ route('nutrition.show', urlencode($food->menu)) }}
```

**XSS — use `{{ }}` (auto-escaped) instead of `<?php echo htmlspecialchars(...) ?>`**

**JSON in JS:**
```blade
const data = @json($variable);   {{-- safe, auto-escapes --}}
```

---

## Checklist
- [ ] `layouts/app.blade.php` created
- [ ] `partials/header.blade.php` created (with active users modal)
- [ ] `partials/footer.blade.php` created
- [ ] `auth/login.blade.php` created
- [ ] `auth/register.blade.php` created
- [ ] `foods/index.blade.php` created (table + search + pagination + JS)
- [ ] `foods/nutrition.blade.php` created (chart + comparison table + JS)
- [ ] `foods/comparison.blade.php` created (multi-chart + table + insights + JS)
- [ ] `profile.blade.php` created
- [ ] `about.blade.php` created
- [ ] All hardcoded URLs replaced with `route()` helpers
- [ ] Email display removed from profile view
