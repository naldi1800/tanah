<?php

namespace App\Services;



class AhpAlternativeMatrixService{
    
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