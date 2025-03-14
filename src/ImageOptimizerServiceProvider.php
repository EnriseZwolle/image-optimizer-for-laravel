<?php

namespace EnriseZwolle\ImageOptimizer;

use EnriseZwolle\ImageOptimizer\Commands\ImageOptimizerClearCacheCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ImageOptimizerServiceProvider extends PackageServiceProvider
{

    public function boot(): void
    {
        parent::boot();

        $this->registerDisk()
            ->registerComponentNamespace();
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('image-optimizer')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(ImageOptimizerClearCacheCommand::class);
    }

    protected function registerComponentNamespace(): self
    {
        Blade::componentNamespace('EnriseZwolle\\ImageOptimizer\\View\\Components', 'image-optimizer');

        return $this;
    }

    protected function registerDisk(): self
    {
        $diskName = Config::get('image-optimizer.disk.name');
        $diskConfig = Config::get('image-optimizer.disk.config');
        $storageLinks = Config::get('image-optimizer.disk.links');

        if (! Config::get('filesystems.disks.' . $diskName) && filled($diskConfig)) {
            Config::set('filesystems.disks.' . $diskName, $diskConfig);

            if (filled($storageLinks) && is_array($storageLinks)) {
                $links = Config::get('filesystems.links', []) + $storageLinks;

                Config::set('filesystems.links', $links);
            }
        }

        return $this;
    }
}
