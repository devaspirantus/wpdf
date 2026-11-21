<?php

use App\Http\Controllers\DocumentImageController;
use App\Http\Controllers\ProfileController;
use App\Livewire\CaseSearch;
use App\Livewire\PracticalStation;
use App\Livewire\Reception;
use App\Livewire\ReviewStation;
use App\Livewire\TheoryStation;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/reception', Reception::class)
        ->middleware('station:reception')
        ->name('reception');
    Route::get('/search', CaseSearch::class)
        ->name('case.search');
    Route::get('/theory/{case?}', TheoryStation::class)
        ->name('theory');
    Route::get('/practical/{case}', PracticalStation::class)
        ->name('practical');
    Route::get('/review', ReviewStation::class)
        ->middleware('station:review')
        ->name('review');
    Route::get('/documents/{document}', [DocumentImageController::class, 'show'])
        ->name('documents.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
