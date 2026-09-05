<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WebP quality
    |--------------------------------------------------------------------------
    |
    | Every upload is converted to WebP. 82 keeps photos clean at a fraction of
    | the original weight; raising it grows the files fast for little gain.
    |
    */

    'quality' => 82,

    /*
    |--------------------------------------------------------------------------
    | Variant sizes
    |--------------------------------------------------------------------------
    |
    | Longest side in pixels. Images are never scaled up: a photo smaller than
    | the target keeps its own size.
    |
    */

    'sizes' => [
        'thumb' => 400,
        'card' => 800,
        'full' => 1600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Profiles
    |--------------------------------------------------------------------------
    |
    | Which variants are generated depends on where the file is uploaded from.
    | Product galleries never show a photo wider than the card, so they skip the
    | big one; everything else may end up in the banner, which is full width.
    |
    | The original file is not kept, so a product image cannot be upscaled
    | later: picking one for the banner stretches its card variant.
    |
    */

    'profiles' => [
        'library' => ['thumb', 'card', 'full'],
        'product' => ['thumb', 'card'],
    ],

];
