<?php

namespace EnriseZwolle\ImageOptimizer;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use EnriseZwolle\ImageOptimizer\DataObjects\ImageData;
use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Intervention\Image\ImageManagerStatic as ImageManager;

class ImageOptimizer {
    public function getImage(
        string $src,
        int $quality = 80,
        ?int $width = null,
        bool $webp = false,
    ): string
    {
        // Do not attempt to modify svg images
        if ($this->isSvg($src)) {
            return $src;
        }

        // Transform to image data
        $imageData = $this->getImageData($src, $quality, $width, $webp);

        // Check if the cached file exists
        if ($cachedUrl = $this->getCachedImage($imageData)) {
            return $cachedUrl;
        }

        try {
            // Convert url to Intervention image and process options
            $image = $this->loadImage($imageData->url);

            $this->processImage($image, $imageData);

            // Encode the image and apply quality
            $encodedImage = $this->encodeImage($image, $imageData);

            // Attempt to write image to disk and return the public url
            if (Storage::disk($this->getDisk())->put($imageData->uniqueFilename, $encodedImage)) {
                return Storage::disk($this->getDisk())->url($imageData->uniqueFilename);
            }

            // When image could not be stored to disk return base image
            return $src;
        } catch (Exception $exception) {
            report($exception);

            // When image could not be processed return the base image
            return $src;
        }
    }

    public function clearCache(): void
    {
        $path = Storage::disk($this->getDisk())->path('');

        $filesystem = app(Filesystem::class);

        $filesystem->cleanDirectory($path);
    }

    public function getDisk(): string
    {
        return Config::get('image-optimizer.disk.name');
    }

    protected function isSvg($src): bool
    {
        return pathinfo($src, PATHINFO_EXTENSION) === 'svg';
    }

    protected function getCachedImage(ImageData $imageData): ?string
    {
        if (Storage::disk($this->getDisk())->exists($imageData->uniqueFilename)) {
            return Storage::disk($this->getDisk())->url($imageData->uniqueFilename);
        }

        return null;
    }

    protected function loadImage(string $url): Image
    {
        $imageData = file_get_contents($url);

        ImageManager::configure(['driver' => $this->getInterventionDriver()]);
        return ImageManager::make($imageData);
    }

    protected function processImage(Image &$image, ImageData $imageData): void
    {
        if ($width = $imageData->width) {
            $this->resizeImage($image, $width);
        }
    }

    protected function resizeImage(Image &$image, int $width): void
    {
        $image->resize($width, null, function (Constraint $constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }

    protected function encodeImage(Image $image, ImageData $imageData): Image
    {
        if ($imageData->webp) {
            return $image->encode('webp', $imageData->quality);
        }

        return $image->encode(quality: $imageData->quality);
    }

    protected function getInterventionDriver(): string
    {
        return match (Config::get('image-optimizer.driver')) {
            'gd' => 'gd',
            'imagick' => 'imagick',
            default => throw new Exception('Invalid image driver, only gd and imagick are supported'),
        };
    }

    protected function getImageData(string $src, int $quality, ?int $width, bool $webp): ImageData
    {
        $filename = pathinfo($src, PATHINFO_BASENAME);
        $extension = $webp ? 'webp' : pathinfo($src, PATHINFO_EXTENSION);

        $url = $this->isRelativePath($src) ? url($src) : $src;

        $dimensionalWidth = $this->getDimension($width);

        $uniqueIdentifier = implode('_', array_filter([$url, $quality, $dimensionalWidth, $webp]));
        $encryptedFilename = hash('sha256', $uniqueIdentifier);

        return new ImageData(
            originalSrc: $src,
            originalFilename: $filename,
            originalExtension: $extension,
            url: $url,
            uniqueIdentifier: $uniqueIdentifier,
            uniqueFilename: $encryptedFilename . '.' . $extension,
            quality: $quality,
            width: $dimensionalWidth,
            webp: $webp,
        );
    }

    protected function isRelativePath($url): bool
    {
        return ! isset(parse_url($url)['host']);
    }

    protected function getDimension(?int $width): ?int
    {
        if (! filled($width)) {
            return null;
        }

        $dimensions = collect(Config::get('image-optimizer.dimensions', []))->sort();

        if ($dimension = $dimensions->firstWhere(fn($value) => $value >= $width)) {
            return $dimension;
        }

        if (Config::boolean('image-optimizer.limit-width', false)) {
            return $dimensions->last();
        }

        return null;
    }
}
