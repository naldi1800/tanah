<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Service untuk menghitung skor kesesuaian lahan
 * Bertanggung jawab mengkonversi data mentah menjadi skor kesesuaian (0-100)
 * berdasarkan kebutuhan tanaman yang ditentukan
 */
class LandSuitabilityService
{
    /**
     * Konfigurasi kebutuhan tanaman
     * Rentang ideal untuk setiap kriteria
     */
    protected const PLANT_REQUIREMENTS = [
        'ph' => [
            'min' => 5.5,
            'max' => 7.0,
            'optimal' => 6.25, // Tengah rentang
        ],
        'humidity' => [
            'min' => 75,
            'max' => 90,
            'optimal' => 82.5, // Tengah rentang
        ],
        'temperature' => [
            'min' => 25,
            'max' => 35,
            'optimal' => 30, // Tengah rentang
        ],
    ];

    /**
     * Skor untuk jenis tanah (kategorikal)
     * Berdasarkan tingkat kesesuaian untuk tanaman
     */
    protected const SOIL_TYPE_SCORES = [
        'Andosol' => 100,
        'Latosol' => 85,
        'Mediteran' => 70,
        'Lempung' => 60,
        'Pasir' => 30,
        'Liat' => 40,
        'default' => 20,
    ];

    /**
     * Skor untuk drainase (kategorikal)
     * Berdasarkan tingkat kesesuaian untuk tanaman
     */
    protected const DRAINASE_SCORES = [
        'Baik' => 100,
        'Sedang' => 60,
        'Buruk' => 30,
        'default' => 20,
    ];

    protected ?AhpConfigService $configService = null;

    public function __construct(?AhpConfigService $configService = null)
    {
        $this->configService = $configService ?? new AhpConfigService();
    }

    protected function getConfigService(): AhpConfigService
    {
        return $this->configService ??= new AhpConfigService();
    }

    /**
     * Konversi data agregasi menjadi skor kesesuaian
     * 
     * @param Collection $aggregatedData Data dari AhpConfigService::getAlternativesWithAggregatedData()
     * @return Collection Data dengan skor kesesuaian untuk setiap kriteria
     */
    public function convertToSuitabilityScores(Collection $aggregatedData): Collection
    {
        return $aggregatedData->map(function (array $data) {
            return [
                'street' => $data['street'],
                'jenis_dominan' => $data['jenis_dominan'],
                'soil_suitability_score' => $this->soilSuitabilityScore($data['jenis_dominan']),
                'ph_suitability_score' => $this->phSuitabilityScore($data['avg_ph']),
                'humidity_suitability_score' => $this->humiditySuitabilityScore($data['avg_kelembapan']),
                'temperature_suitability_score' => $this->temperatureSuitabilityScore($data['avg_suhu']),
                'drainase_suitability_score' => $this->drainaseSuitabilityScore($data['drainase_dominan']),
                // Retain original data for reference
                'original' => [
                    'avg_ph' => $data['avg_ph'],
                    'avg_kelembapan' => $data['avg_kelembapan'],
                    'avg_suhu' => $data['avg_suhu'],
                    'drainase_dominan' => $data['drainase_dominan'],
                ],
            ];
        })->values();
    }

    /**
     * Hitung skor kesesuaian jenis tanah
     * 
     * @param string|null $soilType Nama jenis tanah
     * @return int Skor 0-100
     */
    public function soilSuitabilityScore(?string $soilType): int
    {
        if ($soilType === null) {
            return self::SOIL_TYPE_SCORES['default'];
        }

        return self::SOIL_TYPE_SCORES[$soilType] ?? self::SOIL_TYPE_SCORES['default'];
    }

    /**
     * Hitung skor kesesuaian pH
     * Menggunakan fungsi Gaussian (bell curve) untuk transisi halus
     * 
     * @param float $phValue Nilai pH
     * @return float Skor 0-100
     */
    public function phSuitabilityScore(float $phValue): float
    {
        $req = self::PLANT_REQUIREMENTS['ph'];
        $optimal = $req['optimal'];
        $range = $req['max'] - $req['min'];
        
        // Standar deviasi untuk kontrol lebar kurva (sekitar 1/3 dari rentang)
        $sigma = $range / 3;
        
        // Fungsi Gaussian: exp(-((x - optimal)^2) / (2 * sigma^2))
        $distance = abs($phValue - $optimal);
        $exponent = -pow($distance, 2) / (2 * pow($sigma, 2));
        $score = 100 * exp($exponent);
        
        return max(0, min(100, $score));
    }

    /**
     * Hitung skor kesesuaian kelembapan
     * Menggunakan fungsi trapezoidal untuk transisi halus
     * 
     * @param float $humidityValue Nilai kelembapan (%)
     * @return float Skor 0-100
     */
    public function humiditySuitabilityScore(float $humidityValue): float
    {
        $req = self::PLANT_REQUIREMENTS['humidity'];
        $min = $req['min'];
        $max = $req['max'];
        $optimal = $req['optimal'];
        
        // Fungsi trapezoidal
        if ($humidityValue >= $min && $humidityValue <= $max) {
            // Di dalam rentang ideal: gunakan Gaussian di sekitar optimal
            $range = $max - $min;
            $sigma = $range / 3;
            $distance = abs($humidityValue - $optimal);
            $exponent = -pow($distance, 2) / (2 * pow($sigma, 2));
            $score = 100 * exp($exponent);
        } elseif ($humidityValue < $min) {
            // Di bawah rentang: linear decay
            $distance = $min - $humidityValue;
            $score = 100 * max(0, 1 - $distance / 20); // 20% tolerance di bawah
        } else {
            // Di atas rentang: linear decay
            $distance = $humidityValue - $max;
            $score = 100 * max(0, 1 - $distance / 15); // 15% tolerance di atas
        }
        
        return max(0, min(100, $score));
    }

    /**
     * Hitung skor kesesuaian suhu
     * Menggunakan fungsi trapezoidal untuk transisi halus
     * 
     * @param float $temperatureValue Nilai suhu (°C)
     * @return float Skor 0-100
     */
    public function temperatureSuitabilityScore(float $temperatureValue): float
    {
        $req = self::PLANT_REQUIREMENTS['temperature'];
        $min = $req['min'];
        $max = $req['max'];
        $optimal = $req['optimal'];
        
        // Fungsi trapezoidal
        if ($temperatureValue >= $min && $temperatureValue <= $max) {
            // Di dalam rentang ideal: gunakan Gaussian di sekitar optimal
            $range = $max - $min;
            $sigma = $range / 3;
            $distance = abs($temperatureValue - $optimal);
            $exponent = -pow($distance, 2) / (2 * pow($sigma, 2));
            $score = 100 * exp($exponent);
        } elseif ($temperatureValue < $min) {
            // Di bawah rentang: linear decay
            $distance = $min - $temperatureValue;
            $score = 100 * max(0, 1 - $distance / 5); // 5°C tolerance di bawah
        } else {
            // Di atas rentang: linear decay
            $distance = $temperatureValue - $max;
            $score = 100 * max(0, 1 - $distance / 5); // 5°C tolerance di atas
        }
        
        return max(0, min(100, $score));
    }

    /**
     * Hitung skor kesesuaian drainase
     * 
     * @param string|null $drainase Nilai drainase (Baik, Sedang, Buruk)
     * @return int Skor 0-100
     */
    public function drainaseSuitabilityScore(?string $drainase): int
    {
        if ($drainase === null) {
            return self::DRAINASE_SCORES['default'];
        }

        return self::DRAINASE_SCORES[$drainase] ?? self::DRAINASE_SCORES['default'];
    }

    /**
     * Get data alternatif dengan skor kesesuaian lengkap
     * Ini adalah method utama yang digunakan oleh AHP
     * 
     * @return Collection Data alternatif dengan skor kesesuaian
     */
    public function getAlternativesWithSuitabilityScores(): Collection
    {
        $aggregatedData = $this->getConfigService()->getAlternativesWithAggregatedData();
        return $this->convertToSuitabilityScores($aggregatedData);
    }

    /**
     * Get skor kesesuaian untuk kriteria tertentu
     * Digunakan untuk membangun pairwise matrix
     * 
     * @param string $criterion Nama kriteria
     * @return array Array skor untuk setiap alternatif
     */
    public function getSuitabilityScoresByCriterion(string $criterion): array
    {
        $data = $this->getAlternativesWithSuitabilityScores();
        
        return $data->map(function ($item) use ($criterion) {
            return match ($criterion) {
                'Jenis Tanah' => $item['soil_suitability_score'],
                'pH Tanah' => $item['ph_suitability_score'],
                'Kelembapan' => $item['humidity_suitability_score'],
                'Suhu' => $item['temperature_suitability_score'],
                'Drainase' => $item['drainase_suitability_score'],
                default => 0,
            };
        })->toArray();
    }

    /**
     * Get konfigurasi kebutuhan tanaman
     * Berguna untuk debugging atau display di UI
     */
    public function getPlantRequirements(): array
    {
        return self::PLANT_REQUIREMENTS;
    }

    /**
     * Get skor jenis tanah
     * Berguna untuk debugging atau display di UI
     */
    public function getSoilTypeScores(): array
    {
        return self::SOIL_TYPE_SCORES;
    }

    /**
     * Get skor drainase
     * Berguna untuk debugging atau display di UI
     */
    public function getDrainaseScores(): array
    {
        return self::DRAINASE_SCORES;
    }
}
