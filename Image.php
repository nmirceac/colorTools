<?php

require_once('Color.php');
require_once('Image.php');
require_once('Palette.php');

class Image
{
    private $image = NULL;

    private $imageObject = NULL;

    public function __construct($image)
    {
        if (!file_exists($image)) {
            throw new \Exception('Invalid filename');
        }

        if (filesize($image) <= 11) {
            throw new \Exception('This is too small to be an image');
        }

        $this->image = $image;

        $this->getImageDetails();
    }

    private function getImageDetails()
    {
        $size = getimagesize($this->image);
        if (empty($size)) {
            throw new \Exception('This is not an image');
        }

        $this->type = substr($size['mime'], 6);
        $this->mime = $size['mime'];
        $this->width = $size[0];
        $this->height = $size[1];
    }

    public function getImageObject()
    {
        if(is_null($this->imageObject)) {
            $this->imageObject = call_user_func('imagecreatefrom' . $this->type, $this->image);
        }

        return $this->imageObject;
    }

    public function getColors($palette = null, $comparisonType = null, $precision = null, $minCoverage = null)
    {
        $palette = (is_null($palette)) ? Palette::PALETTE_BRIAN_MCDO : $palette;
        $precision = (is_null($precision)) ? Palette::ADAPTIVE_PRECISION : $precision;
        $comparisonType = (is_null($comparisonType)) ? Color::COMPARE_GREAT : $comparisonType;
        $minCoverage = (is_null($minCoverage)) ? Palette::DEFAULT_MIN_COVERAGE : $minCoverage;

        $palette = new Palette($palette, $comparisonType);
        return $palette->getColors($this, $precision, $minCoverage);
    }
}

