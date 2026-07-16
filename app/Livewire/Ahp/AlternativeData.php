<?php

namespace App\Livewire\Ahp;

use App\Services\AhpConfigService;
use Livewire\Component;
use Livewire\Attributes\Computed;

class AlternativeData extends Component
{
    protected AhpConfigService $configService;

    public string $search = '';

    public function mount()
    {
        $this->configService = new AhpConfigService();
    }

    #[Computed]
    public function alternatives()
    {
        $allAlternatives = $this->configService->getAlternatives()->toArray();

        if (empty($this->search)) {
            return $allAlternatives;
        }

        return array_filter($allAlternatives, function ($alternative) {
            return stripos($alternative, $this->search) !== false;
        });
    }

    public function render()
    {
        return view('livewire.ahp.alternative-data', [
            'alternatives' => $this->alternatives(),
        ]);
    }
}
