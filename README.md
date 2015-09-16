# Image
Image manipulation library

Current support is only GD library

## Usage

        $options = [
            'png_compression_level'   => 9,
            'png_compression_filter' => "all",
        ];
        $image = new Image('public/userfiles/2015_Sep/images/30b0bo0.png', $options);
        $image->resize(320, 240);
        $image->save('public/userfiles/2015_Sep/images/', "test.png");
