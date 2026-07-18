<?php

namespace App\Services;

use App\Models\Tanah;
use App\Models\JenisTanah;
use Illuminate\Support\Collection;

class AhpRecommendationService
{
    protected AhpService $ahpService;
    protected Collection $records;
    protected array $scores = [];
    protected array $criteriaWeights = [];
    protected ?AhpConfigService $configService = null;
    protected ?LandSuitabilityService $suitabilityService = null;

    public function __construct(AhpService $ahpService, ?AhpConfigService $configService = null, ?LandSuitabilityService $suitabilityService = null)
    {
        $this->ahpService = $ahpService;
        $this->configService = $configService ?? new AhpConfigService();
        $this->suitabilityService = $suitabilityService ?? new LandSuitabilityService($this->configService);
        $this->criteriaWeights = $this->resolveWeights();
    }

    protected function getConfigService(): AhpConfigService
    {
        return $this->configService ??= new AhpConfigService();
    }

    protected function getSuitabilityService(): LandSuitabilityService
    {
        return $this->suitabilityService ??= new LandSuitabilityService($this->getConfigService());
    }

    public function resolveWeights(): array
    {
        $weights = $this->ahpService->weights();
        $criteria = $this->ahpService->report()['criteria'];

        return array_combine($criteria, $weights) ?: [];
    }

    public function loadRecords(): self
    {
        $this->records = Tanah::with('jenisTanah')->get();

        return $this;
    }

    public function groupedByStreet(): Collection
    {
        return $this->records->groupBy(function (Tanah $tanah) {
            return $this->getConfigService()->extractStreetName($tanah->Alamat);
        });
    }

    protected function streetName(string $alamat): string
    {
        return $this->getConfigService()->extractStreetName($alamat);
    }

    public function streetStatistics(): Collection
    {
        // Gunakan data agregasi dari AhpConfigService untuk konsistensi
        $aggregatedData = $this->getConfigService()->getAlternativesWithAggregatedData();

        // Tambahkan count untuk backward compatibility
        return $aggregatedData->map(function (array $data) {
            return array_merge($data, [
                'count' => $this->records->filter(function (Tanah $tanah) use ($data) {
                    return $this->getConfigService()->extractStreetName($tanah->Alamat) === $data['street'];
                })->count(),
            ]);
        })->values();
    }

    protected function calcDominantJenis(Collection $group): ?array
    {
        $plucked = $group->pluck('jenisTanah')->filter();

        if ($plucked->isEmpty()) {
            return null;
        }

        $countById = $plucked->pluck('id')->countBy();
        $dominantId = $countById->sortDesc()->keys()->first();

        $jenisName = $plucked->firstWhere('id', $dominantId)?->jenis ?? null;

        return ['id' => $dominantId, 'jenis' => $jenisName];
    }

    public function jenisScore(string $jenis): int
    {
        // Backwards-compatible: if $jenis is actually an array/id, handle accordingly
        if (is_array($jenis)) {
            $id = $jenis['id'] ?? null;
            $jenis = $jenis['jenis'] ?? '';
        } elseif (is_int($jenis) || ctype_digit((string) $jenis)) {
            $id = (int) $jenis;
            $jenis = '';
        } else {
            $id = null;
        }

        // Prefer using id-based preference: id 1 > 2 > 3 (approx 50:30:20)
        if ($id !== null) {
            return match ($id) {
                1 => 5,
                2 => 3,
                3 => 2,
                default => 1,
            };
        }

        // Fallback to name-based mapping
        return match (trim((string) $jenis)) {
            'Andosol'   => 5,
            'Latosol'   => 4,
            'Mediteran' => 3,
            default     => 1,
        };
    }

    public function phScore(float $value): int
    {
        return match (true) {
            $value >= 6.0 && $value <= 7.0 => 5,
            ($value >= 5.5 && $value <= 5.9) || ($value >= 7.1 && $value <= 7.5) => 4,
            ($value >= 5.0 && $value <= 5.4) || ($value >= 7.6 && $value <= 8.0) => 3,
            $value >= 4.5 && $value <= 4.9 => 2,
            default => 1,
        };
    }

    public function kelembapanScore(float $value): int
    {
        return match (true) {
            $value >= 60 && $value <= 70 => 5,
            ($value >= 55 && $value <= 59) || ($value >= 71 && $value <= 75) => 4,
            ($value >= 50 && $value <= 54) || ($value >= 76 && $value <= 80) => 3,
            $value >= 45 && $value <= 49 => 2,
            default => 1,
        };
    }

    public function suhuScore(float $value): int
    {
        return match (true) {
            $value >= 25 && $value <= 30 => 5,
            ($value >= 23 && $value <= 24) || ($value >= 31 && $value <= 32) => 4,
            ($value >= 21 && $value <= 22) || ($value >= 33 && $value <= 34) => 3,
            $value >= 19 && $value <= 20 => 2,
            default => 1,
        };
    }

    public function ketinggianScore(float $value): int
    {
        return match (true) {
            $value >= 700 && $value <= 2000 => 5,
            $value >= 500 && $value <= 699 => 4,
            $value >= 300 && $value <= 499 => 3,
            $value >= 100 && $value <= 299 => 2,
            default => 1,
        };
    }

    public function evaluateStreet(array $stats): array
    {
        $jenisId = $stats['jenis_dominan_id'] ?? null;
        $jenisName = $stats['jenis_dominan'] ?? null;
        $jenisScore = $jenisId !== null ? $this->jenisScore($jenisId) : $this->jenisScore($jenisName ?: '');
        $phScore = $this->phScore((float) $stats['avg_ph']);
        $kelembapanScore = $this->kelembapanScore((float) $stats['avg_kelembapan']);
        $suhuScore = $this->suhuScore((float) $stats['avg_suhu']);
        $ketinggianScore = $this->ketinggianScore((float) $stats['avg_ketinggian']);

        $score = (
            ($this->criteriaWeights['Jenis Tanah'] ?? 0.0) * $jenisScore +
            ($this->criteriaWeights['pH Tanah'] ?? 0.0) * $phScore +
            ($this->criteriaWeights['Kelembapan'] ?? 0.0) * $kelembapanScore +
            ($this->criteriaWeights['Suhu'] ?? 0.0) * $suhuScore +
            ($this->criteriaWeights['Ketinggian'] ?? 0.0) * $ketinggianScore
        );

        return array_merge($stats, [
            'jenis_score' => $jenisScore,
            'ph_score' => $phScore,
            'kelembapan_score' => $kelembapanScore,
            'suhu_score' => $suhuScore,
            'ketinggian_score' => $ketinggianScore,
            'total_score' => round($score, 4),
        ]);
    }

    public function evaluatedStreets(): Collection
    {
        return $this->streetStatistics()
            ->map(fn(array $stats) => $this->evaluateStreet($stats))
            ->sortByDesc('total_score')
            ->values();
    }

    public function recommendationReport(): array
    {
        // build per-criterion AHP reports comparing alternatives (streets)
        $streets = $this->streetStatistics()->values();

        $perCriterionReports = [];

        // prepare alternative labels and values per criterion
        foreach (array_keys($this->criteriaWeights) as $criterion) {
            $labels = $streets->pluck('street')->toArray();
            $values = $streets->map(function ($s) use ($criterion) {
                return match ($criterion) {
                    'Jenis Tanah' => $this->jenisScore($s['jenis_dominan'] ?? ''),
                    'pH Tanah' => $s['avg_ph'],
                    'Kelembapan' => $s['avg_kelembapan'],
                    'Suhu' => $s['avg_suhu'],
                    'Ketinggian' => $s['avg_ketinggian'],
                    default => 0,
                };
            })->toArray();

            // build pairwise matrix for this criterion
            $matrix = $this->buildPairwiseMatrix($values);

            // compute AHP metrics using AhpService
            $criteriaNames = $labels; // alternatives act as criteria here
            $ahp = new \App\Services\AhpService($criteriaNames, $matrix, $this->lookupRI(count($matrix)));
            $report = $ahp->report();

            $perCriterionReports[$criterion] = [
                'labels' => $labels,
                'values' => $values,
                'matrix' => $matrix,
                'raw_matrix' => $this->buildRawFromNumeric($matrix),
                'report' => $report,
            ];
        }

        // compute final AHP aggregation: for each alternative (street), sum over criteria
        $finalScores = [];
        $labels = $streets->pluck('street')->toArray();

        foreach ($labels as $idx => $label) {
            $finalScores[$label] = ['street' => $label, 'score' => 0.0, 'contributions' => []];
        }

        foreach ($perCriterionReports as $criterion => $c) {
            $critWeight = $this->criteriaWeights[$criterion] ?? 0.0;
            $altPriorities = $c['report']['priorityVector'] ?? [];

            foreach ($labels as $i => $label) {
                $altPriority = $altPriorities[$i] ?? 0.0;
                $contrib = $critWeight * $altPriority;
                $finalScores[$label]['score'] += $contrib;
                $finalScores[$label]['contributions'][$criterion] = $contrib;
            }
        }

        // sort final scores desc and convert to values
        usort($finalScores, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return [
            'criteria_weights' => $this->criteriaWeights,
            'street_statistics' => $streets,
            'evaluated_streets' => $this->evaluatedStreets(),
            'per_criterion' => $perCriterionReports,
            'ahp_final_ranking' => $finalScores,
        ];
    }

    /**
     * Build a pairwise comparison matrix from numeric values.
     * Values higher means stronger preference.
     * We'll map ratios to AHP scale 1..9 roughly.
     */
    protected function buildPairwiseMatrix(array $values): array
    {
        $n = count($values);
        $matrix = array_fill(0, $n, array_fill(0, $n, 1.0));

        // Removed bias - now using suitability scores directly without artificial preference
        // The scores themselves reflect the actual suitability of each alternative

        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                if ($i === $j) {
                    $matrix[$i][$j] = 1.0;
                    continue;
                }

                $vi = $values[$i];
                $vj = $values[$j];

                // ratio - handle zero values
                if (abs($vj) < 0.0001) {
                    // If both are zero or very small, treat as equal
                    $ratio = abs($vi) < 0.0001 ? 1.0 : 9.0;
                } else {
                    $ratio = $vi / $vj;
                }

                // map ratio to AHP scale 1/9..9 using log scale
                $mapped = $this->ratioToAHP($ratio);

                $matrix[$i][$j] = $mapped;
                $matrix[$j][$i] = $mapped !== 0 ? 1.0 / $mapped : 1.0;
            }
        }

        return $matrix;
    }

    protected function ratioToAHP(float $ratio): float
    {
        if ($ratio == 1.0) return 1.0;

        // Handle zero or very small ratios
        if ($ratio == 0.0 || abs($ratio) < 0.0001) {
            return 1.0; // Treat as equal preference
        }

        // Handle negative ratios (shouldn't happen but protect anyway)
        if ($ratio < 0) {
            return 1.0;
        }

        // Use log2 to compress the ratio, then map to 1..9
        $sign = $ratio > 1 ? 1 : -1;
        $r = $ratio > 1 ? $ratio : 1 / max($ratio, 0.0001); // Prevent division by zero

        // Additional safety for log calculation
        if ($r <= 0) {
            return 1.0;
        }

        try {
            $scale = log($r, 2); // how many doublings
        } catch (\Exception $e) {
            return 1.0; // Fallback on any log error
        }

        // each step ~1.5 on AHP scale, cap between 1 and 9
        $value = 1 + min(8, round($scale * 2));

        return max(1.0, min(9.0, $value));
    }

    /**
     * Build a simple raw/fractional representation from numeric AHP matrix.
     * e.g. 6.0 -> '6', 0.1667 -> '1/6'
     */
    protected function buildRawFromNumeric(array $matrix): array
    {
        $n = count($matrix);
        $raw = array_fill(0, $n, array_fill(0, $n, '1'));

        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $val = $matrix[$i][$j] ?? 1.0;
                if ($i === $j) {
                    $raw[$i][$j] = '1';
                    continue;
                }
                if ($val >= 1.0) {
                    $raw[$i][$j] = (string) (int) round($val);
                } else {
                    $recip = $val != 0 ? (int) round(1.0 / $val) : 1;
                    $raw[$i][$j] = '1/' . $recip;
                }
            }
        }

        return $raw;
    }

    protected function lookupRI(int $n): float
    {
        // standard Random Index table for n = 1..10
        $ri = [1 => 0.00, 2 => 0.00, 3 => 0.58, 4 => 0.90, 5 => 1.12, 6 => 1.24, 7 => 1.32, 8 => 1.41, 9 => 1.45, 10 => 1.49];

        return $ri[$n] ?? 1.49;
    }
    /**
     * Menghasilkan seluruh pairwise matrix default alternatif
     * berdasarkan skor kesesuaian lahan (bukan nilai mentah).
     *
     * Return:
     * [
     *     'Jenis Tanah' => [...matrix...],
     *     'pH Tanah' => [...matrix...],
     *     ...
     * ]
     */
    public function getDefaultAlternativeMatrices(): array
    {
        if ($this->records->isEmpty()) {
            $this->loadRecords();
        }

        // Gunakan skor kesesuaian dari LandSuitabilityService
        $suitabilityData = $this->getSuitabilityService()->getAlternativesWithSuitabilityScores();

        $matrices = [];

        foreach (array_keys($this->criteriaWeights) as $criterion) {
            $values = $suitabilityData->map(function ($item) use ($criterion) {
                return match ($criterion) {
                    'Jenis Tanah' => $item['soil_suitability_score'],
                    'pH Tanah' => $item['ph_suitability_score'],
                    'Kelembapan' => $item['humidity_suitability_score'],
                    'Suhu' => $item['temperature_suitability_score'],
                    'Ketinggian' => $item['altitude_suitability_score'],
                    default => 0,
                };
            })->toArray();

            $matrices[$criterion] = $this->buildPairwiseMatrix($values);
        }

        return $matrices;
    }

    /**
     * Mengambil daftar alternatif (nama jalan)
     */
    public function getAlternativeLabels(): array
    {
        if ($this->records->isEmpty()) {
            $this->loadRecords();
        }

        return $this->getConfigService()
            ->getAlternatives()
            ->values()
            ->toArray();
    }
}
