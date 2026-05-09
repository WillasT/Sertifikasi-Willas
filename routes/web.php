<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BorrowerController;
use App\Http\Middleware\EnsureUserIsAdmin;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', EnsureUserIsAdmin::class])->group(function () {
    Route::get('/borrowers', [BorrowerController::class, 'index'])->name('borrowers.index');
    Route::get('/borrowers/{borrower}/edit', [BorrowerController::class, 'edit'])->name('borrowers.edit');
    Route::patch('/borrowers/{borrower}', [BorrowerController::class, 'update'])->name('borrowers.update');
    Route::delete('/borrowers/{borrower}', [BorrowerController::class, 'destroy'])->name('borrowers.destroy');

    // In the future, you can easily drop Room Management and Equipment Management routes in here too!
});

require __DIR__.'/auth.php';
