<?php

/**
 * MIT License
 * ===========.
 *
 * Copyright (c) 2015 Stanimir Dimitrov <stanimirdim92@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author     Stanimir Dimitrov <stanimirdim92@gmail.com>
 * @copyright  2015 (c) Stanimir Dimitrov.
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 *
 * @version    0.0.3
 *
 * @link       TBA
 */

namespace Image\Entity;

use Image\Entity\GD as ExtendedGD;

/**
 * TODO: improve interfaces
 * TODO: LSP
 * TODO: crop
 * TODO: copy
 * TODO: thumbnail
 * TODO: filters.
 */
final class Image implements ImageInterface
{
    /**
     * A valid image path /path/to/image.png.
     *
     * @var string
     */
    private $imageFile = null;

    /**
     * Image format, taken from the mime type.
     *
     * @var string
     */
    private $format = null;

    /**
     * The current dimensions of the image.
     *
     * @var array
     */
    private $currentDimensions = ['width' => 1, 'height' => 1];

    /**
     * @var int
     */
    private $width = 320;

    /**
     * @var int
     */
    private $height = 270;

    /**
     * All config options for different image formats.
     *
     * @var array
     */
    private $options = [
        'preserve_alpha'          => true,
        'alpha_color_allocate'    => [255, 255, 255],
        'alpha_transperancy'      => 64,
        'png_compression_level'   => -1,
        'png_compression_filter'  => 'all',
        'jpeg_quality'            => 75,
        'foreground'              => [0, 0, 0],

        // still not used anywhere
        'interlace'               => 0,
        'transparency_mask_color' => [0, 0, 0],
    ];

    /**
     * All allowed mime types.
     *
     * @var array
     */
    private $allowedMimeTypes = [
        'image/gif',
        'image/jpeg',
        'image/png',
        'image/bmp',
        'image/webp',
        'image/wbmp',
    ];

    /**
     * PNG compression filters.
     *
     * @var array
     */
    private $pngFilterTypes = [
        'no'    => PNG_NO_FILTER,
        'none'  => PNG_FILTER_NONE,
        'sub'   => PNG_FILTER_SUB,
        'up'    => PNG_FILTER_UP,
        'avg'   => PNG_FILTER_AVG,
        'paeth' => PNG_FILTER_PAETH,
        'all'   => PNG_ALL_FILTERS,
    ];

    /**
     * The GD library.
     *
     * @var GD
     */
    private $gd = null;

    /**
     * @param resource $imageFile
     * @param array    $options
     */
    public function __construct($imageFile, array $options = [])
    {
        /*
         * Return early for invalid images
         */
        if (!is_file($imageFile)) {
            throw new \InvalidArgumentException('Invalid image');
        }

        $this->imageFile = $imageFile;
        $this->gd = new ExtendedGD();

        $this->setOptions($options);
        $this->extractImageFormat();
        $this->createImageFromFormat();

        $this->currentDimensions = [
            'width'  => imagesx($this->getImageFile()),
            'height' => imagesy($this->getImageFile()),
        ];
    }

    /**
     * Free up memory.
     */
    public function __destruct()
    {
        if (is_resource($this->getImageFile())) {
            imagedestroy($this->getImageFile());
        }
    }

    /**
     * @return resource
     */
    private function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * Returns the format.
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * The current dimensions of the image.
     *
     * @return array
     */
    public function getCurrentDimensions()
    {
        return $this->currentDimensions;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Holds all config data for all methods.
     *
     * @param array $options
     *
     * @return Image
     */
    private function setOptions(array $options = [])
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Parameter provided to %s must be an array or Traversable',
                __METHOD__
            ));
        }

        if (!empty($options)) {
            foreach ($options as $key => $value) {
                $this->options[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Get all options set.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get an individual option.
     *
     * Keys are normalized to lowercase.
     *
     * Returns null for unfound options
     *
     * @param string $option
     *
     * @return mixed
     */
    public function getOption($option)
    {
        $option = strtolower($option);
        if (array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }

        return;
    }

    /**
     * The function will return false for invalid images.
     *
     * @return array|false
     */
    public function getImageInfo()
    {
        return getimagesize($this->getImageFile());
    }

    /**
     * Prepare new image size.
     *
     * @param int $width
     * @param int $height
     */
    private function checkImageSizes($width = 1, $height = 1)
    {
        if ($height < 1 || $width < 1) {
            throw new \InvalidArgumentException('Image height and width must be at least 1 pixel');
        }

        $this->width = (int) $width;
        $this->height = (int) $height;
    }

    /**
     * Extract the file format by mime-type.
     *
     * @throws Exception for invalid mime-types
     */
    private function extractImageFormat()
    {
        $format = $this->getImageInfo();

        if (!in_array($format['mime'], $this->allowedMimeTypes)) {
            throw new \RuntimeException('Unsupported image format');
        }

        /*
         * strip out image/ from string and make the rest upper cases
         */
        $format = strtoupper(substr($format['mime'], 6));

        /*
         * Normalize formats
         */
        if ($format === 'JPG' || $format === 'PJPEG') {
            $format = 'JPEG';
        }

        if ($format === 'VND.WAP.WBMP') {
            $format = 'WBMP';
        }

        $this->format = $format;
    }

    /**
     * Try to create a new image from the supplied file.
     *
     * @throws Exception on invalid image format
     */
    private function createImageFromFormat()
    {
        switch ($this->getFormat()) {
            case 'GIF':
                $this->imageFile = $this->imageCreateFromGIF();
                break;

            case 'JPEG':
                $this->imageFile = $this->imageCreateFromJPEG();
                break;

            case 'PNG':
                $this->imageFile = $this->imageCreateFromPNG();
                break;

            case 'WEBP':
                $this->imageFile = $this->imageCreateFromWEBP();
                break;

            default:
                throw new \RuntimeException('Invalid image format');
                break;
        }
    }

    /**
     * See if we can create GIF images.
     *
     * @throws Exception on missing support
     */
    private function imageCreateFromGIF()
    {
        if ($this->gd->hasGIFCreateSupport()) {
            return imagecreatefromgif($this->getImageFile());
        }

        throw new \Exception('Missing GIF create support');
    }

    /**
     * See if we can create JEPG|JPG images.
     *
     * @throws Exception on missing support
     */
    private function imageCreateFromJPEG()
    {
        if ($this->gd->hasJPEGSupport()) {
            return imagecreatefromjpeg($this->getImageFile());
        }

        throw new \Exception('Missing JPEG support');
    }

    /**
     * See if we can create PNG images.
     *
     * @throws Exception on missing support
     */
    private function imageCreateFromPNG()
    {
        if ($this->gd->hasPNGSupport()) {
            return imagecreatefrompng($this->getImageFile());
        }

        throw new \Exception('Missing PNG support');
    }

    /**
     * See if we can create WEBP images.
     *
     * @throws Exception on missing support
     */
    private function imageCreateFromWEBP()
    {
        if ($this->gd->hasJPEGSupport() || $this->gd->hasPNGSupport()) {
            if (function_exists('imagecreatefromwebp')) {
                return imagecreatefromwebp($this->getImageFile());
            }
        }

        throw new \Exception('Missing WEBP support');
    }

    /**
     * Generates a GD image.
     *
     * @return resource
     */
    private function generateImage()
    {
        $resource = imagecreatetruecolor($this->getWidth(), $this->getHeight());

        imagealphablending($resource, $this->getOption('preserve_alpha'));
        imagesavealpha($resource, true);

        if (function_exists('imageantialias')) {
            imageantialias($resource, true);
        }

        $color = $this->getOption('alpha_color_allocate');

        if (!is_array($color)) {
            $color = [255, 255, 255];
        }

        $alpha = $this->getOption('alpha_transperancy');

        if ($alpha < 1 || $alpha > 127) {
            $alpha = 64;
        }

        $transparentColor = imagecolorallocatealpha($resource, $color[0], $color[1], $color[2], $alpha);
        imagefill($resource, 0, 0, $transparentColor);
        imagecolortransparent($resource, $transparentColor);

        return $resource;
    }

    /**
     * Create the image with the given width and height.
     *
     * @param int width
     * @param int height
     *
     * @throws RuntimeException on invalid operation
     *
     * @return Image
     */
    public function resize($width = 1, $height = 1)
    {
        $this->checkImageSizes($width, $height);

        $oldImageDimensions = $this->getCurrentDimensions();
        $newImage = $this->generateImage();

        imagealphablending($this->getImageFile(), true);
        imagealphablending($newImage, true);

        if (!imagecopyresampled($newImage, $this->getImageFile(), 0, 0, 0, 0, $this->getWidth(), $this->getHeight(), $oldImageDimensions['width'], $oldImageDimensions['height'])) {
            throw new \RuntimeException('Image resizing has failed');
        }

        imagealphablending($this->getImageFile(), false);
        imagealphablending($newImage, false);
        imagedestroy($this->getImageFile());

        $this->imageFile = $newImage;

        return $this;
    }

    /**
     * @param string $path
     * @param string $fileName
     *
     * @throws RuntimeException if path or image name is not set
     */
    public function save($path, $fileName)
    {
        if (!$path || !is_dir($path)) {
            throw new \RuntimeException('Path is not set');
        }

        if (!is_writable($path)) {
            throw new \RuntimeException('Path is not writable');
        }

        if (!$fileName) {
            throw new \RuntimeException('Image name is not set');
        }

        $format = strtolower($this->getFormat());
        $imageSaveMethod = 'image'.$format;
        $options = [$this->getImageFile(), $path.DIRECTORY_SEPARATOR.$fileName];

        $formatOptions = $this->checkFormatOptions();
        foreach ($formatOptions as $key => $value) {
            $options[] = $value;
        }

        if (!call_user_func_array($imageSaveMethod, $options)) {
            throw new RuntimeException('Save operation failed');
        }
    }

    /**
     * @return array
     */
    private function checkFormatOptions()
    {
        $params = [];
        $format = strtolower($this->getFormat());

        if ($format === 'png') {
            $png = $this->pngOptions();
            $params[] = $png['level'];
            $params[] = $png['filter'];
        } elseif ($format === 'jpeg') {
            $params[] = $this->jpegOptions();
        } elseif ($format === 'wbmp') {
            $params[] = $this->wbmpOptions();
        }

        return $params;
    }

    /**
     * @return array
     */
    private function pngOptions()
    {
        $params = [];
        $allowedFilters = array_keys($this->pngFilterTypes);
        $params['level'] = $this->getOption('png_compression_level');
        $filter = $this->getOption('png_compression_filter');

        if ($params['level'] < 0 || $params['level'] > 9) {
            // http://www.zlib.net/manual.html
            $params['level'] = -1; // Z_DEFAULT_COMPRESSION
        }
        $this->options['png_compression_level'] = $params['level'];

        if (!is_string($filter)) {
            throw new \RuntimeException('png_compression_filter must be a string '.gettype($filter).' given');
        }

        if (!in_array($filter, $allowedFilters)) {
            throw new \RuntimeException(sprintf('png_compression_filter should be one or a combination of: "%s"', implode('", "', $allowedFilters)));
        }
        $params['filter'] = $this->pngFilterTypes[$filter];

        return $params;
    }

    /**
     * @return int
     */
    private function jpegOptions()
    {
        $jpegQuality = $this->getOption('jpeg_quality');

        if ($jpegQuality < 0 || $jpegQuality > 100) {
            $jpegQuality = 75;
        }

        $this->options['jpeg_quality'] = $jpegQuality;

        return $jpegQuality;
    }

    /**
     * @return array
     */
    private function wbmpOptions()
    {
        $foreground = $this->getOption('foreground');

        if (!is_array($foreground)) {
            $foreground = [0, 0, 0];
        }

        $this->options['foreground'] = $foreground;

        return $foreground;
    }
}
