<?php

namespace Database\Seeders;

use App\Models\Food;
use Illuminate\Database\Seeder;

class FoodsSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/foods.csv');
        $file = fopen($path, 'r');
        fgetcsv($file); // skip header

        $chunk = [];
        while (($row = fgetcsv($file)) !== false) {
            $chunk[] = [
                'menu'                => $row[1],
                'energy_kj'           => $row[2] !== '' ? $row[2] : null,
                'protein_g'           => $row[3] !== '' ? $row[3] : null,
                'fat_g'               => $row[4] !== '' ? $row[4] : null,
                'carbohydrates_g'     => $row[5] !== '' ? $row[5] : null,
                'dietary_fiber_g'     => $row[6] !== '' ? $row[6] : null,
                'pufa_g'              => $row[7] !== '' ? $row[7] : null,
                'cholesterol_mg'      => $row[8] !== '' ? $row[8] : null,
                'vitamin_a_mg'        => $row[9] !== '' ? $row[9] : null,
                'vitamin_e_mg'        => $row[10] !== '' ? $row[10] : null,
                'vitamin_b1_mg'       => $row[11] !== '' ? $row[11] : null,
                'vitamin_b2_mg'       => $row[12] !== '' ? $row[12] : null,
                'vitamin_b6_mg'       => $row[13] !== '' ? $row[13] : null,
                'total_folic_acid_ug' => $row[14] !== '' ? $row[14] : null,
                'vitamin_c_mg'        => $row[15] !== '' ? $row[15] : null,
                'sodium_mg'           => $row[16] !== '' ? $row[16] : null,
                'potassium_mg'        => $row[17] !== '' ? $row[17] : null,
                'calcium_mg'          => $row[18] !== '' ? $row[18] : null,
                'magnesium_mg'        => $row[19] !== '' ? $row[19] : null,
                'phosphorus_mg'       => $row[20] !== '' ? $row[20] : null,
                'iron_mg'             => $row[21] !== '' ? $row[21] : null,
                'zinc_mg'             => $row[22] !== '' ? $row[22] : null,
            ];

            if (count($chunk) >= 100) {
                Food::insertOrIgnore($chunk);
                $chunk = [];
            }
        }

        if ($chunk) {
            Food::insertOrIgnore($chunk);
        }

        fclose($file);
    }
}
