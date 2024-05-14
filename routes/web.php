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
        # Profile routes
        Route::post('/profile/update-images', [ProfileController::class, 'updateImage'])->name('profile.updateImage');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


        # Post routes
        Route::post('/post', [PostController::class, 'store'])->name('post.store');
        Route::put('/post/{post}', [PostController::class, 'update'])->name('post.update');
        Route::delete('/post/{post}', [PostController::class, 'destroy'])->name('post.destroy');
        Route::get('/post/download/{post_attachment}', [PostController::class, 'downloadAttachment'])->name('post.download');
        Route::post('/post/{post}/reaction', [PostController::class, 'postReaction'])->name('post.reaction');

        Route::post('/post/{post}/comment', [PostController::class, 'createComment'])->name('post.comment.create');
        Route::delete('/post/comment/{comment}', [PostController::class, 'deleteComment'])->name('post.comment.delete');
        Route::put('/post/comment/{comment}', [PostController::class, 'updateComment'])->name('post.comment.update');
    }
);

require __DIR__.'/auth.php';
