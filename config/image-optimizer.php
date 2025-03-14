<?php

// config for EnriseZwolle/ImageOptimizer
return [
    'disk' => [
        /*
         * Set te disk name. You can use your own or register a new one.
         */
        'name' => 'image-optimizer',

        /*
         * Config can be null when using an existing disk name.
         * When disk has not yet been registered image-optimizer will register a new disk using this config.
         */
        'config' => [
            'driver' => 'local',
            'root' => storage_path('app/image-optimizer'),
            'url' => env('APP_URL').'/optimizer',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        /*
         * To access images stored in the drive registering storage links might be necessary.
         * This can be null when using a pre-existing disk.
         * Please run `php artisan storage:link` to create the symlink in the public directory.
         */
        'links' => [
            public_path('optimizer') => storage_path('app/image-optimizer'),
        ],
    ],

    /*
     * The driver that will be used to create images. Can be set to gd or imagick.
     */
    'driver' => 'gdd',

    /*
     * The widths an image is stored in.
     * Image optimizer will only use these image widths.
     * When optimizer is given a width not in this list it will scale up to the next available width.
     * For example: when given a width of 320 it will create an image width a width of 500.
     */
    'dimensions' => [
        100,
        250,
        300,
        500,
        750,
        1000,
        1500,
        2000,
    ],

    /*
     * When set to true images will be constrained to the maximum width defined in 'dimensions'.
     */
    'limit-width' => true,
];
