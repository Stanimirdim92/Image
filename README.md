# Image - Image manipulation library

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/c3d586f9-1941-40a5-96d7-4189ce47fe24/mini.png)](https://insight.sensiolabs.com/projects/c3d586f9-1941-40a5-96d7-4189ce47fe24)
[![StyleCI](https://styleci.io/repos/42566577/shield?style=flat&source=github)](https://styleci.io/repos/42566577)

<a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-green.svg" alt="Software License"></img></a>
<a href="https://github.com/Stanimirdim92/Image"><img src="https://img.shields.io/badge/release-0.0.4-blue.svg" alt="Latest Version"></img></a>

## Documentation

TBA

## License

Image is licensed under [The MIT License (MIT)](LICENSE).


Current support is only GD library

## Usage

        $options = [
            'png_compression_level'   => 9,
            'png_compression_filter' => "all",
        ];
        $image = new Image('public/userfiles/2015_Sep/images/30b0bo0.png', $options);
        $image->resize(320, 240);
        $image->save('public/userfiles/2015_Sep/images/', "test.png");
