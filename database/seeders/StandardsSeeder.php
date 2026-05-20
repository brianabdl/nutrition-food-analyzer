<?php

namespace Database\Seeders;

use App\Models\Standard;
use Illuminate\Database\Seeder;

class StandardsSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/standard-nutrition.csv');
        $file = fopen($path, 'r');
        $headers = fgetcsv($file);

        // Map header index by name
        $idx = array_flip($headers);

        $rows = [];
        while (($row = fgetcsv($file)) !== false) {
            $rows[] = [
                'nutrisi'           => $row[$idx['Nutrisi']],
                'minimum'           => ($row[$idx['Minimum']] ?? '') !== '' ? $row[$idx['Minimum']] : null,
                'maximum'           => ($row[$idx['Maximum']] ?? '') !== '' ? $row[$idx['Maximum']] : null,
                'rekomendasi_harian'=> $row[$idx['Rekomendasi Harian Anak (1-5 tahun)']] ?? null,
                'fungsi_zat'        => $row[$idx['Fungsi Zat']] ?? null,
                'dampak_kelebihan'  => $row[$idx['Dampak Kelebihan']] ?? null,
                'dampak_kekurangan' => $row[$idx['Dampak Kekurangan']] ?? null,
            ];
        }

        fclose($file);
        Standard::insertOrIgnore($rows);
    }
}
