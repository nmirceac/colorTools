<?php namespace ColorTools;

class Image
{
    private $image = NULL;
    private $imageType = NULL;
    private $imagePath = NULL;

    private $imageObject = NULL;

    public function __construct($image)
    {
        if(gettype($image)=='string')
        {
            if(substr($image, 0, 7)=='http://' or substr($image, 0, 8)=='https://') {
                $this->imageType = 'url';
                $this->imagePath = $image;
                $image = file_get_contents($image);
            } elseif (strlen($image)>255) { //assume it's the content of an image file
                $this->imageType = 'string';
            } else {
                if (!file_exists($image)) {
                    throw new \Exception('Invalid filename');
                }

                if (filesize($image) <= 11) {
                    throw new \Exception('This is too small to be an image');
                }

                $this->imageType = 'file';
                $this->imagePath = $image;
            }
        } else if(gettype($image)=='resource') {
            $resourceType = get_resource_type($image);
            switch ($resourceType) {
                case 'gd' :
                    $this->imageType = 'gd';

                default :
                break;
            }
        } else if(gettype($image)=='object') {
            $class = get_class($image);
            switch ($class) {
                case 'Imagick' :
                    $this->imageType = 'imagick';

                default :
                    break;
            }
        }

        $this->image = $image;
        $this->getImageDetails();
    }

    public function getImageType(){
        return $this->imageType;
    }

    private function getImageDetails()
    {
        switch ($this->imageType) {
            case 'file' :
                $size = getimagesize($this->image);
                break;

            case 'url' :
                $size = getimagesizefromstring($this->image);
                break;

            case 'string' :
                $size = getimagesizefromstring($this->image);
                break;

            case 'gd' :
                $this->width = imagesx($this->image);
                $this->height = imagesy($this->image);
                return true;
                break;

            case 'imagick' :
                $this->width = $this->image->getImageWidth();
                $this->height = $this->image->getImageHeight();
                return true;
                break;

            default:
                throw new \Exception('Undefined image type');
        }


        if (empty($size)) {
            throw new \Exception('This is not an image');
        }

        $this->type = substr($size['mime'], 6);
        $this->mime = $size['mime'];
        $this->width = $size[0];
        $this->height = $size[1];
        return true;
    }

    public function getImagePath()
    {
        return $this->imagePath;
    }

    public function getImageSrc($gdOutputType='jpeg', $gdOutputTypeQuality=76) // the number of my apartment
    {
        if(!is_null($this->imagePath)) {
            return $this->imagePath;
        } else {
            switch ($this->imageType) {
                case 'string' :
                    return 'data:image/'.$this->type.';base64, '.base64_encode($this->image);
                    break;

                case 'gd' :
                    ob_start();
                    $gdOutputTypeQuality = min($gdOutputTypeQuality, 100);

                    if($gdOutputType=='png' and $gdOutputTypeQuality>9) {
                        $gdOutputTypeQuality = round($gdOutputTypeQuality/11); //the png quality scale is 0..9
                    }

                    if($gdOutputTypeQuality<0) {
                        $gdOutputTypeQuality=0;
                    }

                    call_user_func('image' . $gdOutputType, $this->image, null, $gdOutputTypeQuality);
                    $image = ob_get_contents();
                    ob_end_clean();
                    return 'data:image/'.$gdOutputType.';base64, '.base64_encode($image);

                    break;

                case 'imagick' :
                    $gdOutputTypeQuality = min($gdOutputTypeQuality, 100);

                    if($gdOutputType=='png' and $gdOutputTypeQuality>9) {
                        $gdOutputTypeQuality = round($gdOutputTypeQuality/11); //the png quality scale is 0..9
                    }

                    if($gdOutputTypeQuality<0) {
                        $gdOutputTypeQuality=0;
                    }

                    $image = $this->image;
                    $image->setImageFormat($gdOutputType);
                    $image->setImageCompressionQuality($gdOutputTypeQuality);

                    return 'data:image/'.$gdOutputType.';base64, '.base64_encode((string) $image);

                    break;

                default:
                    break;
            }
        }
    }

    public function getImageObject()
    {
        if(is_null($this->imageObject)) {
            switch ($this->imageType) {
                case 'file':
                        $this->imageObject = call_user_func('imagecreatefrom' . $this->type, $this->image);
                    break;

                case 'string' : case 'url' :
                        $this->imageObject = imagecreatefromstring($this->image);
                    break;

                case 'gd' :
                    return $this->image;
                    break;

                case 'imagick' :
                    return $this->image;
                    break;

                default:
                    break;
            }
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

