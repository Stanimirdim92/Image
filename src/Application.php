<?php

/**
 * @copyright  2015 (c) Stanimir Dimitrov.
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 *
 * @version    0.0.4
 *
 * @link       TBA
 */

namespace Image;

use Image\Image\Image;

class Application
{
    /**
     * @var Image
     */
    private $image;

    public function __construct($imageFile, array $options = [])
    {
        $this->image = new Image($imageFile, $options);
    }

    /**
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }
}
