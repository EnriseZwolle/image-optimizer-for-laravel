<?php

namespace EnriseZwolle\ImageOptimizer\Commands;

use EnriseZwolle\ImageOptimizer\Facades\ImageOptimizer;
use Illuminate\Console\Command;

class ImageOptimizerClearCacheCommand extends Command
{
    public $signature = 'image-optimizer:clear-cache';

    public $description = 'This removes all files in the configured drive. Use with caution.';

    public function handle(): int
    {
        $confirmed = $this->confirm('Are you sure you want to clear the cache? This will remove ALL files in the configured drive.', false);

        if ($confirmed) {
            ImageOptimizer::clearCache();
        }

        return self::SUCCESS;
    }
}
