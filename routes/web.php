<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Web\Admin\StepperPageController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

// Route::middleware(['auth', 'verified'])->group(function () {
//     Route::get('dashboard', function () {
//         return Inertia::render('dashboard');
//     })->name('dashboard');
// });
Route::middleware(['auth', 'role_check'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('stepper-pages', StepperPageController::class);
        Route::patch('stepper-pages/{stepper_page}/toggle', [StepperPageController::class, 'toggleStatus'])->name('stepper-pages.toggle');
        Route::get('stepper-data', [StepperPageController::class, 'newIndex'])->name('stepper-data');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
