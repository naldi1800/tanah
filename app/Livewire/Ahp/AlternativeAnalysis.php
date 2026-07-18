<?php

namespace App\Livewire\Ahp;

use App\Services\AhpConfigService;
use App\Services\AhpSessionService;
use App\Services\AhpService;
use Livewire\Component;
use Livewire\Attributes\Computed;

class AlternativeAnalysis extends Component
{
    protected ?AhpConfigService $configService = null;
    protected ?AhpSessionService $sessionService = null;

    public string $selectedCriterion = '';
    public bool $showModal = false;
    public ?string $selectedAlternative1 = null;
    public ?string $selectedAlternative2 = null;
    public ?string $selectedScale = null;
    public string $search = '';

    protected function getConfigService(): AhpConfigService
    {
        return $this->configService ??= new AhpConfigService();
    }

    protected function getSessionService(): AhpSessionService
    {
        return $this->sessionService ??= new AhpSessionService($this->getConfigService());
    }

    public function mount()
    {
        // Initialize session dengan default data untuk alternatif
        if (empty($this->getSessionService()->getAlternativesMatrix())) {
            $this->getSessionService()->resetAlternativesMatrix();
        }

        // Set default criterion
        if (empty($this->selectedCriterion)) {
            $criteria = $this->getConfigService()->getCriteria();
            $this->selectedCriterion = $criteria[0] ?? '';
        }
    }

    #[Computed]
    public function criteria()
    {
        return $this->getConfigService()->getCriteria();
    }

    #[Computed]
    public function alternatives()
    {
        return $this->getConfigService()->getAlternativesWithIndex();
    }

    #[Computed]
    public function pairwiseMatrix()
    {
        return $this->getSessionService()->getAlternativesMatrixByCriteria($this->selectedCriterion);
    }

    #[Computed]
    public function alternativesList()
    {
        $list = $this->getConfigService()->getAlternatives()->toArray();

        if (empty($this->search)) {
            return $list;
        }

        return array_filter($list, function ($alt) {
            return stripos($alt, $this->search) !== false;
        });
    }

    #[Computed]
    public function ahpService()
    {
        $alternatives = $this->getConfigService()->getAlternatives()->toArray();
        return new AhpService($alternatives, $this->pairwiseMatrix(), 1.12);
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
    public function saatySaleOptions()
    {
        return $this->getConfigService()->getSaatySaleOptions();
    }

    #[Computed]
    public function availableAlternatives2()
    {
        if (!$this->selectedAlternative1) {
            return [];
        }

        return array_filter($this->alternativesList(), function ($alt) {
            return $alt !== $this->selectedAlternative1;
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

    public function updatedSelectedCriterion(): void
    {
        // Reset ketika kriteria berubah
        $this->resetModalForm();
    }

    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetModalForm();
    }

    protected function resetModalForm(): void
    {
        $this->selectedAlternative1 = null;
        $this->selectedAlternative2 = null;
        $this->selectedScale = null;
    }

    public function updateValue(): void
    {
        if (!$this->selectedAlternative1 || !$this->selectedAlternative2 || !$this->selectedScale) {
            return;
        }

        $alternatives = $this->getConfigService()->getAlternatives()->toArray();
        $row = array_search($this->selectedAlternative1, $alternatives, true);
        $col = array_search($this->selectedAlternative2, $alternatives, true);

        if ($row === false || $col === false) {
            return;
        }

        $value = $this->getConfigService()->scaleToNumeric($this->selectedScale);
        $this->getSessionService()->updateAlternativeValue($this->selectedCriterion, $row, $col, $value);

        $this->closeModal();
        $this->dispatch('notify', message: 'Nilai pairwise berhasil diperbarui');
    }

    public function resetToDefault(): void
    {
        $this->getSessionService()->forceResetForNewScoring();
        $this->dispatch('notify', message: 'Nilai pairwise direset ke default dengan skor kesesuaian baru');
    }

    public function render()
    {
        return view('livewire.ahp.alternative-analysis', [
            'configService' => $this->getConfigService(),
            'criteria' => $this->criteria(),
            'alternatives' => $this->alternativesList(),
            'pairwiseMatrix' => $this->pairwiseMatrix(),
            'columnSums' => $this->columnSums(),
            'normalizedMatrix' => $this->normalizedMatrix(),
            'priorityVector' => $this->priorityVector(),
            'weights' => $this->weights(),
            'rowEigenValues' => $this->rowEigenValues(),
            'saatySaleOptions' => $this->saatySaleOptions(),
            'availableAlternatives2' => $this->availableAlternatives2(),
        ]);
    }
}
