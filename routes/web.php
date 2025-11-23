<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;

// Route::post('/resetPassword', [ResetPasswordController::class, 'reset'])->name('resetPassword');

// Serve favicon.ico as logo.png (for browsers that require .ico)
Route::get('/favicon.ico', function () {
    $logoPath = public_path('logo.png');
    if (file_exists($logoPath)) {
        return response()->file($logoPath, ['Content-Type' => 'image/png']);
    }
    return response('', 404);
})->name('favicon');

Route::view('/{any}', 'app')->where('any', '.*');
