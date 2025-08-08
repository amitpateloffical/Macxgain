<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;

Route::post('/resetPassword', [ResetPasswordController::class, 'reset'])->name('resetPassword');

Route::view('/{any}', 'app')->where('any', '.*');
