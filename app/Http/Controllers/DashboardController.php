<?php

namespace App\Http\Controllers;

use App\Models\Tanah;
use App\Models\JenisTanah;
use App\Services\AhpConfigService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $tanahCount = Tanah::count();
        $jenisTanahCount = JenisTanah::count();
        
        // Get criteria and alternatives from AHP Config Service
        $configService = new AhpConfigService();
        $criteriaCount = count($configService->getCriteria());
        $alternativesCount = $configService->getAlternatives()->count();
        
        return view('dashboard', compact('tanahCount', 'jenisTanahCount', 'criteriaCount', 'alternativesCount'));
    }
}
