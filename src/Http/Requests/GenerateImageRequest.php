<?php

namespace EnriseZwolle\ImageOptimizer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use EnriseZwolle\ImageOptimizer\DataObjects\ImageData;
use EnriseZwolle\ImageOptimizer\Facades\ImageOptimizer;

class GenerateImageRequest extends FormRequest
{
    public function getImagePath(): string
    {
        return ImageOptimizer::decodePath($this->route()->parameter('hash'));
    }

    public function getQuality(): int
    {
        return $this->get('quality', 80);
    }

    public function getWidth(): ?int
    {
        return $this->get('width');
    }

    public function isWebp(): bool
    {
        return $this->get('webp', false);
    }

    public function getImageData(): ImageData
    {
        return ImageOptimizer::getImageData($this->getImagePath(), $this->getQuality(), $this->getWidth(), $this->isWebp());
    }
}
