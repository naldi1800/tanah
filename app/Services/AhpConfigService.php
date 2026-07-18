<?php

namespace App\Services;

use App\Models\Tanah;
use Illuminate\Support\Collection;

/**
 * Service untuk mengelola konfigurasi AHP
 * Menangani: kriteria, alternatif, dan default pairwise matrix
 */
class AhpConfigService
{
    /**
     * Daftar kriteria tetap
     */
    protected const CRITERIA = [
        'Jenis Tanah',
        'pH Tanah',
        'Kelembapan',
        'Suhu',
        'Ketinggian',
    ];

    /**
     * Default pairwise comparison matrix
     * Nilai default dari AhpController.php
     */
    protected const DEFAULT_PAIRWISE_MATRIX = [
        [1, 5, 5, 7, 7],
        [1/5, 1, 3, 5, 5],
        [1/5, 1/3, 1, 3, 3],
        [1/7, 1/5, 1/3, 1, 2],
        [1/7, 1/5, 1/3, 1/2, 1],
    ];

    /**
     * Skala Saaty untuk perbandingan pairwise
     */
    protected const SAATY_SCALE = [
        9 => 'Mutlak lebih penting',
        7 => 'Sangat penting',
        5 => 'Lebih penting',
        3 => 'Sedikit lebih penting',
        1 => 'Sama penting',
        '1/3' => 'Sedikit kurang penting',
        '1/5' => 'Kurang penting',
        '1/7' => 'Sangat kurang penting',
        '1/9' => 'Mutlak kurang penting',
    ];

    /**
     * Pemetaan nama field Tanah ke kriteria
     */
    protected const FIELD_TO_CRITERIA = [
        'Jenis Tanah' => 'jenis_tanah_id',
        'pH Tanah' => 'PH_Tanah',
        'Kelembapan' => 'Kelembaban_Tanah',
        'Suhu' => 'Suhu_Tanah',
        'Ketinggian' => 'Ketinggian_Tanah',
    ];

    /**
     * Field yang diabaikan saat menampilkan kriteria
     */
    protected const IGNORED_FIELDS = [
        'id',
        'alamat',
        'created_at',
        'updated_at',
    ];

    /**
     * Get daftar kriteria
     */
    public function getCriteria(): array
    {
        return self::CRITERIA;
    }

    /**
     * Get default pairwise matrix
     */
    public function getDefaultPairwiseMatrix(): array
    {
        return self::DEFAULT_PAIRWISE_MATRIX;
    }

    /**
     * Get Saaty scale options
     */
    public function getSaatySaleOptions(): array
    {
        return self::SAATY_SCALE;
    }

    /**
     * Get daftar alternatif (nama jalan yang dikelompokkan)
     */
    public function getAlternatives(): Collection
    {
        return $this->getAlternativesWithAggregatedData()->pluck('street')->values();
    }

    /**
     * Get alternatif dengan data agregasi lengkap
     * Return format:
     * [
     *     [
     *         'street' => 'Jln Anggrek',
     *         'jenis_dominan' => 'Lempung',
     *         'avg_ph' => 6.77,
     *         'avg_kelembapan' => 80.33,
     *         'avg_suhu' => 28.00,
     *         'avg_ketinggian' => 112.33,
     *     ],
     *     ...
     * ]
     */
    public function getAlternativesWithAggregatedData(): Collection
    {
        $records = Tanah::with('jenisTanah')->get();
        $grouped = $records->groupBy(function (Tanah $tanah) {
            return $this->extractStreetName($tanah->Alamat);
        });

        return $grouped->map(function (Collection $group, string $street) {
            return [
                'street' => $street,
                'jenis_dominan' => $this->calculateModusJenis($group),
                'avg_ph' => $group->avg('PH_Tanah'),
                'avg_kelembapan' => $group->avg('Kelembaban_Tanah'),
                'avg_suhu' => $group->avg('Suhu_Tanah'),
                'avg_ketinggian' => $group->avg('Ketinggian_Tanah'),
            ];
        })->values();
    }

    /**
     * Hitung modus untuk jenis tanah dari group data
     */
    protected function calculateModusJenis(Collection $group): ?string
    {
        $plucked = $group->pluck('jenisTanah')->filter();

        if ($plucked->isEmpty()) {
            return null;
        }

        $countById = $plucked->pluck('id')->countBy();
        $dominantId = $countById->sortDesc()->keys()->first();

        return $plucked->firstWhere('id', $dominantId)?->jenis ?? null;
    }

    /**
     * Extract nama jalan dari alamat lengkap
     * 
     * Contoh:
     * "Jln. Anggrek No 10" -> "Jln. Anggrek"
     * "Jln. Melati 2" -> "Jln. Melati"
     */
    public function extractStreetName(string $alamat): string
    {
        // split by common 'No' patterns first
        $part = preg_split('/\s+No\.?\s*/i', $alamat, 2);
        $base = trim($part[0]);

        // remove trailing numeric or unit suffixes
        $base = preg_replace('/[\s,;:-]*\b(?:No\.?|no\.?|\d+|[IVXLC]+)\b.*$/i', '', $base);
        // remove any trailing standalone numbers
        $base = preg_replace('/\s+\d+$/', '', $base);
        $base = preg_replace('/\s+\d+\/\d+$/', '', $base);

        return trim($base);
    }

    /**
     * Get alternatif dengan index
     * Return format: [index => street_name]
     */
    public function getAlternativesWithIndex(): array
    {
        return $this->getAlternatives()->mapWithKeys(function (string $street, int $index) {
            return [$index => $street];
        })->toArray();
    }

    /**
     * Convert Saaty scale value ke numeric
     * '1/3' -> 0.333...
     * 5 -> 5
     */
    public function scaleToNumeric($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value) && strpos($value, '/') !== false) {
            [$numerator, $denominator] = explode('/', $value);
            return (float) $numerator / (float) $denominator;
        }

        return 1.0;
    }

    /**
     * Convert numeric value ke Saaty scale representation
     * 0.333 -> '1/3'
     * 5.0 -> 5
     */
    public function numericToScale(float $value): string
    {
        // Round to nearest valid scale
        if ($value >= 1) {
            $rounded = round($value);
            return in_array($rounded, [1, 3, 5, 7, 9]) ? (string) $rounded : '1';
        }

        // For reciprocals
        $reciprocal = 1 / $value;
        $rounded = round($reciprocal);
        
        if (in_array($rounded, [3, 5, 7, 9])) {
            return '1/' . $rounded;
        }

        return '1';
    }

    /**
     * Get kriteria index berdasarkan nama
     */
    public function getCriteriaIndex(string $criteriaName): ?int
    {
        $criteria = $this->getCriteria();
        return array_search($criteriaName, $criteria, true) !== false
            ? array_search($criteriaName, $criteria, true)
            : null;
    }

    /**
     * Get nama field dari kriteria
     */
    public function getFieldFromCriteria(string $criteriaName): ?string
    {
        return self::FIELD_TO_CRITERIA[$criteriaName] ?? null;
    }

    /**
     * Get kriteria dari nama field
     */
    public function getCriteriaFromField(string $fieldName): ?string
    {
        return array_search($fieldName, self::FIELD_TO_CRITERIA, true) ?: null;
    }
}
