<?php

namespace EnriseZwolle\ImageOptimizer\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string getImage(string $src, int $quality = 80, ?int $width = null, bool $webp = false)
 * @method static void clearCache()
 *
 * @see \EnriseZwolle\ImageOptimizer\ImageOptimizer
 */
class ImageOptimizer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \EnriseZwolle\ImageOptimizer\ImageOptimizer::class;
    }
}
