<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\OltController;
use App\Http\Controllers\OnuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AcsProfileController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('acs-profiles', AcsProfileController::class);
    Route::resource('script-templates', App\Http\Controllers\ScriptTemplateController::class);

    Route::get('olts/{olt}/status', [OltController::class, 'status'])->name('olts.status');
    Route::post('olts/{olt}/sync', [OltController::class, 'sync'])->name('olts.sync');
    Route::resource('olts', OltController::class);
    
    Route::post('onus/sync-background', [OnuController::class, 'syncBackground'])->name('onus.sync.background');
    Route::get('onus/{onu}/power', [OnuController::class, 'power'])->name('onus.power');
    Route::get('onus/unconfigured', [OnuController::class, 'unconfigured'])->name('onus.unconfigured');
    Route::resource('onus', OnuController::class)->except(['show']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
