<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('user/{user:username}', [ProfileController::class, 'index'])
    ->name('profile');

Route::middleware('auth')
    ->group(function () {
        Route::post('/profile/update-images', [ProfileController::class, 'updateImage'])
            ->name('profile.updateImage');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::post('/post', [PostController::class, 'store'])->name('post.store');
        Route::put('/post/{post:id}', [PostController::class, 'update'])->name('post.update');
    }
);

require __DIR__.'/auth.php';
