<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    public function index(Request $request)
    {
        $search = (string) $request->input('search', '');

        $foods = Food::query()
            ->when($search, fn($q) => $q->where('menu', 'like', "%{$search}%"))
            ->orderBy('menu')
            ->paginate(config('nutrition.items_per_page'))
            ->appends(['search' => $search]);

        return view('foods.index', compact('foods', 'search'));
    }
}
