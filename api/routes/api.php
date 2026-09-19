<?php

use App\Enums\UserRole;
use App\Http\Controllers\Api\Admin\BackupController as AdminBackupController;
use App\Http\Controllers\Api\Admin\ImpersonationController as AdminImpersonationController;
use App\Http\Controllers\Api\Admin\MetricsController as AdminMetricsController;
use App\Http\Controllers\Api\Admin\PlatformLogoController as AdminPlatformLogoController;
use App\Http\Controllers\Api\Admin\PlatformStoreLogoController as AdminPlatformStoreLogoController;
use App\Http\Controllers\Api\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Api\Admin\StoreController as AdminStoreController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Auth\HandoffController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CategoryReorderController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\Public\OrderController as PublicOrderController;
use App\Http\Controllers\Api\Public\PlatformController;
use App\Http\Controllers\Api\Public\PlatformIconController;
use App\Http\Controllers\Api\Public\ProductController as PublicProductController;
use App\Http\Controllers\Api\Public\StoreController as PublicStoreController;
use App\Http\Controllers\Api\Public\ThemeController;
use App\Http\Controllers\Api\Public\TrackController as PublicTrackController;
use App\Http\Controllers\Api\Public\WaitlistController as PublicWaitlistController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\HeroCloneController;
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
use App\Http\Controllers\Api\StoreLogoController;
use App\Http\Controllers\Api\WaitlistController;
use Illuminate\Support\Facades\Route;

Route::post('register', RegisterController::class)->middleware('throttle:10,1');

Route::post('login', [LoginController::class, 'login'])->middleware('throttle:6,1');

Route::post('forgot-password', [PasswordResetController::class, 'forgot'])->middleware('throttle:6,1');

Route::post('reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1');

/* Canje del código con el que una sesión salta de un subdominio a otro. No
   lleva auth: el código es la credencial, dura un minuto y sirve una vez. */
Route::post('auth/handoff/redeem', [HandoffController::class, 'redeem'])
    ->middleware('throttle:10,1')
    ->name('auth.handoff.redeem');

Route::get('themes', ThemeController::class)->name('public.themes');

/* La marca de la plataforma la piden las pantallas de acceso, antes de que
   exista una sesión: no puede ir detrás de auth. */
Route::get('platform', PlatformController::class)->name('public.platform');

/* El ícono de la app instalada, por medida. Lo pide el manifest del panel, que
   es un archivo estático y no puede nombrar el archivo subido. */
Route::get('platform/icon/{size}', PlatformIconController::class)
    ->whereIn('size', ['192', '512'])
    ->name('public.platform.icon');

Route::get('stores/{slug}', [PublicStoreController::class, 'show'])->name('public.store.show');
Route::get('stores/{slug}/products', [PublicProductController::class, 'index'])->name('public.products.index');
/* La vitrina del home: los cinco de arriba, no una página del listado. */
Route::get('stores/{slug}/featured', [PublicProductController::class, 'featured'])->name('public.products.featured');
/* The offers below the contact page: three at random. */
Route::get('stores/{slug}/offers', [PublicProductController::class, 'offers'])->name('public.products.offers');
Route::get('stores/{slug}/products/{productSlug}', [PublicProductController::class, 'show'])->name('public.products.show');

/* Los dos son anónimos y escriben en la base: van con límite de peticiones. */
Route::post('stores/{slug}/orders', [PublicOrderController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('public.orders.store');
Route::post('stores/{slug}/waitlist', [PublicWaitlistController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('public.waitlist.store');

/* Lo que el visitante hace en el navegador y la API no ve: el botón compartir. */
Route::post('stores/{slug}/track', PublicTrackController::class)
    ->middleware('throttle:30,1')
    ->name('public.track');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('me', [LoginController::class, 'me']);
    Route::post('logout', [LoginController::class, 'logout']);

    Route::post('auth/handoff', [HandoffController::class, 'issue'])
        ->middleware('throttle:20,1')
        ->name('auth.handoff.issue');

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

    /* Los logos que dejó el superadmin y el que la tienda elige de esa lista.
       Elegirlo copia la imagen a la biblioteca de la tienda. */
    Route::get('store-logos', [StoreLogoController::class, 'index'])->name('store-logos.index');
    Route::post('store/logo/platform', [StoreImageController::class, 'platform'])
        ->name('store.logo.platform');

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
    Route::post('media/{media}/crop', [MediaController::class, 'crop'])->name('media.crop');

    Route::post('heroes/reorder', HeroReorderController::class)->name('heroes.reorder');
    Route::post('heroes/{hero}/clone', HeroCloneController::class)->name('heroes.clone');
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
    Route::put('products/{product}/images/{image}', [ProductImageController::class, 'update'])->name('products.images.update');
    Route::delete('products/{product}/images/{image}',[ProductImageController::class, 'destroy'])->name('products.images.destroy');

    Route::middleware('role:'.UserRole::Superadmin->value.',sanctum')
        ->prefix('admin')
        ->name('admin.')
        ->group(function (): void {
            Route::get('metrics', AdminMetricsController::class)->name('metrics');

            /* Tres logos: el de las pantallas de acceso, el de la barra
               lateral y el ícono cuadrado de la app. Cada ruta fija el suyo,
               como el logo y la portada de una tienda. */
            Route::post('platform/logo/auth', [AdminPlatformLogoController::class, 'store'])
                ->defaults('variant', 'auth')
                ->name('platform.logo.auth.store');
            Route::delete('platform/logo/auth', [AdminPlatformLogoController::class, 'destroy'])
                ->defaults('variant', 'auth')
                ->name('platform.logo.auth.destroy');

            Route::post('platform/logo/panel', [AdminPlatformLogoController::class, 'store'])
                ->defaults('variant', 'panel')
                ->name('platform.logo.panel.store');
            Route::delete('platform/logo/panel', [AdminPlatformLogoController::class, 'destroy'])
                ->defaults('variant', 'panel')
                ->name('platform.logo.panel.destroy');

            Route::post('platform/logo/icon', [AdminPlatformLogoController::class, 'store'])
                ->defaults('variant', 'icon')
                ->name('platform.logo.icon.store');
            Route::delete('platform/logo/icon', [AdminPlatformLogoController::class, 'destroy'])
                ->defaults('variant', 'icon')
                ->name('platform.logo.icon.destroy');

            /* La galería de logos para las tiendas: son muchos y se listan, al
               revés de los tres de arriba, que son fijos. */
            Route::get('platform/store-logos', [AdminPlatformStoreLogoController::class, 'index'])
                ->name('platform.store-logos.index');
            Route::post('platform/store-logos', [AdminPlatformStoreLogoController::class, 'store'])
                ->name('platform.store-logos.store');
            Route::put('platform/store-logos/{platformLogo}', [AdminPlatformStoreLogoController::class, 'update'])
                ->name('platform.store-logos.update');
            Route::patch('platform/store-logos/{platformLogo}/default', [AdminPlatformStoreLogoController::class, 'markDefault'])
                ->name('platform.store-logos.default');
            Route::delete('platform/store-logos/{platformLogo}', [AdminPlatformStoreLogoController::class, 'destroy'])
                ->name('platform.store-logos.destroy');

            Route::get('stores', [AdminStoreController::class, 'index'])->name('stores.index');
            Route::post('stores', [AdminStoreController::class, 'store'])->name('stores.store');
            Route::get('stores/{store}', [AdminStoreController::class, 'show'])->name('stores.show');
            Route::put('stores/{store}', [AdminStoreController::class, 'update'])->name('stores.update');
            Route::patch('stores/{store}/active', [AdminStoreController::class, 'active'])->name('stores.active');
            Route::post('stores/{store}/impersonate', AdminImpersonationController::class)->name('stores.impersonate');

            /* Papelera: mover y restaurar. El DELETE es el borrado sin rastro y
               sólo acepta tiendas que ya están en la papelera. */
            Route::patch('stores/{store}/trash', [AdminStoreController::class, 'trash'])->name('stores.trash');
            Route::patch('stores/{store}/restore', [AdminStoreController::class, 'restore'])->name('stores.restore');
            Route::delete('stores/{store}', [AdminStoreController::class, 'destroy'])->name('stores.destroy');

            Route::get('settings', [AdminSettingController::class, 'show'])->name('settings.show');
            Route::put('settings', [AdminSettingController::class, 'update'])->name('settings.update');

            Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
            Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
            Route::patch('users/{user}/suspend', [AdminUserController::class, 'suspend'])->name('users.suspend');

            /* Respaldo de la plataforma entera. El GET descarga y el POST del
               mismo camino restaura: son la misma cosa en los dos sentidos. */
            Route::get('backup/database', [AdminBackupController::class, 'database'])->name('backup.database');
            Route::post('backup/database', [AdminBackupController::class, 'restoreDatabase'])->name('backup.database.restore');
            Route::get('backup/files', [AdminBackupController::class, 'files'])->name('backup.files');
            Route::post('backup/files', [AdminBackupController::class, 'restoreFiles'])->name('backup.files.restore');
        });
});
