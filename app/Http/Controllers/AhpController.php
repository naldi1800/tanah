<?php

namespace App\Http\Controllers;

use App\Services\AhpRecommendationService;
use App\Services\AhpService;
use Illuminate\Http\Request;

class AhpController extends Controller
{
    public function index(Request $request)
    {
        $criteria = [
            'Jenis Tanah',
            'pH Tanah',
            'Kelembapan',
            'Suhu',
            'Ketinggian',
        ];

        $matrix = [
            [1, 5, 5, 7, 7],
            [1/5, 1, 3, 5, 5],
            [1/5, 1/3, 1, 3, 3],
            [1/7, 1/5, 1/3, 1, 2],
            [1/7, 1/5, 1/3, 1/2, 1],
        ];

        $ahp = new AhpService($criteria, $matrix, 1.12);
        $report = $ahp->report();

        $recommendation = (new AhpRecommendationService($ahp))
            ->loadRecords()
            ->recommendationReport();

        // top five recommendation

        return view('livewire.ahp.index', [
            'report' => $report,
            'recommendation' => $recommendation,
        ]);
    }
}
