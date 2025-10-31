<?php

namespace EnriseZwolle\ImageOptimizer;

use Exception;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Drivers\AbstractDriver;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Exceptions\DriverException;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use EnriseZwolle\ImageOptimizer\DataObjects\ImageData;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;

class ImageOptimizer {
    public function getUrl(
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
            return route('image-optimizer.generate', [
                'hash' => $this->encodePath($imageData->src),
                'quality' => $quality,
                'width' => $width,
                'webp' => $webp,
            ]);
        } catch (Exception $exception) {
            return $src;
        }
    }

    public function encodePath(string $path): string
    {
        return rtrim(strtr(base64_encode($path), '+/', '-_'), '=');
    }

    public function decodePath(string $path): string
    {
        $remainder = strlen($path) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $path .= str_repeat('=', $padlen);
        }

        return base64_decode(strtr($path, '-_', '+/'));
    }

    public function getImage(
        string $src,
        int $quality = 80,
        ?int $width = null,
        bool $webp = false,
        bool $relative = false,
    ): string
    {
        // Do not attempt to modify svg images
        if ($this->isSvg($src)) {
            return $src;
        }

        // Transform to image data
        $imageData = $this->getImageData($src, $quality, $width, $webp);

        // Check if the cached file exists
        if ($cachedUrl = $this->getCachedImage($imageData, $relative)) {
            return $cachedUrl;
        }

        try {
            // Convert url to Intervention image and process options
            $image = $this->loadImage($imageData->src);

            // If image could not be loaded return original src
            if (! $image) {
                return $src;
            }

            $this->processImage($image, $imageData);

            // Encode the image and apply quality
            $encodedImage = $this->encodeImage($image, $imageData);

            // Attempt to write image to disk and return the public url
            if (Storage::disk($this->getDisk())->put($imageData->uniqueFilename, $encodedImage)) {
                return Storage::disk($this->getDisk())->path($imageData->uniqueFilename);
            }

            // When image could not be stored to disk return base image
            return $src;
        } catch (DriverException $exception) {
            // Throw exception when driver is configured incorrectly
            throw $exception;
        } catch (Exception $exception) {
            // When image could not be processed return the base image
            return $src;
        }
    }

    public function getCachedImage(ImageData $imageData, bool $relative = false): ?string
    {
        if (Storage::disk($this->getDisk())->exists($imageData->uniqueFilename)) {
            if ($relative) {
                return Storage::disk($this->getDisk())->path($imageData->uniqueFilename);
            }

            return Storage::disk($this->getDisk())->url($imageData->uniqueFilename);
        }

        return null;
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

    protected function loadImage(string $url): ?ImageInterface
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 3, // timeout in seconds
            ]
        ]);

        $imageData = @file_get_contents($url, false, $context);

        if (! $imageData) {
            return null;
        }

        $manager = new ImageManager($this->getInterventionDriver());
        return $manager->read($imageData);
    }

    protected function processImage(ImageInterface &$image, ImageData $imageData): void
    {
        if ($width = $imageData->width) {
            $this->resizeImage($image, $width);
        }
    }

    protected function resizeImage(ImageInterface &$image, int $width): void
    {
        $image->scaleDown(width: $width);
    }

    protected function encodeImage(ImageInterface $image, ImageData $imageData): EncodedImageInterface
    {
        if ($imageData->webp) {
            return $image->encode(new WebpEncoder(quality: $imageData->quality));
        }

        return $image->encode(new AutoEncoder(quality: $imageData->quality));
    }

    protected function getInterventionDriver(): AbstractDriver
    {
        return match (Config::get('image-optimizer.driver')) {
            'gd' => new GdDriver(),
            'imagick' => new ImagickDriver(),
            default => throw new DriverException(),
        };
    }

    public function getImageData(string $src, int $quality, ?int $width, bool $webp): ImageData
    {
        // Encode spaces in url when it isn't a local path
        if (! Str::startsWith($src, '/')) {
            $src = str_replace(' ', '%20', $src);
        }

        // Transform FQN for local files to a relative path
        if (Str::startsWith($src, config('app.url'))) {
            $src = ltrim(Str::after($src, config('app.url')), '/');
        }

        $filename = pathinfo($src, PATHINFO_BASENAME);
        $extension = $webp ? 'webp' : pathinfo($src, PATHINFO_EXTENSION);

        $dimensionalWidth = $this->getDimension($width);

        $uniqueIdentifier = implode('_', array_filter([$src, $quality, $dimensionalWidth, $webp]));
        $encryptedFilename = hash('sha256', $uniqueIdentifier);

        return new ImageData(
            src: $src,
            originalFilename: $filename,
            originalExtension: $extension,
            uniqueIdentifier: $uniqueIdentifier,
            uniqueFilename: $encryptedFilename . '.' . $extension,
            quality: $quality,
            width: $dimensionalWidth,
            webp: $webp,
        );
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
