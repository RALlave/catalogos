<?php

use App\Enums\UserRole;
use App\Http\Controllers\Api\Admin\MetricsController as AdminMetricsController;
use App\Http\Controllers\Api\Admin\StoreController as AdminStoreController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CategoryReorderController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\Public\OrderController as PublicOrderController;
use App\Http\Controllers\Api\Public\ProductController as PublicProductController;
use App\Http\Controllers\Api\Public\StoreController as PublicStoreController;
use App\Http\Controllers\Api\Public\ThemeController;
use App\Http\Controllers\Api\Public\WaitlistController as PublicWaitlistController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\HeroController;
use App\Http\Controllers\Api\HeroReorderController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProductCloneController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\ProductReorderController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\StoreImageController;
use App\Http\Controllers\Api\WaitlistController;
use Illuminate\Support\Facades\Route;

Route::post('register', RegisterController::class)->middleware('throttle:10,1');

Route::post('login', [LoginController::class, 'login'])->middleware('throttle:6,1');

Route::post('forgot-password', [PasswordResetController::class, 'forgot'])->middleware('throttle:6,1');

Route::post('reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1');

Route::get('themes', ThemeController::class)->name('public.themes');

Route::get('stores/{slug}', [PublicStoreController::class, 'show'])->name('public.store.show');
Route::get('stores/{slug}/products', [PublicProductController::class, 'index'])->name('public.products.index');
Route::get('stores/{slug}/products/{productSlug}', [PublicProductController::class, 'show'])->name('public.products.show');

/* Los dos son anónimos y escriben en la base: van con límite de peticiones. */
Route::post('stores/{slug}/orders', [PublicOrderController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('public.orders.store');
Route::post('stores/{slug}/waitlist', [PublicWaitlistController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('public.waitlist.store');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('me', [LoginController::class, 'me']);
    Route::post('logout', [LoginController::class, 'logout']);

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('password', [ProfileController::class, 'password'])->name('profile.password');

    Route::get('store', [StoreController::class, 'show'])->name('store.show');
    Route::post('store', [StoreController::class, 'store'])->name('store.store');
    Route::put('store', [StoreController::class, 'update'])->name('store.update');

    Route::post('store/logo', [StoreImageController::class, 'upload'])
        ->defaults('field', 'logo')
        ->name('store.logo.upload');
    Route::put('store/logo', [StoreImageController::class, 'set'])
        ->defaults('field', 'logo')
        ->name('store.logo.set');
    Route::delete('store/logo', [StoreImageController::class, 'destroy'])
        ->defaults('field', 'logo')
        ->name('store.logo.destroy');

    Route::post('store/cover', [StoreImageController::class, 'upload'])
        ->defaults('field', 'cover')
        ->name('store.cover.upload');
    Route::put('store/cover', [StoreImageController::class, 'set'])
        ->defaults('field', 'cover')
        ->name('store.cover.set');
    Route::delete('store/cover', [StoreImageController::class, 'destroy'])
        ->defaults('field', 'cover')
        ->name('store.cover.destroy');

    /* El singular de "media" que arma Laravel sería "medium": se fija a mano. */
    Route::apiResource('media', MediaController::class)->parameters(['media' => 'media']);

    Route::post('heroes/reorder', HeroReorderController::class)->name('heroes.reorder');
    Route::apiResource('heroes', HeroController::class);

    Route::post('categories/reorder', CategoryReorderController::class)->name('categories.reorder');
    Route::apiResource('categories', CategoryController::class);

    Route::post('products/reorder', ProductReorderController::class)->name('products.reorder');
    Route::apiResource('products', ProductController::class);

    Route::post('products/{product}/clone', ProductCloneController::class)->name('products.clone');

    Route::get('orders/top', [OrderController::class, 'top'])->name('orders.top');
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('waitlist', [WaitlistController::class, 'index'])->name('waitlist.index');
    Route::patch('waitlist/{waitlistEntry}/notified', [WaitlistController::class, 'notified'])->name('waitlist.notified');
    Route::delete('waitlist/{waitlistEntry}', [WaitlistController::class, 'destroy'])->name('waitlist.destroy');

    Route::post('products/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store');
    Route::post('products/{product}/images/attach', [ProductImageController::class, 'attach'])->name('products.images.attach');
    Route::post('products/{product}/images/reorder', [ProductImageController::class, 'reorder'])->name('products.images.reorder');
    Route::delete('products/{product}/images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');

    Route::middleware('role:'.UserRole::Superadmin->value.',sanctum')
        ->prefix('admin')
        ->name('admin.')
        ->group(function (): void {
            Route::get('metrics', AdminMetricsController::class)->name('metrics');

            Route::get('stores', [AdminStoreController::class, 'index'])->name('stores.index');
            Route::post('stores', [AdminStoreController::class, 'store'])->name('stores.store');
            Route::get('stores/{store}', [AdminStoreController::class, 'show'])->name('stores.show');
            Route::put('stores/{store}', [AdminStoreController::class, 'update'])->name('stores.update');
            Route::patch('stores/{store}/active', [AdminStoreController::class, 'active'])->name('stores.active');

            Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
            Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
            Route::patch('users/{user}/suspend', [AdminUserController::class, 'suspend'])->name('users.suspend');
        });
});
