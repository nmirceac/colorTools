<?php namespace ColorTools;

use Illuminate\Support\Facades\File as Filesystem;

/**
 * Class ImageStore
 * @package ColorTools
 */
class ImageStore extends \Illuminate\Database\Eloquent\Model
{
    /**
     * @var null
     */
    private $relationship = [];

    const IMAGE_ASSOCIATIONS_PIVOT_TABLE = 'image_associations';

    /**
     * @var array
     */
    public static $withPivot = ['order', 'role', 'details'];

    /**
     * @var string
     */
    protected $table = 'images';
    /**
     * @var array
     */
    protected $appends = ['orientation', 'basename', 'details'];

    /**
     * @var array
     */
    protected $hidden = ['exif', 'histogram', 'ai'];

    /**
     * @param $value
     */
    public function setNameAttribute($name)
    {
        $this->attributes['name'] = iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8//IGNORE", $name);
    }

    /**
     * @param $value
     */
    public function setMetadataAttribute($value)
    {
        $this->attributes['metadata'] = json_encode($value);
    }

    /**
     * @return mixed
     */
    public function getAiAttribute()
    {
        return json_decode($this->attributes['ai']);
    }

    /**
     * @param $value
     */
    public function setAiAttribute($value)
    {
        $this->attributes['ai'] = json_encode($value);
    }

    /**
     * @return mixed
     */
    public function getHistogramAttribute()
    {
        return json_decode($this->attributes['histogram']);
    }

    /**
     * @param $value
     */
    public function setHistogramAttribute($value)
    {
        $this->attributes['histogram'] = json_encode($value);
    }

    /**
     * @return mixed
     */
    public function getMetadataAttribute()
    {
        $metadata = json_decode($this->attributes['metadata']);
        if(!isset($metadata->name)) {
            $metadata->name = '';
        }
        if(!isset($metadata->caption)) {
            $metadata->caption = '';
        }
        if(!isset($metadata->copyright)) {
            $metadata->copyright = '';
        }
        if(!isset($metadata->tags)) {
            $metadata->tags = [];
        }

        return $metadata;
    }

    /**
     * @param $value
     */
    public function setAnalyzedAttribute($value)
    {
        retun;
    }

    /**
     * @return mixed
     */
    public function getAnalyzedAttribute()
    {
        $metadata = $this->metadata;
        if(isset($metadata->analyzed) and !empty($metadata->analyzed)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $value
     */
    public function setExifAttribute($value)
    {
        $this->attributes['exif'] = json_encode($value);
    }

    /**
     * @return mixed
     */
    public function getExifAttribute()
    {
        return json_decode($this->attributes['exif']);
    }

    /**
     * @param $value
     */
    public function setColorsAttribute($value)
    {
        $this->attributes['colors'] = json_encode($value);
    }

    /**
     * @return mixed
     */
    public function getColorsAttribute()
    {
        return json_decode($this->attributes['colors']);
    }

    /**
     * @param $value
     */
    public function setBasenameAttribute($value)
    {
        return;
    }

    /**
     * @return string
     */
    public function getBasenameAttribute()
    {
        return $this->name.'.'.$this->type;
    }

    /**
     * @param $value
     */
    public function setOrientationAttribute($value)
    {
        return;
    }

    /**
     * @return string
     */
    public function getOrientationAttribute()
    {
        return ($this->width >= $this->height) ? 'L' : 'P';
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getDetailsAttribute($value)
    {
        if(isset($this->pivot['details'])) {
            return json_decode($this->pivot['details']);
        }
    }

    /**
     * @param $value
     */
    public function setDetailsAttribute($value)
    {
        if(isset($this->pivot['details'])) {
            $this->pivot->update(['details' => json_encode($value)]);
        }
    }

    /**
     * Finds an image by id
     * @param $id
     * @return \ColorTools\ImageStore
     */
    public static function find($id)
    {
        return parent::query()->find($id);
    }

    /**
     * @param string $hash
     * @return \ColorTools\ImageStore
     */
    public static function getByHash($hash)
    {
        return self::where('hash', $hash)->first();
    }

    /**
     * Creates a new ImageStore
     * @param array $metadata
     * @param string $contents
     * @return ImageStore
     * @throws \Exception
     */
    public static function create(array $metadata, string $contents='')
    {
        $validator = \Validator::make($metadata, [
            'hash' => 'required|string|size:32',
            'name' => 'required|string',
            'mime' => 'required|string',
            'size' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors());
        }

        if(empty($contents)) {
            throw new \Exception('Contents is empty');
        }

        $image = static::getByHash($metadata['hash']);
        if(!is_null($image)) {
            try {
                $store = \ColorTools\Store::findByHash($metadata['hash']);
            } catch (\ColorTools\Exception $exception) {
                if($exception->getCode() == \ColorTools\Exception::STORE_EXCEPTION_HASH_NOT_FOUND) {
                    $store = \ColorTools\Store::create($contents);
                    $store->store();
                }
            }
            return $image;
        } else {
            $store = \ColorTools\Store::create($contents);
            $imageDetails = $store->getObject()->serializeDetails();
            $store->store();
        }

        $image = new static;
        $image->hash = $metadata['hash'];
        $image->name = $metadata['name'];
        $image->type = substr($metadata['mime'], 1 + strrpos($metadata['mime'], '/'));
        $image->size = $metadata['size'];
        $image->width = $imageDetails['width'];
        $image->height = $imageDetails['height'];

        $image->colors = [];
        $image->ai = [];
        $image->histogram = [];
        $image->exif = [];

        if(!isset($metadata['extension']) or (isset($metadata['extension']) and empty($metadata['extension']))) {
            $metadata['extension'] = $image->type;
        }

        $image->metadata = $metadata;
        $image->save();

        if(config('colortools.store.analyzeAfterCreate', false)) {
            $image->analyze();
        }

        return $image;
    }

    /**
     * Creates an ImageStore from path
     * @param string $filePath
     * @return ImageStore
     * @throws \Exception
     */
    public static function createFromPath(string $filePath)
    {
        if(!file_exists(($filePath))) {
            throw new \Exception('File not found at path '.$filePath.' ('.getcwd().'/'.$filePath.')');
        }

        if(is_dir(($filePath))) {
            throw new \Exception('The path '.$filePath.' resolves to a directory, not to a file');
        }

        $metadata['mime'] = Filesystem::mimeType($filePath);
        $metadata['name'] = Filesystem::name($filePath);
        $metadata['dirname'] = Filesystem::dirname($filePath);
        $metadata['basename'] = Filesystem::basename($filePath);
        $metadata['extension'] = Filesystem::extension($filePath);
        $metadata['size'] = Filesystem::size($filePath);
        $metadata['lastModified'] = Filesystem::lastModified($filePath);
        $metadata['originalPath'] = $filePath;
        $metadata['hash'] = Filesystem::hash($filePath);

        return static::create($metadata, file_get_contents($filePath));
    }

    /**
     * Creates an ImageStore form a Laravel request
     * @param \Illuminate\Http\Request $request
     * @param string $fileKey
     * @return \ColorTools\ImageStore
     * @throws \Exception
     */
    public static function createFromRequest(\Illuminate\Http\Request $request, $fileKey = 'image')
    {
        if(!isset($request->allFiles()[$fileKey])) {
            throw new \Exception('Missing file "'.$fileKey.'"');
        }

        if(!$request->hasFile($fileKey)) {
            throw new \Exception('There was a problem with file "'.$fileKey.'"');
        }

        $fileInfo = $request->file($fileKey);

        $metadata['mime'] = $fileInfo->getMimeType();
        $metadata['name'] = $fileInfo->getClientOriginalName();
        $metadata['basename'] = $fileInfo->getClientOriginalName();
        $metadata['extension'] = $fileInfo->getClientOriginalExtension();
        $metadata['size'] = $fileInfo->getSize();
        $metadata['originalPath'] = $fileInfo->getRealPath();
        $metadata['hash'] = md5_file($metadata['originalPath']);

        if(!empty($metadata['extension'])) {
            $metadata['name'] = substr($metadata['name'], 0, -(1+strlen($metadata['extension'])));
        }

        $contents = file_get_contents($fileInfo->getRealPath());
        return static::create($metadata, $contents);
    }

    /**
     * Replaces an ImageStore
     * @param array $metadata
     * @param string $contents
     * @return ImageStore
     * @throws \Exception
     */
    public function replace(array $metadata, string $contents='')
    {
        $validator = \Validator::make($metadata, [
            'hash' => 'required|string|size:32',
            'name' => 'required|string',
            'mime' => 'required|string',
            'size' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors());
        }

        if(empty($contents)) {
            throw new \Exception('Contents is empty');
        }

        $image = static::getByHash($metadata['hash']);
        if(!is_null($image)) {
            try {
                $store = \ColorTools\Store::findByHash($metadata['hash']);
            } catch (\ColorTools\Exception $exception) {
                if($exception->getCode() == \ColorTools\Exception::STORE_EXCEPTION_HASH_NOT_FOUND) {
                    $store = \ColorTools\Store::create($contents);
                    $store->store();
                }
            }
            return $image;
        } else {
            $store = \ColorTools\Store::create($contents);
            $imageDetails = $store->getObject()->serializeDetails();
            $store->store();
        }

        $oldHash = $this->hash;

        $this->hash = $metadata['hash'];
        $this->name = $metadata['name'];
        $this->type = substr($metadata['mime'], 1 + strrpos($metadata['mime'], '/'));
        $this->size = $metadata['size'];
        $this->width = $imageDetails['width'];
        $this->height = $imageDetails['height'];

        $this->colors = [];
        $this->ai = [];
        $this->histogram = [];
        $this->exif = [];

        if(!isset($metadata['extension']) or (isset($metadata['extension']) and empty($metadata['extension']))) {
            $metadata['extension'] = $this->type;
        }

        $this->metadata = $metadata;
        $this->save();

        if(config('colortools.store.analyzeAfterCreate', false)) {
            $this->analyze();
        }

        if(self::where('hash', $oldHash)->count()==0) {
            $imageFromStore = Store::findByHash($oldHash);
            if($imageFromStore) {
                $imageFromStore->deletePublished();
                $imageFromStore->deleteFromStore();
            }
        }

        return $this;
    }
    /**
     * Replaces an ImageStore from path
     * @param string $filePath
     * @return ImageStore
     * @throws \Exception
     */
    public function replaceFromPath(string $filePath)
    {
        if(!file_exists(($filePath))) {
            throw new \Exception('File not found at path '.$filePath.' ('.getcwd().'/'.$filePath.')');
        }

        if(is_dir(($filePath))) {
            throw new \Exception('The path '.$filePath.' resolves to a directory, not to a file');
        }

        $metadata['mime'] = Filesystem::mimeType($filePath);
        $metadata['name'] = Filesystem::name($filePath);
        $metadata['dirname'] = Filesystem::dirname($filePath);
        $metadata['basename'] = Filesystem::basename($filePath);
        $metadata['extension'] = Filesystem::extension($filePath);
        $metadata['size'] = Filesystem::size($filePath);
        $metadata['lastModified'] = Filesystem::lastModified($filePath);
        $metadata['originalPath'] = $filePath;
        $metadata['hash'] = Filesystem::hash($filePath);

        return $this->replace($metadata, file_get_contents($filePath));
    }

    /**
     * Replaces an ImageStore form a Laravel request
     * @param \Illuminate\Http\Request $request
     * @param string $fileKey
     * @return \ColorTools\ImageStore
     * @throws \Exception
     */
    public function replaceFromRequest(\Illuminate\Http\Request $request, $fileKey = 'image')
    {
        if(!$request->hasFile($fileKey)) {
            return response()->json([
                'error' => 'Missing file "'.$fileKey.'"'
            ]);
        }

        $fileInfo = $request->file($fileKey);

        $metadata['mime'] = $fileInfo->getMimeType();
        $metadata['name'] = $fileInfo->getClientOriginalName();
        $metadata['basename'] = $fileInfo->getClientOriginalName();
        $metadata['extension'] = $fileInfo->getClientOriginalExtension();
        $metadata['size'] = $fileInfo->getSize();
        $metadata['originalPath'] = $fileInfo->getRealPath();
        $metadata['hash'] = md5_file($metadata['originalPath']);

        if(!empty($metadata['extension'])) {
            $metadata['name'] = substr($metadata['name'], 0, -(1+strlen($metadata['extension'])));
        }

        $contents = file_get_contents($fileInfo->getRealPath());
        return $this->replace($metadata, $contents);
    }

    /**
     * @param bool $redo
     * @return $this
     * @throws \ColorTools\Exception
     */
    public function analyze($redo = false)
    {
        if($this->analyzed and !$redo) {
            return $this;
        }

        $analysis = $this->getStore()->getObject()->serializeAnalysis();
        $this->exif = $this->getStore()->getObject()->getExifInfo();;
        $this->colors = $analysis['colors'];
        $this->ai = $this->getAiInfo();
        $this->histogram = $analysis['histogram'];

        $metadata = $this->metadata;
        $metadata->analyzed = true;
        $metadata->luma = $analysis['luma'];
        if(isset($metadata->histogram)) {
            unset($metadata->histogram);
        }
        $this->metadata = $metadata;
        $this->save();

        return $this;
    }

    public static function getAiInfoFromContent($content)
    {
        if(!(config('colortools.rekognition.key') and config('colortools.rekognition.secret') and config('colortools.rekognition.region'))) {
            return [];
        }

        $rekognition = new \Aws\Rekognition\RekognitionClient([
            'credentials' => (new \Aws\Credentials\Credentials(config('colortools.rekognition.key'), config('colortools.rekognition.secret'))),
            'version' => 'latest',
            'region' => config('colortools.rekognition.region')
        ]);

        try {
            $result = $rekognition->detectLabels(array(
                    'Image' => array(
                        'Bytes' => $content,
                    ),
                    'Attributes' => array('ALL')
                )
            );
        } catch (\Aws\S3\Exception\S3Exception $exception) {
            throw new \Exception($exception->getAwsErrorMessage());
        }

        $ai['rekognition']['labels'] = $result->toArray()['Labels'];

        $searchFaces = false;
        $searchText = false;

        foreach($ai['rekognition']['labels'] as $label) {
            if($label['Confidence']>=20) {
                $ai['labels'][] = $label['Name'];
            }

            if(in_array($label['Name'], ['Human', 'Person', 'People', 'Portrait'])) {
                $searchFaces = true;
            }

            if(in_array($label['Name'], ['Sign', 'Symbol', 'Text'])) {
                $searchText = true;
            }
        }

        $ai['labels'] = array_unique($ai['labels']);

        if($searchFaces) {
            try {
                $result = $rekognition->detectFaces(array(
                        'Image' => array(
                            'Bytes' => $content,
                        ),
                        'Attributes' => array('ALL')
                    )
                );
            } catch (\Aws\S3\Exception\S3Exception $exception) {
                throw new \Exception($exception->getAwsErrorMessage());
            }

            $ai['rekognition']['faces'] = $result->toArray()['FaceDetails'];
        }

        if($searchText) {
            try {
                $result = $rekognition->detectText(array(
                        'Image' => array(
                            'Bytes' => $content,
                        ),
                        'Attributes' => array('ALL')
                    )
                );
            } catch (\Aws\S3\Exception\S3Exception $exception) {
                throw new \Exception($exception->getAwsErrorMessage());
            }

            $ai['rekognition']['text'] = $result->toArray()['TextDetections'];

            foreach($ai['rekognition']['text'] as $text) {
                if($text['Type']=='LINE') {
                    $ai['text'][] = $text['DetectedText'];
                }
            }

            $ai['text'] = array_unique($ai['text']);
        }

        return $ai;
    }

    /**
     * Gets AI info from 3rd party APIs
     * @return array
     * @throws \ColorTools\Exception
     */
    public function getAiInfo()
    {
        $ai = [];

        if(config('colortools.rekognition.key') and config('colortools.rekognition.secret') and config('colortools.rekognition.region')) {
            if($this->width>1920 or $this->height>1920) {
                $content = $this->getStore()->getObject()->resizeCover(1920, 1920)->getImageContent('jpeg');
            } else {
                $content = $this->getStore()->getObject()->getImageContent('jpeg');
            }

            try {
                $ai = self::getAiInfoFromContent($content);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }

        return $ai;
    }

    /**
     * Gets the histogram object
     * @return \ColorTools\Histogram
     * @throws \ColorTools\Exception
     */
    public function getHistogram()
    {
        $this->analyze();

        return \ColorTools\Histogram::create((array) $this->histogram);
    }

    /**
     * Gets a base64 encoded image source representing a histogram SVG
     * @param string $histogram
     * @param array $options
     * @return string
     * @throws \ColorTools\Exception
     */
    public function getHistogramSrc($histogram='c', $options=[])
    {
        return $this->getHistogram()->getSrc($histogram, $options=[]);
    }

    /**
     * Returns a laravel response of image representing a histogram SVG
     * @param string $histogram
     * @param array $options
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \ColorTools\Exception
     */
    public function showHistogram($histogram='c', $options=[])
    {
        return response($this->getHistogram()->buildHistogram($histogram, $options=[]))
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Description', $this->name.' histogram ('.$histogram.')')
            ->header('Content-Disposition', 'inline; filename="'.$this->name.'-histogram-'.$histogram.'.svg"');
    }

    /**
     * Gets the stored content of an image
     * @return mixed
     */
    public function getContent()
    {
        return $this->getStore()->getObject()->getImageContent();
    }

    /**
     * Checks how many times an image is used within the application
     * @return integer
     */
    public function usageCount()
    {
        return $this->getConnection()
            ->table('image_associations')
            ->where('image_id', $this->id)
            ->count();
    }

    /**
     * Checks if an image is in use
     * @return bool
     */
    public function inUse()
    {
        if ($this->usageCount() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Tries to delete an image after a detach opeartion, if not in use somewhere else
     * @param $imageId
     */
    public static function tryToDelete($imageId)
    {
        $image = self::find($imageId);
        if(!$image->inUse()) {
            $image->delete();
        }
    }


    /**
     * Deletes an image
     * @return bool
     * @throws \Exception
     */
    public function delete()
    {
        if(self::where('id', '!=', $this->id)->where('hash', $this->hash)->count()==0) {
            $this->getStore()->deletePublished();
            $this->getStore()->deleteFromStore();
        }

        return parent::delete();
    }

    /**
     * Serves an image (inline)
     * @return mixed
     */
    public function serve()
    {
        return response($this->getContent())
            ->header('Content-Type', $this->mime)
            ->header('Content-Description', $this->name)
            ->header('Content-Length', $this->size)
            ->header('Content-Disposition', 'inline; filename="'.$this->basename.'"');
    }

    /**
     * Serves an image (download)
     * @return mixed
     */
    public function serveForceDownload()
    {
        return response(file_get_contents($this->getStore()->getPath()))
            ->header('Content-Type', $this->mime)
            ->header('Content-Description', $this->name)
            ->header('Content-Length', $this->size)
            ->header('Content-Disposition', 'attachment; filename="'.$this->basename.'"');
    }

    /**
     * Checks the relation ship with the associated attaching model
     * @param $model
     * @return |null
     * @throws \Exception
     */
    private function checkRelationship($model)
    {
        if(!is_object($model)) {
            throw new \Exception('Passed model variable is not an object');
        }

        $modelName = get_class($model);

        if (!isset($this->relationship[$modelName])) {
            $modelName = strtolower(substr($modelName, 1 + strrpos($modelName, '\\')));

            if(!method_exists($this, $modelName) and !method_exists($this, str_plural($modelName))) {
                throw new \Exception(self::class.' missing relationship to model of type '.get_class($model));
            }

            if(!method_exists($model, 'images')) {
                throw new \Exception('Model of type '.get_class($model).' is missing a relationship to '.self::class);
            }

            $this->relationship[$modelName] = $model->imagesRelationship();
        }

        return $this->relationship[$modelName];
    }

    /**
     * Attaches a related model to the image
     * @param $model
     * @param string $role
     * @param int $order
     * @param array $details
     * @throws \Exception
     */
    public function attach($model, $role='images', $order=0, $details=[])
    {
        $relationship = $this->checkRelationship($model);

        if(empty($order)) {
            $order = 'next';
        }
        if($order < 0) {
            $order = 'first';
        }

        if(!isset($details['name'])) {
            $details['name'] = $this->name;
        }

        $models = [];
        foreach($model->imagesByRole($role)->get() as $file) {
            $models[$file->id] = [
                'order'=>$file->pivot->order,
                'role'=>$file->pivot->role,
                'details'=>$file->pivot->details,
            ];
        }

        $models[$this->id] = [
            'order'=>$order,
            'role'=>$role,
            'details'=>json_encode($details)
        ];

        if(in_array($order, ['next', 'last'])) {
            $order = count($models);
        } else if($order=='first') {
            $order = 1;
        } else {
            $order = min($order, count($models));
        }

        $newOrder = range(1, count($models));

        $index = 0;
        foreach($models as $id=>$model) {
            if($this->id == $id) {
                $models[$id]['order'] = $order;
            } else {
                if($order == $newOrder[$index]) {
                    $index--;
                }
                $models[$id]['order'] = $newOrder[$index];
            }
            $index++;
        }

        $relationship->syncWithoutDetaching($models);

        return $relationship->where('id', $this->id)->first();
    }

    /**
     * Replaces an image for a specific role for an attached model
     * @param $model
     * @param string $role
     * @param array $details
     * @param bool $deleteReplaced
     * @return mixed
     * @throws \Exception
     */
    public function set($model, $role='image', $details=[], $deleteReplaced = false)
    {
        $relationship = $this->checkRelationship($model);
        $this->clear($model, $role, $deleteReplaced);

        if(!isset($details['name'])) {
            $details['name'] = $this->name;
        }

        $pivotDetails = [
            'order'=>1,
            'role'=>$role,
            'details'=>json_encode($details)
        ];

        $relationship->attach($this->id, $pivotDetails);

        return $relationship->where('id', $this->id)->first();
    }

    /**
     * Clears all images for a specific role for an attached model
     * @param $model
     * @param string $role
     * @param bool $deleteReplaced
     * @throws \Exception
     */
    public function clear($model, $role='image', $deleteReplaced = false)
    {
        $relationship = $this->checkRelationship($model);

        if($deleteReplaced) {
            $relationship->wherePivot('role', $role)->delete();
        } else {
            $relationship->wherePivot('role', $role)->detach();
        }

        $model->reorderImagesByRole([], $role);
    }

    public function clearForModel($model)
    {
        $relationship = $this->checkRelationship($model);
        $relationship->detach();
    }

    public function clearAllAssociations()
    {
        return \DB::connection($this->connection)
            ->table(self::FILE_ASSOCIATIONS_PIVOT_TABLE)
            ->where('file_id', $this->id)
            ->delete();
    }

    /**
     * @return \ColorTools\Store
     * @throws Exception
     */
    public function getStore()
    {
        return \ColorTools\Store::findByHash($this->hash);
    }

    /**
     * Gets a public image URL of an image
     * @param closure $transformations
     * @return \ColorTools\Image
     * @throws Exception
     */
    public function getUrl($transformations = null, $type='auto')
    {
        if($type=='auto') {
            if(strpos(request()->userAgent(), 'Chrome')) {
                $type='webp';
            } else {
                $type='jpeg';
            }
        }

        if(config('colortools.router.returnRelativeUrls', true)) {
            return $this->getRelativeUrl($transformations, $type);
        } else {
            return $this->getAbsoluteUrl($transformations, $type);
        }
    }

    /**
     * Gets a public relative URL of an image
     * @param null $transformations
     * @param string $type
     * @return string
     */
    public function getRelativeUrl($transformations = null, $type='jpeg')
    {
        return route(config('colortools.router.namedPrefix').'.get', \ColorTools\Store::getHashAndTransformations($this->hash, $transformations, $type), false);
    }

    /**
     * Gets a public absolute URL of an image
     * @param null $transformations
     * @param string $type
     * @return string
     */
    public function getAbsoluteUrl($transformations = null, $type='jpeg')
    {
        return route(config('colortools.router.namedPrefix').'.get', \ColorTools\Store::getHashAndTransformations($this->hash, $transformations, $type), true);
    }


    /**
     * Applies modifiers to an image
     * @param Modifier closure $closure
     * @return \ColorTools\Image
     * @throws Exception
     */
    public function modifyImage($closure = null)
    {
        return $this->getStore()->modifyImage($closure);
    }

    /**
     * Modify and publish an image
     * @param Modifier closure $closure
     * @return string
     * @throws Exception
     */
    public function modifyImagePublish($closure = null, $type='jpeg')
    {
        return $this->modifyImage($closure)->publish($type);
    }

    /**
     * Publishes an image and returns it's path
     * @param string $type
     * @return string
     * @throws Exception
     */
    public function publish($type='jpeg')
    {
        return $this->getStore()->publish($type);
    }

    /**
     * Processes modifier string
     * @param $modifiersString
     * @return Store
     * @throws Exception
     */
    public function processModifiersString($modifiersString)
    {
        return $this->getStore()->processModifiersString($modifiersString);
    }
}
