<?php

namespace EnriseZwolle\ImageOptimizer\Http\Controllers;

use Exception;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\HttpFoundation\StreamedResponse;
use EnriseZwolle\ImageOptimizer\Facades\ImageOptimizer;
use EnriseZwolle\ImageOptimizer\Http\Requests\GenerateImageRequest;

class GenerateImageController
{
    public function __invoke(GenerateImageRequest $request, string $hash)
    {
        // Check if the cached file exists
        if ($cachedImage = ImageOptimizer::getCachedImage($request->getImageData(), true)) {
            return $this->getStreamedResponse($cachedImage);
        }

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

    public function getStreamedResponse(string $path): StreamedResponse
    {
        $filename = basename($path);
        $mime = (new MimeTypes())->guessMimeType($path);

        return response()->stream(
            fn () => readfile($path),
            200,
            [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=86400',
                'Content-Disposition' => `inline; filename="{$filename}"`,
            ]
        );
    }
}
