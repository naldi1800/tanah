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
    protected AhpConfigService $configService;
    protected AhpSessionService $sessionService;

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
    public function ahpService()
    {
        $matrix = $this->sessionService->getCriteriaMatrix();
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
        $service = new AhpRecommendationService($ahp);
        $service->loadRecords();

        return $service->recommendationReport();
    }

    #[Computed]
    public function topResults()
    {
        $results = $this->recommendation()['final_scores'] ?? [];
        return array_slice($results, 0, 5);
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
