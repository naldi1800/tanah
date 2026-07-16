<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Volt::route('tanah', 'tanah.index')->name('tanah.index');
    Volt::route('jenis-tanah', 'jenistanah.index')->name('jenistanah.index');
    
    // AHP Routes
    Volt::route('ahp/data-kriteria', 'ahp.criteria-data')->name('ahp.criteria-data');
    Volt::route('ahp/data-alternatif', 'ahp.alternative-data')->name('ahp.alternative-data');
    Volt::route('ahp/analisis-kriteria', 'ahp.criteria-analysis')->name('ahp.criteria-analysis');
    Volt::route('ahp/analisis-alternatif', 'ahp.alternative-analysis')->name('ahp.alternative-analysis');
    Volt::route('ahp/perhitungan', 'ahp.calculation-result')->name('ahp.calculation-result');
    
    // Legacy route for backward compatibility
    Route::get('ahp', [App\Http\Controllers\AhpController::class, 'index'])->name('ahp.index');
});




require __DIR__.'/settings.php';
