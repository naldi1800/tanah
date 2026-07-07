<?php

namespace Database\Seeders;

use App\Models\JenisTanah;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JenisTanahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        JenisTanah::create([
            "jenis" => "Andosol",
            "ciri_ciri" => "Warna tanah hitam/coklat, tekstur gambur dan lembut, sangat subur, banyak menganduk bahan organik"
        ]);

        JenisTanah::create([
            "jenis" => "Latosal",
            "ciri_ciri" => "Berwarna merah, coklat/kekuningan, kesuburan sedang"
        ]);

        JenisTanah::create([
            "jenis" => "Mediteran",
            "ciri_ciri" => "Berwarna merah kecoklatan, tekstur lebih keras dibandingkan andosol, mudah kering saat musim panas"
        ]);
    }
}
