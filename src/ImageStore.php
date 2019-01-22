<?php namespace ColorTools;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File as Filesystem;

/**
 * Class ImageStore
 * @package ColorTools
 */
class ImageStore extends Model
{
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
    protected $appends = ['basename', 'details'];

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
    public function getMetadataAttribute()
    {
        return json_decode($this->attributes['metadata']);
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
     * @param string $hash
     * @return \ColorTools\ImageStore
     */
    public static function getByHash($hash)
    {
        return self::where('hash', $hash)->first();
    }

    /**
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
            $store->store();
        }

        $image = new static;
        $image->hash = $metadata['hash'];
        $image->name = $metadata['name'];
        $image->type = substr($metadata['mime'], 1 + strrpos($metadata['mime'], '/'));
        $image->size = $metadata['size'];

        if(!isset($metadata['extension']) or (isset($metadata['extension']) and empty($metadata['extension']))) {
            $metadata['extension'] = $image->type;
        }

        $image->metadata = $metadata;
        $image->save();

        return $image;
    }

    /**
     * @param string $filePath
     * @return ImageStore
     * @throws \Exception
     */
    public static function createFromPath(string $filePath)
    {
        if(!file_exists(($filePath))) {
            throw new \Exception('File not found at path '.$filePath.' ('.base_path($filePath).')');
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
     * @param \Illuminate\Http\Request $request
     * @param string $fileKey
     * @return \ColorTools\ImageStore
     * @throws \Exception
     */
    public static function createFromRequest(\Illuminate\Http\Request $request, $fileKey = 'image')
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
        return static::create($metadata, $contents);
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return \Storage::get(self::storagePrefix.$this->hash);
    }

    /**
     * @return bool
     */
    public function inUse()
    {
        if($this->expenses()->count()>0) {  //////////////////// find relationships
            return true;
        }

        return false;
    }

    /**
     * @param $fileId
     */
    public static function tryToDelete($fileId) // ???
    {
        $file = self::find($fileId);
        if(!$file->inUse()) {
            $file->delete();
        }
    }


    /**
     * @return bool|void|null
     * @throws \Exception
     */
    public function delete()
    {
        if(self::where('id', '!=', $this->id)->where('hash', $this->hash)->count()==0) {
            $this->getStore()->deletePublished();
            $this->getStore()->deleteFromStore();
        }
        parent::delete();


    }

    /**
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
     * @return mixed
     */
    public function serveForceDownload()
    {
        return response($this->getContent())
            ->header('Content-Type', $this->mime)
            ->header('Content-Description', $this->name)
            ->header('Content-Length', $this->size)
            ->header('Content-Disposition', 'attachment; filename="'.$this->basename.'"');
    }

    /**
     * @param $model
     * @param string $role
     * @param int $order
     * @param array $details
     * @throws \Exception
     */
    public function attach($model, $role='image', $order=0, $details=[])
    {
        $modelName = get_class($model);
        $modelName = strtolower(substr($modelName, 1 + strrpos($modelName, '\\')));

        if(empty($order)) {
            $order = 'next';
        }
        if($order < 0) {
            $order = 'first';
        }


        if(!method_exists($this, $modelName) and !method_exists($this, str_plural($modelName))) {
            throw new \Exception(self::class.' missing relationship to model of type '.get_class($model));
        }

        if(method_exists($model, 'images')) {
            $method = 'images';
        } else {
            throw new \Exception('Model of type '.get_class($model).' is missing a relationship to '.self::class);
        }

        if(!isset($details['name'])) {
            $details['name'] = $this->name;
        }

        if(method_exists($model, 'image')) {
            $method = 'image';
        }

        $relationship = $model->images();

        if($method=='images') {
            $models = [];
            foreach($relationship->get(['id']) as $file) {
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
        } else {
            $models = [
                $this->id => [
                    'order'=>0,
                    'role'=>$role,
                    'details'=>json_encode($details)
                ]
            ];
        }

        $relationship->sync($models);

        return $relationship->where('id', $this->id)->first();
    }

    public function set($model, $role='image', $details=[])
    {
        $modelName = get_class($model);
        $modelName = strtolower(substr($modelName, 1 + strrpos($modelName, '\\')));

        if(!method_exists($this, $modelName) and !method_exists($this, str_plural($modelName))) {
            throw new \Exception(self::class.' missing relationship to model of type '.get_class($model));
        }

        if(method_exists($model, 'images')) {
            $method = 'images';
        } else {
            throw new \Exception('Model of type '.get_class($model).' is missing a relationship to '.self::class);
        }

        if(!isset($details['name'])) {
            $details['name'] = $this->name;
        }

        if(method_exists($model, 'image')) {
            $method = 'image';
        }

        $relationship = $model->images();

        $models = [
            $this->id => [
                'order'=>1,
                'role'=>$role,
                'details'=>json_encode($details)
                ]
            ];

        $relationship->sync($models);

        return $relationship->where('id', $this->id)->first();
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
     * @param closure $transformations
     * @return \ColorTools\Image
     * @throws Exception
     */
    public function getUrl($transformations = null, $type='jpeg')
    {
        if(config('colortools.router.returnRelativeUrls', true)) {
            return $this->getRelativeUrl($transformations, $type);
        } else {
            return $this->getAbsoluteUrl($transformations, $type);
        }
    }

    /**
     * @param null $transformations
     * @param string $type
     * @return string
     */
    public function getRelativeUrl($transformations = null, $type='jpeg')
    {
        return route(config('colortools.router.namedPrefix').'.get', \ColorTools\Store::getHashAndTransformations($this->hash, $transformations, $type), false);
    }

    /**
     * @param null $transformations
     * @param string $type
     * @return string
     */
    public function getAbsoluteUrl($transformations = null, $type='jpeg')
    {
        return route(config('colortools.router.namedPrefix').'.get', \ColorTools\Store::getHashAndTransformations($this->hash, $transformations, $type), true);
    }


    /**
     * @param closure $closure
     * @return \ColorTools\Image
     * @throws Exception
     */
    public function modifyImage($closure = null)
    {
        return $this->getStore()->modifyImage($closure);
    }

    /**
     * @param closure $closure
     * @return string
     * @throws Exception
     */
    public function modifyImagePublish($closure = null, $type='jpeg')
    {
        return $this->modifyImage($closure)->publish($type);
    }

    /**
     * @param string $type
     * @return mixed|string
     * @throws Exception
     */
    public function publish($type='jpeg')
    {
        return $this->getStore()->publish($type);
    }

    /**
     * @param $modifiersString
     * @return Store
     * @throws Exception
     */
    public function processModifiersString($modifiersString)
    {
        return $this->getStore()->processModifiersString($modifiersString);
    }
}