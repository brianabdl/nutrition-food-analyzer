<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FoodResource;
use App\Models\Food;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    public function search(Request $request)
    {
        try {
            $search  = (string) $request->input('search', '');
            $sortDir = $request->input('sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';
            $sortBy  = $request->input('sort_by', 'menu');

            $allowedSorts = ['menu', 'energy_kj', 'protein_g', 'fat_g', 'carbohydrates_g'];
            if (!in_array($sortBy, $allowedSorts, true)) {
                $sortBy = 'menu';
            }

            $query = Food::query()
                ->when($search, fn($q) => $q->where('menu', 'like', "%{$search}%"));

            foreach (['energy_kj', 'protein_g', 'fat_g', 'carbohydrates_g'] as $col) {
                $min = $request->input("min_{$col}");
                $max = $request->input("max_{$col}");
                if (is_numeric($min)) $query->where($col, '>=', (float) $min);
                if (is_numeric($max)) $query->where($col, '<=', (float) $max);
            }

            $foods = $query->orderBy($sortBy, $sortDir)
                ->paginate(config('nutrition.items_per_page'));

            return response()->json([
                'success' => true,
                'data'    => [
                    'foods'      => FoodResource::collection($foods),
                    'pagination' => [
                        'currentPage'  => $foods->currentPage(),
                        'totalPages'   => $foods->lastPage(),
                        'totalCount'   => $foods->total(),
                        'itemsPerPage' => $foods->perPage(),
                        'showingStart' => $foods->firstItem() ?? 0,
                        'showingEnd'   => $foods->lastItem()  ?? 0,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => 'Failed to fetch food data'], 500);
        }
    }
}
