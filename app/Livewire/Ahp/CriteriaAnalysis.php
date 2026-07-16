<?php

namespace App\Livewire\Ahp;

use App\Services\AhpConfigService;
use App\Services\AhpSessionService;
use App\Services\AhpService;
use Livewire\Component;
use Livewire\Attributes\Computed;

class CriteriaAnalysis extends Component
{
    protected AhpConfigService $configService;
    protected AhpSessionService $sessionService;

    public bool $showModal = false;
    public ?string $selectedCriteria1 = null;
    public ?string $selectedCriteria2 = null;
    public ?string $selectedScale = null;
    public string $search = '';

    public function mount()
    {
        $this->configService = new AhpConfigService();
        $this->sessionService = new AhpSessionService($this->configService);
    }

    #[Computed]
    public function criteria()
    {
        return $this->configService->getCriteria();
    }

    #[Computed]
    public function pairwiseMatrix()
    {
        return $this->sessionService->getCriteriaMatrix();
    }

    #[Computed]
    public function ahpService()
    {
        return new AhpService($this->criteria(), $this->pairwiseMatrix(), 1.12);
    }

    #[Computed]
    public function columnSums()
    {
        return $this->ahpService()->columnSums();
    }

    #[Computed]
    public function normalizedMatrix()
    {
        return $this->ahpService()->normalizedMatrix();
    }

    #[Computed]
    public function priorityVector()
    {
        return $this->ahpService()->priorityVector();
    }

    #[Computed]
    public function weights()
    {
        return $this->ahpService()->weights();
    }

    #[Computed]
    public function lambdaMax()
    {
        return $this->ahpService()->lambdaMax();
    }

    #[Computed]
    public function consistencyIndex()
    {
        return $this->ahpService()->consistencyIndex();
    }

    #[Computed]
    public function consistencyRatio()
    {
        return $this->ahpService()->consistencyRatio();
    }

    #[Computed]
    public function status()
    {
        return $this->ahpService()->status();
    }

    #[Computed]
    public function saatySaleOptions()
    {
        return $this->configService->getSaatySaleOptions();
    }

    #[Computed]
    public function availableCriteria2()
    {
        if (!$this->selectedCriteria1) {
            return [];
        }

        return array_filter($this->criteria(), function ($c) {
            return $c !== $this->selectedCriteria1;
        });
    }

    #[Computed]
    public function rowEigenValues()
    {
        $weights = $this->weights();
        $matrix = $this->pairwiseMatrix();
        $eigen = [];

        foreach ($matrix as $rowIndex => $row) {
            $weightedSum = 0.0;
            foreach ($row as $colIndex => $value) {
                $weightedSum += (float) $value * $weights[$colIndex];
            }
            $eigen[$rowIndex] = $weights[$rowIndex] !== 0
                ? $weightedSum / $weights[$rowIndex]
                : 0.0;
        }

        return $eigen;
    }

    public function openModal(): void
    {
        $this->showModal = true;
        $this->dispatch('open-criteria-modal');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->dispatch('close-criteria-modal');
        $this->resetModalForm();
    }

    protected function resetModalForm(): void
    {
        $this->selectedCriteria1 = null;
        $this->selectedCriteria2 = null;
        $this->selectedScale = null;
    }

    public function updateValue(): void
    {
        if (!$this->selectedCriteria1 || !$this->selectedCriteria2 || !$this->selectedScale) {
            return;
        }

        $row = $this->configService->getCriteriaIndex($this->selectedCriteria1);
        $col = $this->configService->getCriteriaIndex($this->selectedCriteria2);

        if ($row === null || $col === null) {
            return;
        }

        $value = $this->configService->scaleToNumeric($this->selectedScale);
        $this->sessionService->updateCriteriaValue($row, $col, $value);

        $this->closeModal();
        $this->dispatch('notify', message: 'Nilai pairwise berhasil diperbarui');
    }

    public function resetToDefault(): void
    {
        $this->sessionService->resetCriteriaMatrix();
        $this->dispatch('notify', message: 'Nilai pairwise direset ke default');
    }

    public function render()
    {
        return view('livewire.ahp.criteria-analysis', [
            'configService' => $this->configService,
            'criteria' => $this->criteria(),
            'pairwiseMatrix' => $this->pairwiseMatrix(),
            'columnSums' => $this->columnSums(),
            'normalizedMatrix' => $this->normalizedMatrix(),
            'priorityVector' => $this->priorityVector(),
            'weights' => $this->weights(),
            'lambdaMax' => $this->lambdaMax(),
            'ci' => $this->consistencyIndex(),
            'cr' => $this->consistencyRatio(),
            'status' => $this->status(),
            'rowEigenValues' => $this->rowEigenValues(),
            'saatySaleOptions' => $this->saatySaleOptions(),
            'availableCriteria2' => $this->availableCriteria2(),
        ]);
    }
}
