<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('user/{user:username}', [ProfileController::class, 'index'])
    ->name('profile');

Route::get('group/{group:slug}', [GroupController::class, 'show'])
    ->name('group.profile');

Route::middleware('auth')
    ->group(function () {
        # Profile routes
        Route::post('/profile/update-images', [ProfileController::class, 'updateImage'])->name('profile.updateImage');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        #Group routes
        Route::post('/group/update-images/{group:slug}', [GroupController::class, 'updateImage'])->name('group.updateImage');

        # Post routes
        Route::post('/post', [PostController::class, 'store'])->name('post.store');
        Route::put('/post/{post}', [PostController::class, 'update'])->name('post.update');
        Route::delete('/post/{post}', [PostController::class, 'destroy'])->name('post.destroy');
        Route::get('/post/download/{post_attachment}', [PostController::class, 'downloadAttachment'])->name('post.download');
        Route::post('/post/{post}/reaction', [PostController::class, 'postReaction'])->name('post.reaction');

        # Comment routes
        Route::post('/post/{post}/comment', [PostController::class, 'createComment'])->name('post.comment.create');
        Route::delete('/post/comment/{comment}', [PostController::class, 'deleteComment'])->name('post.comment.delete');
        Route::put('/post/comment/{comment}', [PostController::class, 'updateComment'])->name('post.comment.update');
        Route::post('/post/comment/{comment}/reaction', [PostController::class, 'commentReaction'])->name('post.comment.reaction');

        # Group routes
        Route::post('/group', [GroupController::class, 'store'])->name('group.store');
    }
);

require __DIR__.'/auth.php';
