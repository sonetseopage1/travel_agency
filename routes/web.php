<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PromoCodeController as AdminPromoCodeController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\TourController as AdminTourController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TourController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tours', [TourController::class, 'index'])->name('tours.index');
Route::get('/tour/{slug}', [TourController::class, 'show'])->name('tours.show');
Route::get('/booking', [BookingController::class, 'create'])->name('bookings.create');
Route::post('/booking', [BookingController::class, 'store'])->name('bookings.store');
Route::post('/booking/validate-promo', [BookingController::class, 'validatePromo'])->name('bookings.validate-promo');
Route::get('/booking/{id}/success', [BookingController::class, 'success'])->name('bookings.success');
Route::post('/contact', [HomeController::class, 'contactSubmit'])->name('contact.submit');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('tours', AdminTourController::class);
    Route::resource('bookings', AdminBookingController::class)->except(['edit', 'update', 'destroy']);
    Route::patch('bookings/{id}/status', [AdminBookingController::class, 'update'])->name('bookings.status');
    Route::resource('reviews', AdminReviewController::class);
    Route::post('reviews/{id}/toggle', [AdminReviewController::class, 'toggleApproval'])->name('reviews.toggle');
    Route::resource('promo-codes', AdminPromoCodeController::class)->except('show');
});

Route::post('/logout', function () {
    auth()->logout();

    return redirect('/');
})->name('logout')->middleware('web');

require __DIR__.'/auth.php';
