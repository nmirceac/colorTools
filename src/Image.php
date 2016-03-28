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
                    throw new Exception('Invalid filename');
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

    public static function create($image)
    {
        return new Image($image);
    }

    public static function createFromColors($colorsArray=array(), $width=0, $height=0)
    {
        if (empty($colorsArray)) {
            throw new \Exception('Couldn\'t find any colors.');
        }

        if (empty($width)) {
            throw new \Exception('I need the width of the image');
        }
        if (empty($height)) {
            throw new \Exception('I also need the height of the image');
        }

        $image = imagecreatetruecolor($width, $height);
        for($x=0; $x<$width; $x++) {
            for($y=0; $y<$height; $y++) {
                imagesetpixel($image, $x, $y, $colorsArray[$x + $y * $width]->int);
            }
        }

        return new Image($image);
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

    public function getImageContent($outputType='jpeg', $outputTypeQuality=76)
    {
        if(!is_null($this->imagePath)) {
            return file_get_contents($this->imagePath);
        } else {
            switch ($this->imageType) {
                case 'string' :
                    return $this->image;
                    break;

                case 'gd' : case 'imagick' :
                    $outputTypeQuality = min($outputTypeQuality, 100);

                    if($outputType=='png' and $outputTypeQuality>9) {
                        $outputTypeQuality = round($outputTypeQuality/11); //the png quality scale is 0..9
                    }

                    if($outputTypeQuality<0) {
                        $outputTypeQuality=0;
                    }

                    if($this->imageType=='gd') {
                        ob_start();
                        call_user_func('image' . $outputType, $this->image, null, $outputTypeQuality);
                        $image = ob_get_contents();
                        ob_end_clean();
                    } else if($this->imageType=='imagick') {
                        $image = $this->image;
                        $image->setImageFormat($outputType);
                        $image->setImageCompressionQuality($outputTypeQuality);
                        $image = (string) $image;
                    }
                    return $image;
                    break;

                default:
                    break;
            }
        }
    }

    public function getImageSrc($outputType='jpeg', $outputTypeQuality=76) // the number of my apartment
    {
        if(!is_null($this->imagePath)) {
            return $this->imagePath;
        } else {
            switch ($this->imageType) {
                case 'string' :
                    return 'data:image/'.$this->type.';base64, '.base64_encode($this->image);
                    break;

                case 'gd' : case 'imagick' :
                    return 'data:image/'.$outputType.';base64, '.base64_encode($this->getImageContent($outputType, $outputTypeQuality));
                    break;

                default:
                    break;
            }
        }
    }

    public function displayImage($outputType='jpeg', $outputTypeQuality=76)
    {
        if(!in_array($this->imageType, ['gd', 'imagick'])) {
            header("Content-Type: image/".$this->type);
        } else {
            header("Content-Type: image/".$outputType);
        }
        echo $this->getImageContent();
        exit();
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

    public function getAnalysis($analysisOptions=array())
    {
        return Analyze::getAnalysis($this, $analysisOptions);
    }
}

