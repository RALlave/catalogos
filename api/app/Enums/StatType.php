<?php

namespace App\Enums;

enum StatType: string
{
    /** Someone opened the store catalog. */
    case Visit = 'visit';

    /** Someone opened a product detail page. */
    case ProductView = 'product_view';

    /** Someone pressed the share button on a product. */
    case Share = 'share';
}
