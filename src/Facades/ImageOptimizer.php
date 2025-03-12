<?php

namespace EnriseZwolle\ImageOptimizer\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \EnriseZwolle\ImageOptimizer\ImageOptimizer
 */
class ImageOptimizer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \EnriseZwolle\ImageOptimizer\ImageOptimizer::class;
    }
}
