<?php

namespace EnriseZwolle\ImageOptimizer\Http\Controllers;

use EnriseZwolle\ImageOptimizer\Facades\ImageOptimizer;
use EnriseZwolle\ImageOptimizer\Http\Requests\GenerateImageRequest;
use Exception;

class GenerateImageController
{
    public function __invoke(GenerateImageRequest $request, string $hash)
    {
        try {
            $image = ImageOptimizer::getImage(
                $request->getImagePath(),
                $request->getQuality(),
                $request->getWidth(),
                $request->isWebp()
            );

            if (file_exists($image)) {
                return response()->stream(function () use ($image) {
                    readfile($image);
                });
            }
        } catch (Exception $exception) {
            abort(404);
        }

        abort(404);
    }
}
