<?php

namespace App\Livewire\Ahp;

use App\Services\AhpConfigService;
use App\Services\AhpSessionService;
use App\Services\AhpService;
use App\Services\AhpRecommendationService;
use Livewire\Component;
use Livewire\Attributes\Computed;

class CalculationResult extends Component
{
    protected ?AhpConfigService $configService = null;
    protected ?AhpSessionService $sessionService = null;

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
    }

    #[Computed]
    public function criteria()
    {
        return $this->getConfigService()->getCriteria();
    }

    #[Computed]
    public function ahpService()
    {
        $matrix = $this->getSessionService()->getCriteriaMatrix();
        return new AhpService($this->criteria(), $matrix, 1.12);
    }

    #[Computed]
    public function criteriaReport()
    {
        return $this->ahpService()->report();
    }

    #[Computed]
    public function recommendation()
    {
        $ahp = $this->ahpService();
        $suitabilityService = new \App\Services\LandSuitabilityService($this->getConfigService());
        $service = new AhpRecommendationService($ahp, $this->getConfigService(), $suitabilityService);
        $service->loadRecords();

        return $service->recommendationReport();
    }

    #[Computed]
    public function topResults()
    {
        $results = $this->recommendation()['ahp_final_ranking'] ?? [];
        return array_slice($results, 0, 5);
    }

    public function forceResetSession(): void
    {
        $this->getSessionService()->forceResetForNewScoring();
        $this->dispatch('notify', message: 'Session di-reset untuk menggunakan skor kesesuaian baru');
    }

    public function render()
    {
        return view('livewire.ahp.calculation-result', [
            'criteriaReport' => $this->criteriaReport(),
            'recommendation' => $this->recommendation(),
            'topResults' => $this->topResults(),
        ]);
    }
}
