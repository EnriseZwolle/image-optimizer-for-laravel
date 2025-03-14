# Image optimizer for laravel

This package allows you to easily optimize images with the power of components, helper methods and facades.
It allows you to easily scale images down, compress images or convert it to the webp format for performance.

Optimized images are automatically stored in a storage disk. For this you can use the default disk or use a 
pre-existing one. When loading an image it will first try to read the file from the storage. If a cached version 
does not exist it will generate a new image.

## Installation

You can install the package via composer:

```bash
composer require enrisezwolle/image-optimizer-for-laravel
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="image-optimizer-for-laravel-config"
```

This is the contents of the published config file:

```php
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
    'driver' => 'gd',

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
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="image-optimizer-for-laravel-views"
```

## Usage

There are multiple ways to use this package.

### View Components

This package comes with two components out of the box.

Both components accept the following 4 parameters

- src: The path to the image. This can be a relative or an absolute path.
- Quality: Optional - Default quality of 80 
- width: Optional - The minimum width of the image. It will pick the closest dimension set in the config
- webp: Optional - Determines if the image should be converted to the webp format

#### Image

```bladehtml
<x-image-optimizer::image
    src="images/image.jpg"
    width="200"
    :webp="true"
/>
```

#### Source

```bladehtml
<picture>
    <x-image-optimizer::source 
        src="images/image.jpg" 
        width="600"
        :webp="true" 
        media="(min-width: 1500px)" 
    />
</picture>
```

### Facade

This package also comes with a facade if you would rather write your own implementation.

```php
use EnriseZwolle\ImageOptimizer\Facades\ImageOptimizer

// Generate an optimized image
ImageOptimizer::getImage(
    src: 'image.jpg',
    quality: 65,
    width: null,
    webp: false 
);

// Clear the cache
// Caution - this deletes EVERY file in the configured storage drive
ImageOptimizer::clearCache();
```

### Helper

This package also supplies a handy helpers to optimize your images.

```php
optimize_image(
    src: 'image.jpg',
    width: 500,
    quality: 70,
    webp: false,  
)
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

