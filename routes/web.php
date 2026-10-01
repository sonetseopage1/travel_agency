<?php

use App\Http\Controllers\Admin\BlogPostController as AdminBlogPostController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\PromoCodeController as AdminPromoCodeController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\TourController as AdminTourController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TourController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tours', [TourController::class, 'index'])->name('tours.index');
Route::get('/tour/{slug}', [TourController::class, 'show'])->name('tours.show');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/gallery/{photo}', [GalleryController::class, 'show'])->name('gallery.show');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/booking', [BookingController::class, 'create'])->name('bookings.create');
Route::post('/booking', [BookingController::class, 'store'])->name('bookings.store');
Route::post('/booking/validate-promo', [BookingController::class, 'validatePromo'])->name('bookings.validate-promo');

// Keyed on the receipt token rather than the booking id. Ids are sequential, so
// an id-based link would let anyone who guessed a number read another
// customer's name, phone number and price.
Route::get('/booking/receipt/{token}', [BookingController::class, 'receipt'])
    ->name('bookings.receipt');
Route::get('/booking/receipt/{token}/download', [BookingController::class, 'downloadReceipt'])
    ->name('bookings.receipt.download');
Route::post('/contact', [HomeController::class, 'contactSubmit'])->name('contact.submit');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('tours', AdminTourController::class);
    Route::resource('bookings', AdminBookingController::class)->except(['edit', 'update', 'destroy']);
    Route::patch('bookings/{id}/status', [AdminBookingController::class, 'update'])->name('bookings.status');
    Route::resource('reviews', AdminReviewController::class);
    Route::post('reviews/{id}/toggle', [AdminReviewController::class, 'toggleApproval'])->name('reviews.toggle');
    Route::resource('promo-codes', AdminPromoCodeController::class)->except('show');
    Route::resource('gallery', AdminGalleryController::class)
        ->parameters(['gallery' => 'photo'])
        ->except('show');
    Route::resource('blog', AdminBlogPostController::class)
        ->parameters(['blog' => 'post'])
        ->except('show');

    Route::get('settings', [AdminSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::put('settings/password', [AdminSettingsController::class, 'updatePassword'])->name('settings.password');
});

Route::post('/logout', function () {
    auth()->logout();

    return redirect('/');
})->name('logout')->middleware('web');

require __DIR__.'/auth.php';
