<?php

ini_set("display_errors", 1);

/**
 * Fixes files and server encoding.
 */
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');


/**
 * Some server configurations are missing a date timezone and PHP will throw a warning.
 */
if (ini_get('date.timezone') == '') {
    date_default_timezone_set('UTC');
}

/**
 * This makes our life easier when dealing with paths. Everything is relative.
 * to the application root now.
 */
chdir(dirname(__DIR__));

/**
 * Setup autoloading via composer.
 */
require_once '/vendor/autoload.php';

use Image\Application;

$options = [
    'png_compression_level'   => 9,
    'png_compression_filter' => "all",
];

$app = new Application(__DIR__."/30b0bo0.png", $options);
$image = $app->getImage();
$image->resize(320, 240);
$image->save('public/images/', "test.png");
