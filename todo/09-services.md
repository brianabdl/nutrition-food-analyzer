# 09 — Service Classes

Service classes are optional but help keep controllers thin. Extract logic that is complex or shared.

## Services to Create

### 1. `NutritionService`
File: `app/Services/NutritionService.php`

Extracts the comparison-building logic shared between `NutritionController` and `ComparisonController`.

**Method: `buildComparisons(Food $food, Collection $standards): array`**

Ports this logic from `nutrition.php:40-98`:
```php
public function buildComparisons(Food $food, Collection $standards): array
{
    $comparisons = [];
    $totalNutrients = 0;
    $safeNutrients = 0;

    foreach ($standards as $standard) {
        $nutrient  = $standard->nutrisi;
        $dbColumn  = Food::NUTRIENT_MAP[$nutrient] ?? null;
        $foodValue = $dbColumn ? $food->$dbColumn : null;

        $status = $this->determineStatus($foodValue, $standard);
        if ($status['in_range']) $safeNutrients++;

        $comparisons[] = [
            'nutrient'     => $nutrient,
            'food_value'   => $foodValue,
            'standard'     => $standard,
            'status'       => $status['status'],
            'status_text'  => $status['text'],
            'in_range'     => $status['in_range'],
        ];
        $totalNutrients++;
    }

    return compact('comparisons', 'totalNutrients', 'safeNutrients');
}

private function determineStatus($value, Standard $standard): array
{
    // Port logic from nutrition.php:53-97
    // Returns ['status' => 'normal|excess|deficiency|no-standard', 'text' => '...', 'in_range' => bool]
}
```

**Method: `buildInsights(array $foods): array`**

Ports energy/protein insight logic from `comparison.php:122-155`.

---

### 2. `FoodNameFormatter`
File: `app/Services/FoodNameFormatter.php` (or keep as static method on `Food` model)

The `formatFoodName()` method from `models/Food.php:43-94` is used in:
- `Food` model
- `comparison.php` (local function copy at line 68)
- Frontend JS (simplified version in `index.php:566-574`)

In Laravel, put it in one place. Options:
- Keep on `Food` model as `Food::formatName(string $name): string`
- Or extract to a dedicated service/helper

The JS version in `index.php:566-574` is a simpler approximation — it still needs to exist in the frontend. No change needed there.

---

## Alternative: Skip Services (simpler approach)
For a student project, it's fine to put all logic directly in controllers. Only extract to a service if:
- The same logic is used in 2+ controllers (the comparison logic is used in both `NutritionController` and `ComparisonController`)
- A controller method exceeds ~40 lines of business logic

## Checklist
- [ ] Decide: use NutritionService or inline logic in controllers
- [ ] `buildComparisons()` logic ported (from `nutrition.php:40-98`)
- [ ] `buildInsights()` logic ported (from `comparison.php:122-155`)
- [ ] `formatFoodName()` in one canonical location (`Food` model recommended)
