<?php

use EnriseZwolle\ImageOptimizer\Facades\ImageOptimizer;

if (!function_exists('optimize_image')) {
    function optimize_image(string $src, int $quality = 80, ?int $width = null, bool $webp = false): string
    {
        if (filled($src)) {
            return ImageOptimizer::getUrl($src, $quality, $width, $webp);
        }

        return $src;
    }
}
