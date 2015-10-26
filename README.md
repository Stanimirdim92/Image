# Image - Image manipulation library

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/c3d586f9-1941-40a5-96d7-4189ce47fe24/mini.png)](https://insight.sensiolabs.com/projects/c3d586f9-1941-40a5-96d7-4189ce47fe24)
[![StyleCI](https://styleci.io/repos/42566577/shield)](https://styleci.io/repos/42566577)

<a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-green.svg" alt="Software License"></img></a>
<a href="https://github.com/Stanimirdim92/Image"><img src="https://img.shields.io/badge/release-0.0.4-blue.svg" alt="Latest Version"></img></a>

## Documentation

TBA

## Installation

 - Require via composer
```bash
$ composer require stanimirdim92/image:dev-master
```

## Contributing
Contributions are very welcome.

## License
Image is licensed under [The MIT License (MIT)](LICENSE).

## Usage

Current support is only for GD library

    use Image\Application;

    $options = [
        'png_compression_level'   => 9,
        'png_compression_filter' => "all",
    ];

    $app = new Application(__DIR__."/30b0bo0.png", $options);
    $image = $app->getImage();
    $image->resize(320, 240);
    $image->save('public/images/', "test.png");
