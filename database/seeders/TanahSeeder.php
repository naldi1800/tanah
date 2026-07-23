<?php

namespace Database\Seeders;

use App\Models\Tanah;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TanahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tanah::truncate();
        $csvFile = fopen(base_path('database/csv/tanah2.csv'), 'r');
        $header = fgetcsv($csvFile);
        if ($header !== false) {
            // Menghilangkan UTF-8 BOM jika ada
            $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
            // Menghilangkan spasi di awal dan akhir teks setiap kolom
            $header = array_map('trim', $header);
        }

        while ($row = fgetcsv($csvFile)) {
            $data = array_combine($header, $row);
            Tanah::create($data);
        }
        fclose($csvFile);
        
    }
}
