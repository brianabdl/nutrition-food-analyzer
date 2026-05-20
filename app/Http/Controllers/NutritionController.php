<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Standard;
use App\Services\NutritionService;

class NutritionController extends Controller
{
    public function __construct(private NutritionService $nutritionService) {}

    public function show(string $name)
    {
        $name = urldecode($name);
        $food = Food::where('menu', $name)->firstOrFail();

        $standards = Standard::all();
        $result    = $this->nutritionService->buildComparisons($food, $standards);

        $comparisons    = $result['comparisons'];
        $totalNutrients = $result['totalNutrients'];
        $safeNutrients  = $result['safeNutrients'];
        $safetyPct      = $totalNutrients > 0
            ? round(($safeNutrients / $totalNutrients) * 100)
            : 0;

        return view('foods.nutrition', compact(
            'food', 'comparisons', 'safetyPct', 'totalNutrients', 'safeNutrients'
        ));
    }
}
