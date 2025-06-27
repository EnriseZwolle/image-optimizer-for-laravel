<?php

namespace EnriseZwolle\ImageOptimizer\Http\Requests;

use EnriseZwolle\ImageOptimizer\Facades\ImageOptimizer;
use Illuminate\Foundation\Http\FormRequest;

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
}
