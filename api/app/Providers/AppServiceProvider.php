<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Hero;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Store;
use App\Models\User;
use App\Observers\CatalogCacheObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (User $user, string $token): string {
            return config('app.frontend_url').'/reset-password?token='.$token.'&email='.urlencode($user->email);
        });

        /* Todo lo que se ve en el catálogo público tira su caché al guardarse. */
        foreach ([Category::class, Hero::class, Media::class, Product::class, ProductImage::class, Store::class] as $model) {
            $model::observe(CatalogCacheObserver::class);
        }
    }
}
