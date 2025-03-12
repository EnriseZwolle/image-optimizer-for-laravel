<?php

namespace EnriseZwolle\ImageOptimizer\Commands;

use Illuminate\Console\Command;

class ImageOptimizerCommand extends Command
{
    public $signature = 'image-optimizer-for-laravel';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
