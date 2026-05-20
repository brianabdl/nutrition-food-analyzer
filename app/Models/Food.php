<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table    = 'foods';

    protected $fillable = [
        'menu', 'energy_kj', 'protein_g', 'fat_g', 'carbohydrates_g',
        'dietary_fiber_g', 'pufa_g', 'cholesterol_mg', 'vitamin_a_mg',
        'vitamin_e_mg', 'vitamin_b1_mg', 'vitamin_b2_mg', 'vitamin_b6_mg',
        'total_folic_acid_ug', 'vitamin_c_mg', 'sodium_mg', 'potassium_mg',
        'calcium_mg', 'magnesium_mg', 'phosphorus_mg', 'iron_mg', 'zinc_mg',
    ];

    public const NUTRIENT_MAP = [
        'Energy (kJ)'           => 'energy_kj',
        'Protein (g)'           => 'protein_g',
        'Fat (g)'               => 'fat_g',
        'Carbohydrates (g)'     => 'carbohydrates_g',
        'Dietary Fiber (g)'     => 'dietary_fiber_g',
        'PUFA (g)'              => 'pufa_g',
        'Cholesterol (mg)'      => 'cholesterol_mg',
        'Vitamin A (mg)'        => 'vitamin_a_mg',
        'Vitamin E (eq.) (mg)'  => 'vitamin_e_mg',
        'Vitamin B1 (mg)'       => 'vitamin_b1_mg',
        'Vitamin B2 (mg)'       => 'vitamin_b2_mg',
        'Vitamin B6 (mg)'       => 'vitamin_b6_mg',
        'Total Folic Acid (µg)' => 'total_folic_acid_ug',
        'Vitamin C (mg)'        => 'vitamin_c_mg',
        'Sodium (mg)'           => 'sodium_mg',
        'Potassium (mg)'        => 'potassium_mg',
        'Calcium (mg)'          => 'calcium_mg',
        'Magnesium (mg)'        => 'magnesium_mg',
        'Phosphorus (mg)'       => 'phosphorus_mg',
        'Iron (mg)'             => 'iron_mg',
        'Zinc (mg)'             => 'zinc_mg',
    ];

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) return $query;
        return $query->where('menu', 'like', "%{$term}%");
    }

    public static function formatName(string $name): string
    {
        // Capitalize first letter of each word, lowercase the rest
        return preg_replace_callback('/\b([a-z])([a-z]*)\b/i', function ($m) {
            $upper = ['di', 'dan', 'dan', 'atau', 'dengan', 'dalam', 'dari', 'ke', 'untuk', 'pada'];
            $word  = strtolower($m[0]);
            return in_array($word, $upper) ? $word : ucfirst($word);
        }, strtolower(trim($name)));
    }
}
