<?php

namespace App\Http\Resources;

use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FoodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = ['Menu' => $this->menu];

        foreach (Food::NUTRIENT_MAP as $displayName => $column) {
            $data[$displayName] = $this->$column;
        }

        return $data;
    }
}
