<?php

namespace EnriseZwolle\ImageOptimizer\Facades;

use Illuminate\Support\Facades\Facade;
use EnriseZwolle\ImageOptimizer\DataObjects\ImageData;

/**
 * @method static string getUrl(string $src, int $quality = 80, ?int $width = null, bool $webp = false)
 * @method static string getImage(string $src, int $quality = 80, ?int $width = null, bool $webp = false)
 * @method static string|null getCachedImage(ImageData $imageData, bool $relative = false)
 * @method static string decodePath(string $path)
 * @method static string encodePath(string $path)
 * @method static ImageData getImageData(string $src, int $quality, ?int $width, bool $webp)
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
