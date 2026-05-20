<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Standard;
use App\Services\NutritionService;
use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    public function __construct(private NutritionService $nutritionService) {}

    public function index(Request $request)
    {
        $raw   = $request->input('foods', '');
        $names = array_filter(array_map('urldecode', explode(',', $raw)));

        if (count($names) < 2) {
            return redirect()->route('foods.index')
                             ->with('error', 'Select at least 2 foods to compare.');
        }

        if (count($names) > config('nutrition.max_comparison')) {
            $names = array_slice($names, 0, config('nutrition.max_comparison'));
        }

        $foods     = Food::whereIn('menu', $names)->get();
        $standards = Standard::all()->keyBy('nutrisi');
        $insights  = $this->nutritionService->buildInsights($foods);

        // Build chart data: nutrient labels + one dataset per food
        $nutrientLabels = array_keys(Food::NUTRIENT_MAP);
        $datasets = $foods->map(function ($food) use ($nutrientLabels) {
            return [
                'label' => $food->menu,
                'data'  => array_map(fn($label) => $food->{Food::NUTRIENT_MAP[$label]}, $nutrientLabels),
            ];
        })->values()->all();

        $chartData = ['labels' => $nutrientLabels, 'datasets' => $datasets];

        return view('foods.comparison', compact('foods', 'standards', 'insights', 'chartData'));
    }
}
