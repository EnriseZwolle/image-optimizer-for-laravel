<?php

namespace EnriseZwolle\ImageOptimizer\View\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use EnriseZwolle\ImageOptimizer\Facades\ImageOptimizer;

class Image extends Component
{
    public bool $hasValidSrc;

    public function __construct(
        private string $src,
        public int $quality = 80,
        public ?int $width = null,
        public bool $webp = false,
    )
    {
        $this->hasValidSrc = filled($this->src);
    }

    protected function getImageUrl(): string
    {
        if (! $this->hasValidSrc) {
            return $this->src;
        }

        return ImageOptimizer::getUrl($this->src, $this->quality, $this->width, $this->webp);
    }

    public function render(): View
    {
        return $this->view('image-optimizer::components.image', [
            'imageSource' => $this->getImageUrl(),
        ]);
    }
}
