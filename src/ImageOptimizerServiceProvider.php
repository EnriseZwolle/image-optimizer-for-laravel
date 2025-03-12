<?php

namespace EnriseZwolle\ImageOptimizer;

use EnriseZwolle\ImageOptimizer\View\Components\Image;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use EnriseZwolle\ImageOptimizer\Commands\ImageOptimizerCommand;

class ImageOptimizerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('image-optimizer-for-laravel')
            ->hasConfigFile()
            ->hasViews('image-optimizer')
            ->hasViewComponents('image-optimizer', Image::class)
            ->hasMigration('create_image_optimizer_for_laravel_table')
            ->hasCommand(ImageOptimizerCommand::class);
    }
}
