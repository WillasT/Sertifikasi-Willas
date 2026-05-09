<?php

use Illuminate\Http\Request;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BorrowerController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
// use GuzzleHttp\Psr7\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (Request $request) {
    if ($request->user()->account_type === 'admin') {
        return redirect()->route('admin.reservations.index');
    }
    return redirect()->route('reservations.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/check-equipment', [ReservationController::class, 'checkAvailableEquipment']);
    Route::get('/reservations/booked-times', [ReservationController::class, 'getBookedTimes']);

    Route::get('/reservations/{reservation}/edit', [ReservationController::class, 'edit'])->name('reservations.edit');
    Route::put('/reservations/{reservation}', [ReservationController::class, 'update'])->name('reservations.update');
});

Route::middleware(['auth', EnsureUserIsAdmin::class])->group(function () {
    Route::get('/borrowers', [BorrowerController::class, 'index'])->name('borrowers.index');
    Route::get('/borrowers/create', [BorrowerController::class, 'create'])->name('borrowers.create');
    Route::post('/borrowers', [BorrowerController::class, 'store'])->name('borrowers.store');
    Route::get('/borrowers/{borrower}/edit', [BorrowerController::class, 'edit'])->name('borrowers.edit');
    Route::patch('/borrowers/{borrower}', [BorrowerController::class, 'update'])->name('borrowers.update');
    Route::delete('/borrowers/{borrower}', [BorrowerController::class, 'destroy'])->name('borrowers.destroy');

    Route::resource('rooms', RoomController::class)->except('show');
    Route::resource('equipments', EquipmentController::class)->except('show');

    Route::get('/admin/reservations', [AdminReservationController::class, 'index'])->name('admin.reservations.index');
    Route::get('/admin/reservations/{reservation}', [AdminReservationController::class, 'show'])->name('admin.reservations.show');
    Route::get('/admin/reservations/export/excel', [AdminReservationController::class, 'exportExcel'])->name('admin.reservations.export.excel');
    Route::get('/admin/reservations/export/pdf', [AdminReservationController::class, 'exportPdf'])->name('admin.reservations.export.pdf');
    Route::patch('/admin/reservations/{reservation}/approve', [AdminReservationController::class, 'approve'])->name('admin.reservations.approve');
    Route::patch('/admin/reservations/{reservation}/reject', [AdminReservationController::class, 'reject'])->name('admin.reservations.reject');
    Route::patch('/admin/reservations/{reservation}/done', [AdminReservationController::class, 'markAsDone'])->name('admin.reservations.done');
});

require __DIR__.'/auth.php';
