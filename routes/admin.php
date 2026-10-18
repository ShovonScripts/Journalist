<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContentItemController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PublicationController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TopicController;
use App\Http\Middleware\NoIndexAdmin;
use Illuminate\Support\Facades\Route;

/*
 * The admin surface. Loaded with the `web` middleware group in
 * bootstrap/app.php behind the `admin` prefix and `admin.` name prefix
 * (ADMIN_PANEL.md §3). Screens are added phase by phase (ADMIN_PANEL.md §16);
 * the sidebar renders any route that does not exist yet as disabled.
 */
Route::prefix('admin')->name('admin.')->middleware(NoIndexAdmin::class)->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->middleware('throttle:admin-login');
    });

    Route::middleware(['auth', 'role:owner'])->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Content — one controller for every type and source.
        Route::get('content/slug-check', [ContentItemController::class, 'slugCheck'])->name('content.slug-check');
        Route::post('content/bulk', [ContentItemController::class, 'bulk'])->name('content.bulk');
        Route::get('content', [ContentItemController::class, 'index'])->name('content.index');
        Route::get('content/create', [ContentItemController::class, 'create'])->name('content.create');
        Route::post('content', [ContentItemController::class, 'store'])->name('content.store');
        Route::get('content/{contentItem}/edit', [ContentItemController::class, 'edit'])->name('content.edit');
        Route::put('content/{contentItem}', [ContentItemController::class, 'update'])->name('content.update');
        Route::delete('content/{contentItem}', [ContentItemController::class, 'destroy'])->name('content.destroy');

        // Registries.
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('tags', TagController::class)->except('show');
        Route::resource('topics', TopicController::class)->except('show');
        Route::resource('publications', PublicationController::class)->except('show');

        // Media library.
        Route::get('media/pick', [MediaController::class, 'pick'])->name('media.pick');
        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::get('media/{media}/edit', [MediaController::class, 'edit'])->name('media.edit');
        Route::put('media/{media}', [MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
    });
});
