<?php

namespace App\Http\Controllers;

use App\Models\Tanah;
use App\Models\JenisTanah;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $tanahCount = Tanah::count();
        $jenisTanahCount = JenisTanah::count();
        
        // For AHP, criteria and alternatives are typically stored in session or config
        // Let's get them from the AHP config service if available
        $criteriaCount = 0;
        $alternativesCount = $tanahCount; // Alternatives are the land data
        
        return view('dashboard', compact('tanahCount', 'jenisTanahCount', 'criteriaCount', 'alternativesCount'));
    }
}
