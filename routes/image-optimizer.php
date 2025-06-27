<?php

use Illuminate\Support\Facades\Route;
use EnriseZwolle\ImageOptimizer\Http\Controllers\GenerateImageController;

Route::get('/image-optimizer/generate/{hash}', GenerateImageController::class)->name('image-optimizer.generate');
