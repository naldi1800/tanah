<?php

namespace App\Http\Controllers;

use App\Services\AhpConfigService;
use App\Services\AhpRecommendationService;
use App\Services\AhpService;
use App\Services\LandSuitabilityService;
use Illuminate\Http\Request;

class AhpController extends Controller
{
    public function index(Request $request)
    {
        $configService = new AhpConfigService();

        $criteria = $configService->getCriteria();
        $matrix = $configService->getDefaultPairwiseMatrix();

        $ahp = new AhpService($criteria, $matrix, 1.12);
        $report = $ahp->report();

        $suitabilityService = new LandSuitabilityService($configService);
        $recommendation = (new AhpRecommendationService($ahp, $configService, $suitabilityService))
            ->loadRecords()
            ->recommendationReport();

        // top five recommendation

        return view('livewire.ahp.index', [
            'report' => $report,
            'recommendation' => $recommendation,
        ]);
    }
}
