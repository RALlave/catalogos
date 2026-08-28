<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'name',
    'slug',
    'logo_media_id',
    'cover_media_id',
    'palette',
    'radius',
    'nav',
    'banner',
    'hero_effect',
    'description',
    'meta_title',
    'meta_description',
    'industry',
    'whatsapp',
    'phone',
    'email',
    'facebook',
    'instagram',
    'tiktok',
    'website',
    'address',
    'map_url',
    'city',
    'country',
    'currency',
    'schedules',
    'cart_enabled',
    'waitlist_enabled',
    'active',
])]
class Store extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'schedules' => 'array',
            'cart_enabled' => 'boolean',
            'waitlist_enabled' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Media, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function logoMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_media_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    /**
     * @return HasMany<Hero, $this>
     */
    public function heroes(): HasMany
    {
        return $this->hasMany(Hero::class);
    }

    /**
     * Heroes shown in the public catalog, in the order the owner arranged them.
     *
     * @return HasMany<Hero, $this>
     */
    public function activeHeroes(): HasMany
    {
        return $this->heroes()->where('active', true)->orderBy('order')->orderBy('id');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Categories shown in the public catalog.
     *
     * @return HasMany<Category, $this>
     */
    public function activeCategories(): HasMany
    {
        return $this->categories()->where('active', true)->orderBy('order')->orderBy('name');
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<WaitlistEntry, $this>
     */
    public function waitlistEntries(): HasMany
    {
        return $this->hasMany(WaitlistEntry::class);
    }
}
