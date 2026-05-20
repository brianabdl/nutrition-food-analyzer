<?php

namespace App\Services;

use App\Models\Food;
use App\Models\Standard;
use Illuminate\Support\Collection;

class NutritionService
{
    public function buildComparisons(Food $food, Collection $standards): array
    {
        $comparisons    = [];
        $totalNutrients = 0;
        $safeNutrients  = 0;

        foreach ($standards as $standard) {
            $nutrient  = $standard->nutrisi;
            $dbColumn  = Food::NUTRIENT_MAP[$nutrient] ?? null;
            $foodValue = $dbColumn ? $food->$dbColumn : null;

            $status = $this->determineStatus($foodValue, $standard);

            if ($status['in_range']) {
                $safeNutrients++;
            }

            $comparisons[] = [
                'nutrient'    => $nutrient,
                'food_value'  => $foodValue,
                'standard'    => $standard,
                'status'      => $status['status'],
                'status_text' => $status['text'],
                'in_range'    => $status['in_range'],
            ];
            $totalNutrients++;
        }

        return compact('comparisons', 'totalNutrients', 'safeNutrients');
    }

    private function determineStatus(mixed $value, Standard $standard): array
    {
        $min = $standard->minimum !== null ? (float) $standard->minimum : null;
        $max = $standard->maximum !== null ? (float) $standard->maximum : null;

        if ($min === null && $max === null) {
            return ['status' => 'no-standard', 'text' => 'No Standard', 'in_range' => false];
        }

        if ($value === null) {
            return ['status' => 'no-data', 'text' => 'No Data', 'in_range' => false];
        }

        $val = (float) $value;

        if ($max !== null && $val > $max) {
            return ['status' => 'excess', 'text' => 'Excess', 'in_range' => false];
        }

        if ($min !== null && $val < $min) {
            return ['status' => 'deficiency', 'text' => 'Deficiency', 'in_range' => false];
        }

        return ['status' => 'normal', 'text' => 'Normal', 'in_range' => true];
    }

    public function buildInsights(Collection $foods): array
    {
        $insights = [];

        $energyValues = $foods->map(fn($f) => (float) ($f->energy_kj ?? 0));
        $maxEnergy    = $energyValues->max();
        $minEnergy    = $energyValues->min();

        $highEnergyFood = $foods->firstWhere('energy_kj', $maxEnergy);
        $lowEnergyFood  = $foods->firstWhere('energy_kj', $minEnergy);

        if ($highEnergyFood && $highEnergyFood->menu !== ($lowEnergyFood->menu ?? '')) {
            $insights[] = [
                'type'    => 'energy',
                'message' => "{$highEnergyFood->menu} has the highest energy at {$maxEnergy} kJ, while {$lowEnergyFood->menu} has the lowest at {$minEnergy} kJ.",
            ];
        }

        $proteinValues  = $foods->map(fn($f) => (float) ($f->protein_g ?? 0));
        $maxProtein     = $proteinValues->max();
        $highProtFood   = $foods->firstWhere('protein_g', $maxProtein);

        if ($highProtFood) {
            $insights[] = [
                'type'    => 'protein',
                'message' => "{$highProtFood->menu} is the highest protein source at {$maxProtein} g per serving.",
            ];
        }

        return $insights;
    }
}
