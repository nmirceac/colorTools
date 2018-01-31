<?php namespace ColorTools;

class Store
{
    const OBJECT_TYPE_IMAGE = 1;

    private $object = null;
    private $objectType = null;

    private $temporaryFile = null;

    private $storePath = null;
    private static $storeBasePath = 'store';
    private static $storePattern = '%hash_prefix%/%hash%';
    private $storeSuffix = '';

    private static $publicPath = 'images';
    private static $publicPattern = 'images/%hash%';

    public function __construct($storageItem=null)
    {
        if(is_null($storageItem)) {
            throw new Exception('No storage item here');
        }

        if(gettype($storageItem)=='object' and get_class($storageItem)=='ColorTools\Image')
        {
            $this->object = $storageItem;
            $this->objectType = self::OBJECT_TYPE_IMAGE;
            $this->setSuffix($this->object->getModifiersString());
        } else {
            try {
                $this->object = new Image($storageItem);
                $this->objectType = self::OBJECT_TYPE_IMAGE;
                $this->setSuffix($this->object->getModifiersString());
            } catch (Exception $e) {
                throw new Exception('I don\'t know what this storage item is: '.print_r($storageItem, true));
            }
        }
    }

    public function __destruct()
    {
        if(!is_null($this->temporaryFile) and file_exists($this->temporaryFile)) {
            unlink($this->temporaryFile);
        }
    }

    public static function settings($settings=[])
    {
        if(empty($settings)) {
            return false;
        }

        if(isset($settings['storeBasePath'])) {
            self::$storeBasePath = $settings['storeBasePath'];
        }

        if(isset($settings['storePattern'])) {
            self::$storePattern = $settings['storePattern'];
        }

        if(isset($settings['publicPath'])) {
            self::$publicPath = $settings['publicPath'];
        }

        if(isset($settings['publicPattern'])) {
            self::$publicPattern = $settings['publicPattern'];
        }

    }

    public static function create($storageItem=null)
    {
        return (new Store($storageItem));
    }

    private function getTemporaryFile()
    {
        if(is_null($this->temporaryFile)) {
            $basePath = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
            $this->temporaryFile = $basePath.DIRECTORY_SEPARATOR.md5(time().rand(0, 10000000)).'.tmp';
        }

        return $this->temporaryFile;
    }

    private function getStorePath()
    {
        if(is_null($this->storePath)) {
            $path = self::$storeBasePath . DIRECTORY_SEPARATOR;
            $path.= str_replace([
                '%hash_prefix%',
                '%hash%'
            ], [
                substr($this->getHash(), 0, 2),
                $this->getHash()
            ], self::$storePattern);

            $this->storePath = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
            $this->setSuffix($this->object->getModifiersString());
            $this->storePath = $this->storePath.$this->storeSuffix;
        }

        return $this->storePath;
    }

    public function getPublishPath()
    {
        $path = self::$publicPath . DIRECTORY_SEPARATOR;
        $path.= str_replace([
            '%hash_prefix%',
            '%hash%'
        ], [
            substr($this->getHash(), 0, 2),
            $this->getHash()
        ], self::$storePattern);

        $this->setSuffix($this->object->getModifiersString());

        $path = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
        $path.= $this->storeSuffix;

        return $path;
    }

    private function verifyPath($path=null)
    {
        if(is_null($path)) {
            throw new Exception('You must specify a path to verify');
        }

        $basePath = dirname($path);
        if(!file_exists($basePath)) {
            mkdir($basePath, 0777, true);
        } else {
            if(!is_dir($basePath)) {
                throw new Exception('Base path '.$basePath.' already exists but it\'s a file not a directory'.
                    ' - cannot store at '.$path);
            }
        }
    }

    private function writeAtPath($filePath=null, $type=null)
    {
        if(is_null($filePath)) {
            throw new Exception('No file path specified');
        }

        if($this->objectType == self::OBJECT_TYPE_IMAGE) {
            if(!is_null($type)) {
                file_put_contents($filePath.'.'.$type, $this->object->getImageContent($type));
            } else {
                file_put_contents($filePath, $this->object->getImageContent());
            }
        }
    }

    public function getPath()
    {
        if($this->objectType == self::OBJECT_TYPE_IMAGE and !empty($this->object->getImagePath())){
            return $this->object->getImagePath();
        } else {
            $temporaryFile = $this->getTemporaryFile();
            $this->writeAtPath($temporaryFile);
            return $temporaryFile;
        }
    }

    public function getHash()
    {
        return $this->object->getHash();
    }

    public function setSuffix($suffix='')
    {
        $this->storeSuffix=trim($suffix);

        return $this;
    }

    public function store($type='jpeg')
    {
        $this->verifyPath($this->getStorePath());
        $this->writeAtPath($this->getStorePath(), $type);

        return $this;
    }

    public function publish($type='jpeg')
    {
        $this->verifyPath($this->getPublishPath());
        $this->writeAtPath($this->getPublishPath(), $type);

        $path = str_replace([
            '%hash_prefix%',
            '%hash%'
        ], [
            substr($this->getHash(), 0, 2),
            $this->getHash()
        ], self::$publicPattern);

        $path = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
        $path.= $this->storeSuffix;
        $path.= '.'.$type;

        return $path;
    }

    public static function findByHash($hash=null)
    {
        if(is_null($hash)) {
            throw new Exception('The hash cannot be empty');
        }

        if(strlen($hash)!=32) {
            throw new Exception('The hash must have 32 characters');
        }

        $path = self::$storeBasePath . DIRECTORY_SEPARATOR;
        $path.= str_replace([
            '%hash_prefix%',
            '%hash%'
        ], [
            substr($hash, 0, 2),
            $hash
        ], self::$storePattern);

        $path = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);

        if(file_exists($path)) {
            $store = Store::create($path);
        } else if(file_exists($path.'.jpeg')) {
            $store = Store::create($path.'.jpeg');
        } else if(file_exists($path.'.png')) {
            $store = Store::create($path.'.png');
        } else {
            throw new Exception('The object with the hash '.$hash.' was not found');
        }
        $store->object->refreshImageObject()->forceModify()->forceHash($hash);

        return $store;
    }

    public function modifyImage($closure=null)
    {
        if(is_null($closure)) {
            throw new Exception('Must pass a closure to the modifyImage function');
        }

        $closure($this->object);
        return $this;
    }

    public static function getUrl($hash, $modifiers=null, $type='jpeg')
    {
        $path = str_replace([
            '%hash_prefix%',
            '%hash%'
        ], [
            substr($hash, 0, 2),
            $hash
        ], self::$publicPattern);

        if(!is_null($modifiers)) {
            if(is_array($modifiers) or is_callable($modifiers)) {
                $path.=self::convertTransformationsToModifierString($modifiers);

            } else if(is_string($modifiers)) {
                $path.='-'.ltrim($modifiers, '-');
            }
        }

        $path.='.'.$type;
        return $path;
    }

    public static function convertTransformationsToModifierString($transformations = null)
    {
        if(is_null($transformations)) {
            throw new Exception('The transformations cannot be null');
        }

        if(!is_callable($transformations) and !is_array($transformations)) {
            throw new Exception('The transformations have to be callable');
        }

        $store = new Store(new Image(Image::FAKE_IMAGE));
        if(is_array($transformations)) {
            foreach($transformations as $transformationCallback) {
                if(!is_callable($transformationCallback)) {
                    throw new Exception('The transformations have to be callable');
                }
                $store->modifyImage($transformationCallback);
            }
        } else {
            $store->modifyImage($transformations);
        }
        return $store->object->getModifiersString();
    }

    public function processModifiersString($modifiersString='')
    {
        $this->object->processModifiersString($modifiersString);
        return $this;
    }

    public static function findAndProcess($hashAndModifiers=null)
    {
        if(is_null($hashAndModifiers)) {
            throw new Exception('The hash cannot be empty');
        }

        if(strlen($hashAndModifiers)<32) {
            throw new Exception('The hash must have at least 32 characters');
        }

        $hash = substr($hashAndModifiers, 0, 32);

        $store = Store::findByHash($hash);
        $store->object->autoRotate();
        $store->processModifiersString(substr($hashAndModifiers, 32));

        return $store;
    }

    public static function optimizeFile($filePath=null)
    {
        if(is_null($filePath)) {
            throw new Exception('The file path cannot be empty');
        }

        if(!file_exists($filePath)) {
            throw new Exception('The file '.$filePath.' cannot be found');
        }

        exec('which jpegoptim', $output);
        if(!empty($output)) {
            exec('jpegoptim -s --all-progressive -m95 '.$filePath, $output);
        }

    }

}