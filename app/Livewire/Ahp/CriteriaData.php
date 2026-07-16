<?php

namespace App\Livewire\Ahp;

use App\Services\AhpConfigService;
use Livewire\Component;
use Livewire\Attributes\Computed;

class CriteriaData extends Component
{
    protected AhpConfigService $configService;

    public string $search = '';

    public function mount()
    {
        $this->configService = new AhpConfigService();
    }

    #[Computed]
    public function criteria()
    {
        $allCriteria = $this->configService->getCriteria();

        if (empty($this->search)) {
            return $allCriteria;
        }

        return array_filter($allCriteria, function ($criterion) {
            return stripos($criterion, $this->search) !== false;
        });
    }

    public function render()
    {
        return view('livewire.ahp.criteria-data', [
            'criteria' => $this->criteria(),
        ]);
    }
}
