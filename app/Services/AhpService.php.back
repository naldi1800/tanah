<?php

namespace App\Services;

use InvalidArgumentException;

class AhpService
{
    protected array $criteria;
    protected array $matrix;
    protected float $randomIndex;
    protected array $columnSums = [];
    protected array $normalizedMatrix = [];
    protected array $priorityVector = [];
    protected ?float $lambdaMax = null;
    protected ?float $ci = null;
    protected ?float $cr = null;

    public function __construct(array $criteria, array $matrix, float $randomIndex = 1.12)
    {
        $this->criteria = $criteria;
        $this->matrix = $matrix;
        $this->randomIndex = $randomIndex;

        $this->validateMatrix();
    }

    protected function validateMatrix(): void
    {
        $size = count($this->matrix);

        if ($size === 0) {
            throw new InvalidArgumentException('Matrix AHP tidak boleh kosong.');
        }

        if ($size !== count($this->criteria)) {
            throw new InvalidArgumentException('Jumlah kriteria harus sama dengan ukuran matrix.');
        }

        foreach ($this->matrix as $row) {
            if (!is_array($row) || count($row) !== $size) {
                throw new InvalidArgumentException('Matrix AHP harus berbentuk matriks bujur sangkar.');
            }
        }
    }

    public function columnSums(): array
    {
        if (!empty($this->columnSums)) {
            return $this->columnSums;
        }

        $size = count($this->matrix);
        $this->columnSums = array_fill(0, $size, 0.0);

        foreach ($this->matrix as $row) {
            foreach ($row as $index => $value) {
                $this->columnSums[$index] += (float) $value;
            }
        }

        return $this->columnSums;
    }

    public function normalizedMatrix(): array
    {
        if (!empty($this->normalizedMatrix)) {
            return $this->normalizedMatrix;
        }

        $columnSums = $this->columnSums();

        foreach ($this->matrix as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $this->normalizedMatrix[$rowIndex][$columnIndex] = $columnSums[$columnIndex] !== 0
                    ? (float) $value / $columnSums[$columnIndex]
                    : 0.0;
            }
        }

        return $this->normalizedMatrix;
    }

    public function priorityVector(): array
    {
        if (!empty($this->priorityVector)) {
            return $this->priorityVector;
        }

        $normalized = $this->normalizedMatrix();
        $size = count($normalized);

        foreach ($normalized as $rowIndex => $row) {
            $this->priorityVector[$rowIndex] = array_sum($row) / $size;
        }

        return $this->priorityVector;
    }

    public function weights(): array
    {
        return $this->priorityVector();
    }

    public function lambdaMax(): float
    {
        if ($this->lambdaMax !== null) {
            return $this->lambdaMax;
        }

        $weights = $this->weights();
        $size = count($this->matrix);
        $lambdaValues = [];

        foreach ($this->matrix as $rowIndex => $row) {
            $weightedSum = 0.0;

            foreach ($row as $columnIndex => $value) {
                $weightedSum += (float) $value * $weights[$columnIndex];
            }

            $lambdaValues[] = $weights[$rowIndex] !== 0
                ? $weightedSum / $weights[$rowIndex]
                : 0.0;
        }

        $this->lambdaMax = array_sum($lambdaValues) / $size;

        return $this->lambdaMax;
    }

    public function consistencyIndex(): float
    {
        if ($this->ci !== null) {
            return $this->ci;
        }

        $n = count($this->matrix);
        $this->ci = ($this->lambdaMax() - $n) / ($n - 1);

        return $this->ci;
    }

    public function consistencyRatio(): float
    {
        if ($this->cr !== null) {
            return $this->cr;
        }

        $ri = $this->randomIndex;
        $this->cr = $ri !== 0 ? $this->consistencyIndex() / $ri : 0.0;

        return $this->cr;
    }

    public function status(): string
    {
        return $this->consistencyRatio() <= 0.1 ? 'Konsisten' : 'Tidak Konsisten';
    }

    public function report(): array
    {
        return [
            'criteria' => $this->criteria,
            'matrix' => $this->matrix,
            'columnSums' => $this->columnSums(),
            'normalized' => $this->normalizedMatrix(),
            'priorityVector' => $this->priorityVector(),
            'weights' => $this->weights(),
            'lambdaMax' => $this->lambdaMax(),
            'ci' => $this->consistencyIndex(),
            'cr' => $this->consistencyRatio(),
            'status' => $this->status(),
        ];
    }
}
