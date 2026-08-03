<?php

use App\Http\Controllers\BingoController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OrganizerController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ScratchController;
use App\Http\Controllers\SlideshowController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GuestController::class, 'splash'])->name('home');
Route::post('/register', [GuestController::class, 'register'])->name('guest.register');
Route::post('/logout', [GuestController::class, 'logout'])->name('guest.logout');

Route::middleware('guest.check')->group(function () {
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/api/tasks/random', [TaskController::class, 'random'])->name('api.tasks.random');
    Route::post('/tasks/{task}/upload', [PhotoController::class, 'upload'])->name('tasks.upload');
    Route::post('/photos', [PhotoController::class, 'upload'])->name('photos.upload');
    Route::get('/gallery', [PhotoController::class, 'gallery'])->name('gallery');
    Route::get('/ranking', [RankingController::class, 'index'])->name('ranking');

    Route::get('/wiadomosc', [MessageController::class, 'create'])->name('message.create');
    Route::post('/wiadomosc', [MessageController::class, 'store'])->name('message.store');

    Route::get('/bingo', [BingoController::class, 'index'])->name('bingo.index');
    Route::post('/bingo/{field}', [BingoController::class, 'markField'])->name('bingo.mark');

    Route::get('/zdrapka', [ScratchController::class, 'index'])->name('scratch.index');
    Route::post('/zdrapka', [ScratchController::class, 'scratch'])->name('scratch.scratch');

    Route::get('/cytaty', [QuoteController::class, 'index'])->name('quotes.index');
    Route::post('/cytaty', [QuoteController::class, 'store'])->name('quotes.store');
    Route::post('/cytaty/{quote}/like', [QuoteController::class, 'toggleLike'])->name('quotes.like');
    Route::get('/api/cytaty', [QuoteController::class, 'feed'])->name('api.quotes');

    Route::get('/api/photos', [PhotoController::class, 'feed'])->name('api.photos');
    Route::get('/api/ranking', [RankingController::class, 'feed'])->name('api.ranking');
});

Route::get('/slideshow', [SlideshowController::class, 'index'])->name('slideshow');
Route::get('/api/slideshow', [SlideshowController::class, 'feed'])->name('api.slideshow');

Route::prefix('organizer')->name('organizer.')->group(function () {
    Route::get('/login', [OrganizerController::class, 'loginForm'])->name('login');
    Route::post('/login', [OrganizerController::class, 'login'])->name('login.submit');
    Route::post('/logout', [OrganizerController::class, 'logout'])->name('logout');

    Route::middleware('organizer')->group(function () {
        Route::get('/', [OrganizerController::class, 'dashboard'])->name('dashboard');
        Route::delete('/photos/{photo}', [OrganizerController::class, 'destroyPhoto'])->name('photos.destroy');
        Route::delete('/messages/{message}', [OrganizerController::class, 'destroyMessage'])->name('messages.destroy');
        Route::get('/download', [OrganizerController::class, 'downloadZip'])->name('download');
    });
});
