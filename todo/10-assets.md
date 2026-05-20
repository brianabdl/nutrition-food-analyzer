# 10 — CSS/JS Assets

## CSS

### `style.css` → `public/css/app.css`
The entire `style.css` file can be copied as-is to `public/css/app.css`.

In Blade layout, reference it with:
```blade
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
```

No changes to the CSS are needed for the migration.

---

## JavaScript Libraries (CDN — keep as-is)
These are loaded via CDN and don't need to change:
```html
<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<!-- Chart.js (only on nutrition/comparison pages) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
```

In the layout, load Chart.js conditionally using a `@stack`:
```blade
{{-- In layout head: --}}
@stack('head-scripts')

{{-- In nutrition/comparison views: --}}
@push('head-scripts')
<script src="https://cdnjs.cloudflare.com/.../chart.min.js"></script>
@endpush
```

---

## Inline JavaScript Changes

### AJAX URL Updates
Every hardcoded PHP file URL in JS must be replaced with a Laravel route.

| Old URL | New URL (in Blade) |
|---|---|
| `api/search_foods.php` | `{{ route('api.foods.search') }}` |
| `api/active_users.php` | `{{ route('api.active-users') }}` |
| `nutrition.php?food=` | `{{ url('/foods') }}/` + encodeURIComponent + `/nutrition` |
| `comparison.php?foods=` | `{{ route('comparison') }}?foods=` |
| `index.php` | `{{ route('foods.index') }}` |

**Pass routes to JS as a data block in the layout:**
```blade
<script>
    const APP_ROUTES = {
        foodsSearch:   "{{ route('api.foods.search') }}",
        activeUsers:   "{{ route('api.active-users') }}",
        comparison:    "{{ route('comparison') }}",
        nutritionBase: "{{ url('/foods') }}",
    };
    const MAX_COMPARISON_ITEMS = {{ config('nutrition.max_comparison') }};
</script>
```
Then in the JS files use `APP_ROUTES.foodsSearch` instead of hardcoded strings.

### CSRF Token for POST requests
Not needed for GET AJAX, but if any POST AJAX is added later:
```blade
<meta name="csrf-token" content="{{ csrf_token() }}">
```
```js
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
```

### `@json()` for server-side data
Replace inline PHP JSON encoding:
```php
// Old
const comparisons = <?php echo json_encode($comparisons); ?>;
// New
const comparisons = @json($comparisons);
```

---

## Checklist
- [ ] `style.css` copied to `public/css/app.css`
- [ ] Layout references `asset('css/app.css')`
- [ ] Chart.js loaded via `@push('head-scripts')` on nutrition/comparison views only
- [ ] `APP_ROUTES` JS object defined in layout with all route URLs
- [ ] All AJAX calls updated to use `APP_ROUTES.*` instead of hardcoded PHP filenames
- [ ] `json_encode()` replaced with `@json()` in Blade templates
