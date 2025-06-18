<?php

namespace EnriseZwolle\ImageOptimizer\DataObjects;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class ImageData
{
    public function __construct(
        public string $src,
        public string $originalFilename,
        public string $originalExtension,
        public string $uniqueIdentifier,
        public string $uniqueFilename,
        public int $quality = 80,
        public ?int $width = null,
        public bool $webp = false,
    )
    {}
}
