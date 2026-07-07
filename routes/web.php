<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Volt::route('tanah', 'tanah.index')->name('tanah.index');
    Volt::route('jenis-tanah', 'jenistanah.index')->name('jenistanah.index');
    Route::get('ahp', [App\Http\Controllers\AhpController::class, 'index'])->name('ahp.index');
});




require __DIR__.'/settings.php';
