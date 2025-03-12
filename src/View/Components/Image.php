<?php

namespace EnriseZwolle\ImageOptimizer\View\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Image extends Component
{
    public function render(): View
    {
        return $this->view('image-optimizer::components.image');
    }
}
