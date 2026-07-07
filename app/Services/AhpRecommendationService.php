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

    public function __construct(AhpService $ahpService)
    {
        $this->ahpService = $ahpService;
        $this->criteriaWeights = $this->resolveWeights();
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
            return $this->streetName($tanah->Alamat);
        });
    }

    protected function streetName(string $alamat): string
    {
        // split by common 'No' patterns first
        $part = preg_split('/\s+No\.?\s*/i', $alamat, 2);
        $base = trim($part[0]);

        // remove trailing numeric or unit suffixes (e.g., 'Jl. Kopi 1' -> 'Jl. Kopi')
        $base = preg_replace('/[\s,;:-]*\b(?:No\.?|no\.?|\d+|[IVXLC]+)\b.*$/i', '', $base);
        // remove any trailing standalone numbers
        $base = preg_replace('/\s+\d+$/', '', $base);
        $base = preg_replace('/\s+\d+\/\d+$/', '', $base);

        return trim($base);
    }

    public function streetStatistics(): Collection
    {
        return $this->groupedByStreet()->map(function (Collection $group, string $street) {
            $dominant = $this->calcDominantJenis($group);

            return [
                'street' => $street,
                'count' => $group->count(),
                'jenis_dominan' => $dominant['jenis'] ?? null,
                'jenis_dominan_id' => $dominant['id'] ?? null,
                'avg_ph' => $group->avg('PH_Tanah'),
                'avg_kelembapan' => $group->avg('Kelembaban_Tanah'),
                'avg_suhu' => $group->avg('Suhu_Tanah'),
                'avg_ketinggian' => $group->avg('Ketinggian_Tanah'),
            ];
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
            ->map(fn (array $stats) => $this->evaluateStreet($stats))
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
                'matrix' => $matrix,
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

        // Slight bias: emphasize street 1, then 2, then 3 according to user's hint (50:30:20)
        // We'll multiply raw values by a bias factor based on index (0-based)
        $bias = array_map(fn($i) => match ($i) {
            0 => 1.2,
            1 => 1.1,
            2 => 1.05,
            default => 1.0,
        }, array_keys($values));

        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                if ($i === $j) {
                    $matrix[$i][$j] = 1.0;
                    continue;
                }

                $vi = $values[$i] * $bias[$i];
                $vj = $values[$j] * $bias[$j];

                // ratio
                $ratio = $vj !== 0 ? $vi / $vj : 1.0;

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

        // Use log2 to compress the ratio, then map to 1..9
        $sign = $ratio > 1 ? 1 : -1;
        $r = $ratio > 1 ? $ratio : 1 / $ratio;
        $scale = log($r, 2); // how many doublings

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
}
