<?php namespace ColorTools;

class Image
{
    private $image = NULL;
    private $imageType = NULL;
    private $imagePath = NULL;

    private $imageObject = NULL;
    private $imageObjectType = NULL;

    private $modified = false;

    protected $type = null;
    protected $mime = null;
    protected $width = null;
    protected $height = null;

    protected $preferredEngine = self::ENGINE_GD;
//    protected $preferredEngine = self::ENGINE_IMAGICK;

    private $resizingOptions = [
        'engine'=>self::RESIZE_ENGINE_NATIVE,
        'imagick'=>[
            'adaptive'=>true,
            'filter'=>self::RESIZE_FILTER_AUTO,
            'blur'=>0.25
        ],
        'gd'=>[
            'filter'=>self::RESIZE_FILTER_AUTO
        ]
    ];

    private $croppingOptions = [
        'anchor'=>self::CROP_ANCHOR_CENTER
    ];

    const ENGINE_GD=1;
    const ENGINE_IMAGICK=2;

    const CROP_ANCHOR_CENTER=0;
    const CROP_ANCHOR_LEFT=1;
    const CROP_ANCHOR_RIGHT=2;
    const CROP_ANCHOR_TOP=3;
    const CROP_ANCHOR_BOTTOM=4;

    const RESIZE_ENGINE_NATIVE=0;
    const RESIZE_ENGINE_GD=1;
    const RESIZE_ENGINE_IMAGICK=2;

    const RESIZE_FILTER_AUTO=99;

    const IMAGE_TYPE_STRING = 1;
    const IMAGE_TYPE_URL = 2;
    const IMAGE_TYPE_FILE = 3;
    const IMAGE_TYPE_GD = 4;
    const IMAGE_TYPE_IMAGICK = 5;
//to replace

    const IMAGE_OBJECT_TYPE_GD = 1;
    const IMAGE_OBJECT_TYPE_IMAGICK = 2;

    public function __construct($image)
    {
        if(gettype($image)=='string')
        {
            if(substr($image, 0, 7)=='http://' or substr($image, 0, 8)=='https://') {
                $this->imageType = self::IMAGE_TYPE_URL;
                $this->imagePath = $image;
                $image = file_get_contents($image);
            } elseif (strlen($image)>64 and strpos(substr($image, 0, 16), '/')===false) {
                //assume it's the content of an image file
                $this->imageType = self::IMAGE_TYPE_STRING;
            } else {
                if (!file_exists($image)) {
                    throw new Exception('Invalid filename');
                }

                if (filesize($image) <= 11) {
                    throw new Exception('This is too small to be an image');
                }

                $this->imageType = self::IMAGE_TYPE_FILE;
                $this->imagePath = $image;
            }
        } else if(gettype($image)=='resource') {
            $resourceType = get_resource_type($image);
            switch ($resourceType) {
                case 'gd' :
                    $this->imageObjectType = self::IMAGE_OBJECT_TYPE_GD;
                    $this->imageType = self::IMAGE_TYPE_GD;

                default :
                break;
            }
        } else if(gettype($image)=='object') {
            $class = get_class($image);
            switch ($class) {
                case 'ColorTools\Image' :
                    return $image;

                case 'Imagick' :
                    $this->imageObjectType = self::IMAGE_OBJECT_TYPE_IMAGICK;
                    $this->imageType = self::IMAGE_TYPE_IMAGICK;

                default :
                    break;
            }
        }

        if($this->imageType == null) {
            throw new Exception('Cannot make anything of this image of type '.gettype($image));
        }

        $this->image = $image;
        $this->getImageDetails();
    }

    public static function create($image)
    {
        if(isset($image) and gettype($image)=='object' and get_class($image) == 'ColorTools\Image') {
            return $image;
        } else {
            return new Image($image);
        }
    }

    public function __get($param) {
        $param = strtolower($param);

        if(in_array($param, ['type', 'mime', 'width', 'height'])) {
            return $this->$param;
        }

        if($param == 'imageObject') {
            return $this->getImageObject();
        }

        if($param == 'imageObjectType') {
            $this->getImageObject();
            return $this->$imageObjectType();
        }

        throw new Exception('Unknown property '.$param);

    }

    public static function createFromColors($colorsArray=array(), $width=0, $height=0)
    {
        if (empty($colorsArray)) {
            throw new Exception('Couldn\'t find any colors.');
        }

        if (empty($width)) {
            throw new Exception('I need the width of the image');
        }
        if (empty($height)) {
            throw new Exception('I also need the height of the image');
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
            case self::IMAGE_TYPE_FILE :
                $size = getimagesize($this->image);
                break;

            case self::IMAGE_TYPE_URL :
                $size = getimagesizefromstring($this->image);
                break;

            case self::IMAGE_TYPE_STRING :
                $size = getimagesizefromstring($this->image);
                break;

            case self::IMAGE_TYPE_GD :
                $this->width = imagesx($this->image);
                $this->height = imagesy($this->image);
                return true;
                break;

            case self::IMAGE_TYPE_IMAGICK :
                $this->width = $this->image->getImageWidth();
                $this->height = $this->image->getImageHeight();
                return true;
                break;

            default:
                throw new Exception('Undefined image type');
        }


        if (empty($size)) {
            throw new Exception('This is not an image');
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
        if(!is_null($this->imagePath) and !$this->modified) {
            return file_get_contents($this->imagePath);
        } else {
            switch ($this->imageType) {
                case self::IMAGE_TYPE_STRING :
                    return $this->image;
                    break;

                case self::IMAGE_TYPE_GD : case self::IMAGE_TYPE_IMAGICK :
                    $outputTypeQuality = min($outputTypeQuality, 100);

                    if($outputType=='png' and $outputTypeQuality>9) {
                        $outputTypeQuality = round($outputTypeQuality/11); //the png quality scale is 0..9
                    }

                    if($outputTypeQuality<0) {
                        $outputTypeQuality=0;
                    }

                    if($this->imageType==self::IMAGE_TYPE_GD) {
                        ob_start();
                        call_user_func('image' . $outputType, $this->image, null, $outputTypeQuality);
                        $image = ob_get_contents();
                        ob_end_clean();
                    } else if($this->imageType==self::IMAGE_TYPE_IMAGICK) {
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

    public function getImageSrc($outputType='jpeg', $outputTypeQuality=76) // the number of my old apartment
    {
        if(!is_null($this->imagePath) and $this->imageType != self::IMAGE_TYPE_FILE and !$this->modified) {
            return $this->imagePath;
        } else {
            switch ($this->imageType) {
                case self::IMAGE_TYPE_STRING : case self::IMAGE_TYPE_FILE :
                    return 'data:image/'.$this->type.';base64, '.base64_encode($this->getImageContent());
                    break;

                case self::IMAGE_TYPE_GD : case self::IMAGE_TYPE_IMAGICK :
                    return 'data:image/'.$outputType.';base64, '.base64_encode($this->getImageContent($outputType, $outputTypeQuality));
                    break;

                default:
                    break;
            }
        }
    }

    public function displayImage($outputType='jpeg', $outputTypeQuality=76)
    {
        if(!in_array($this->imageType, [self::IMAGE_TYPE_GD, self::IMAGE_TYPE_IMAGICK])) {
            $outputType = $this->type;
        }

        if (!headers_sent($filename, $linenum)) {
            header("Content-Type: image/".$outputType);
        } else {
            throw new Exception('Headers already sent by '.$filename.' at line '.$linenum);
        }

        return $this->getImageContent($outputType, $outputTypeQuality);
    }

    public function getImageObject()
    {
        if(is_null($this->imageObject)) {
            switch ($this->imageType) {
                case self::IMAGE_TYPE_FILE :
                        if($this->preferredEngine==self::ENGINE_GD) {
                            $this->imageObject = call_user_func('imagecreatefrom' . $this->type, $this->image);
                            $this->imageObjectType = self::IMAGE_OBJECT_TYPE_GD;
                        } else if($this->preferredEngine==self::ENGINE_IMAGICK) {
                            $this->imageObject = new \Imagick();
                            $this->imageObject->readImageBlob(file_get_contents($this->image));
                            $this->imageObjectType = self::IMAGE_OBJECT_TYPE_IMAGICK;
                        }
                    break;

                case self::IMAGE_TYPE_STRING : case self::IMAGE_TYPE_URL :
                        $this->imageObject = imagecreatefromstring($this->image);
                        $this->imageObjectType = self::IMAGE_OBJECT_TYPE_GD;
                    break;

                case self::IMAGE_TYPE_GD :
                    $this->imageObjectType = self::IMAGE_OBJECT_TYPE_GD;
                    return $this->image;
                    break;

                case self::IMAGE_TYPE_IMAGICK :
                    $this->imageObjectType = self::IMAGE_OBJECT_TYPE_IMAGICK;
                    return $this->image;
                    break;

                default:
                    break;
            }
        }

        return $this->imageObject;
    }

    public function convertObjectTypeTo($imageObjectType=null)
    {
        if(!in_array($imageObjectType, [self::IMAGE_OBJECT_TYPE_GD, self::IMAGE_OBJECT_TYPE_IMAGICK])) {
            throw new Exception('Invalid image object type: '.$imageObjectType);
        }

        if($this->imageObjectType==self::IMAGE_OBJECT_TYPE_GD and $imageObjectType==self::IMAGE_OBJECT_TYPE_IMAGICK) {
            $image = $this->getImageContent('jpeg', 100);
            imagedestroy($this->image);
            $this->image = new \Imagick();
            $this->image->readImageBlob($image);
            $this->imageObject = null;
            $this->imageType = self::IMAGE_TYPE_IMAGICK;
            $this->imageObjectType = self::IMAGE_OBJECT_TYPE_IMAGICK;
            $this->getImageContent('jpeg', 100);
            $this->modified = true;
        } else if($this->imageObjectType==self::IMAGE_OBJECT_TYPE_IMAGICK and $imageObjectType==self::IMAGE_OBJECT_TYPE_GD) {
            $this->image = imagecreatefromstring($this->getImageContent('jpeg', 100));
            $this->imageObject = null;
            $this->imageType = self::IMAGE_TYPE_GD;
            $this->imageObjectType = self::IMAGE_OBJECT_TYPE_GD;
            $this->modified = true;
        }

        return $this;
    }

    public function convertObjectTypeToGd()
    {
        return $this->convertObjectTypeTo(self::IMAGE_OBJECT_TYPE_GD);
    }

    public function convertObjectTypeToImagick()
    {
        return $this->convertObjectTypeTo(self::IMAGE_OBJECT_TYPE_IMAGICK);
    }

    public function fit($width=null, $height=null, $cropAnchor=null)
    {
        if(is_null($width) or is_null($height)) {
            throw new Exception('Invalid sizes');
        }

        $this->resizeCover($width, $height);

        return $this->doCrop($width, $height, $cropAnchor);
    }

    public function resizeContain($width=null, $height=null)
    {
        if(is_null($width) or is_null($height)) {
            throw new Exception('Invalid sizes');
        }

        $horizontalRatio = $this->width / $width;
        $verticalRatio = $this->height / $height;

        $ratio = max($horizontalRatio, $verticalRatio);

        $width = $this->width / $ratio;
        $height = $this->height / $ratio;

        return $this->doResize($width, $height);
    }

    public function resizeCover($width=null, $height=null)
    {
        if(is_null($width) or is_null($height)) {
            throw new Exception('Invalid sizes');
        }

        $horizontalRatio = $this->width / $width;
        $verticalRatio = $this->height / $height;

        $ratio = min($horizontalRatio, $verticalRatio);

        $width = $this->width / $ratio;
        $height = $this->height / $ratio;

        return $this->doResize($width, $height);
    }

    public function doResize($width=null, $height=null)
    {
        if(is_null($width) or is_null($height)) {
            throw new Exception('Invalid sizes');
        }

        if(is_null($this->imageObject)) {
            $this->getImageObject();
        }

        if($this->resizingOptions['engine']==self::RESIZE_ENGINE_GD) {
            $this->convertObjectTypeToGd();
        }

        if($this->resizingOptions['engine']==self::RESIZE_ENGINE_IMAGICK) {
            $this->convertObjectTypeToImagick();
        }

        switch ($this->imageObjectType) {
            case self::IMAGE_OBJECT_TYPE_GD:
                $filter = IMG_BICUBIC_FIXED;
                if(in_array($this->resizingOptions['gd']['filter'], [
                    IMG_BESSEL,
                    IMG_BILINEAR_FIXED,
                    IMG_BICUBIC,
                    IMG_BICUBIC_FIXED,
                    IMG_BLACKMAN,
                    IMG_BOX,
                    IMG_BSPLINE,
                    IMG_CATMULLROM,
                    IMG_GAUSSIAN,
                    IMG_GENERALIZED_CUBIC,
                    IMG_HERMITE,
                    IMG_HAMMING,
                    IMG_HANNING,
                    IMG_MITCHELL,
                    IMG_POWER,
                    IMG_QUADRATIC,
                    IMG_SINC,
                    IMG_NEAREST_NEIGHBOUR,
                    IMG_WEIGHTED4,
                    IMG_TRIANGLE
                ])) {
                    $filter = $this->resizingOptions['gd']['filter'];
                }

                $this->image = imagescale($this->getImageObject(), $width, $height, $filter);
                $this->imageType = self::IMAGE_TYPE_GD;
                break;

            case self::IMAGE_OBJECT_TYPE_IMAGICK:
                $this->imageType = self::IMAGE_TYPE_IMAGICK;

                if($this->resizingOptions['imagick']['adaptive']) {
                     $this->image->adaptiveResizeImage($width, $height);
                } else {
                    $filter = \Imagick::FILTER_BOX;
                    if(in_array($this->resizingOptions['imagick']['filter'], [
                        \Imagick::FILTER_POINT,
                        \Imagick::FILTER_BOX,
                        \Imagick::FILTER_TRIANGLE,
                        \Imagick::FILTER_HERMITE,
                        \Imagick::FILTER_HANNING,
                        \Imagick::FILTER_HAMMING,
                        \Imagick::FILTER_BLACKMAN,
                        \Imagick::FILTER_GAUSSIAN,
                        \Imagick::FILTER_QUADRATIC,
                        \Imagick::FILTER_CUBIC,
                        \Imagick::FILTER_CATROM,
                        \Imagick::FILTER_MITCHELL,
                        \Imagick::FILTER_LANCZOS,
                        \Imagick::FILTER_BESSEL,
                        \Imagick::FILTER_SINC
                    ])) {
                        $filter = $this->resizingOptions['imagick']['filter'];
                    }
                    $this->image->adaptiveResizeImage($width, $height, $filter, $this->resizingOptions['imagick']['blur']);
                }

                break;

            default:
                break;
        }

        $this->imageObject = null;
        $this->modified = true;
        $this->width=$width;
        $this->height=$height;

        return $this;
    }

    public function setCropAnchor($cropAnchor=null)
    {
        if(!in_array($cropAnchor, [
            self::CROP_ANCHOR_CENTER,
            self::CROP_ANCHOR_LEFT,
            self::CROP_ANCHOR_RIGHT,
            self::CROP_ANCHOR_TOP,
            self::CROP_ANCHOR_BOTTOM,
        ])) {
            throw new Exception('Invalid crop anchor: '.$cropAnchor);
        }

        $this->croppingOptions['anchor'] = $cropAnchor;

        return $this;
    }

    public function doCrop($width=null, $height=null, $cropAnchor=null)
    {
        if(is_null($width) or is_null($height)) {
            throw new Exception('Invalid sizes');
        }

        if(!is_null($cropAnchor)) {
            $this->setCropAnchor($cropAnchor);
        }

        if($this->resizingOptions['engine']==self::RESIZE_ENGINE_GD) {
            $this->convertObjectTypeToGd();
        }

        if($this->resizingOptions['engine']==self::RESIZE_ENGINE_IMAGICK) {
            $this->convertObjectTypeToImagick();
        }

        if($this->width > $width) {
            $widthOffset = floor(($this->width - $width)/2);
        } else {
            $widthOffset = 0;
        }

        if($this->height > $height) {
            $heightOffset = floor(($this->height - $height)/2);
        } else {
            $heightOffset = 0;
        }

        if($this->croppingOptions['anchor']==self::CROP_ANCHOR_LEFT) {
            $widthOffset=0;
        } else if($this->croppingOptions['anchor']==self::CROP_ANCHOR_RIGHT) {
            $widthOffset*=2;
        }

        if($this->croppingOptions['anchor']==self::CROP_ANCHOR_TOP) {
            $heightOffset=0;
        } else if($this->croppingOptions['anchor']==self::CROP_ANCHOR_BOTTOM) {
            $heightOffset*=2;
        }


        switch ($this->imageObjectType) {
            case self::IMAGE_OBJECT_TYPE_GD:
                $image = imagecreatetruecolor($width, $height);
                imagecopyresampled($image, $this->image, 0, 0, $widthOffset, $heightOffset, $width, $height, $width, $height);
                imagedestroy($this->image);
                $this->image = $image;
                $this->imageType = self::IMAGE_TYPE_GD;
                break;

            case self::IMAGE_OBJECT_TYPE_IMAGICK:
                $this->image->cropImage($width, $height, $widthOffset, $heightOffset);
                $this->imageType = self::IMAGE_TYPE_IMAGICK;
                break;

            default:
                break;
        }

        $this->imageObject = null;
        $this->modified = true;
        $this->width=$width;
        $this->height=$height;

        return $this;
    }

    public function getAnalysis($analysisOptions=array())
    {
        return Analyze::getAnalysis($this, $analysisOptions);
    }
}

