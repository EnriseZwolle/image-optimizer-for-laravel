<?php

namespace EnriseZwolle\ImageOptimizer\View\Components;

use Illuminate\Contracts\View\View;

class Source extends Image
{
    public function render(): View
    {
        return $this->view('image-optimizer::components.source', [
            'imageSource' => $this->getImageUrl(),
        ]);
    }
}
