<?php

namespace ColorTools\Http\Controllers;

use Illuminate\Http\Request;

class ImagesController extends \App\Http\Controllers\Controller
{
    public function index($urlString='')
    {
        $type='jpeg';
        if(strrpos($urlString, '.')) {
            $type = substr($urlString, 1 + strrpos($urlString, '.'));
        }

        $store = \ColorTools\Store::findAndProcess($urlString);
        $store->publish($type);
        header('Content-type: image/'.$type);
        echo file_get_contents($store->getPublishPath($type));
    }

    public function histogram($type='c', $id)
    {
        return \App\ImageStore::find($id)->showHistogram($type);
    }

    public function upload()
    {
        $roleName = request('role');

        $excludeFromDetails = ['file', 'modelId', 'model', 'single', 'role', $roleName];
        $details = request()->except($excludeFromDetails);

        $image = \App\ImageStore::createFromRequest(request(), $roleName);


        if (request('model', false) and request('modelId', false)) {

            $model = '\\App\\' . ucfirst(request('model'));
            $model = new $model;
            $object = $model->find(request('modelId'));

            if (request('single', false)) {
                $image = $image->set($object, $roleName, $details);
            } else {
                $image = $image->attach($object, $roleName, 'last', $details);
            }

        }

        return response()->json(['image' => $image]);
    }

    public function attach()
    {
        $image = \App\ImageStore::findOrFail(request('id'));

        $roleName = request('role');

        if (request('model', false) and request('modelId', false)) {

            $model = '\\App\\' . ucfirst(request('model'));
            $model = new $model;
            $object = $model->find(request('modelId'));

            if (request('single', false)) {
                $image = $image->set($object, $roleName, []);
            } else {
                $image = $image->attach($object, $roleName, 'last', []);
            }

        }

        return response()->json(['image' => $image]);
    }

    public function delete()
    {
        $image = \App\ImageStore::findOrFail(request('id'));

        if (request('model', false) and request('modelId', false)) {

            $model = '\\App\\' . ucfirst(request('model'));
            $model = new $model;
            $object = $model->find(request('modelId'));

            if (request('id')) {
                $object->clearImage(request('id'));
            } elseif (request('role')) {
                $object->clearImagesByRole(request('role'));
            }
        }

        $image->delete();
    }

    public function replace()
    {
        $originalImage = \App\ImageStore::findOrFail(request('id'));
        $roleName = request('role');

        $newImage = $originalImage->replaceFromRequest(request(), $roleName);

        $imageMeta = (array)$newImage->metadata;

        $metadata = request()->only(['caption', 'copyright', 'tags']);
        foreach ($metadata as $key => $data) {
            if ($data == "null" || is_null($data)) {
                if ($key !== 'tags') {
                    $imageMeta[$key] = '';
                } else {
                    $imageMeta[$key] = [];
                }
            } elseif ($key == 'tags') {
                $imageMeta['tags'] = explode(',', $data);
            } else {
                $imageMeta[$key] = $data;
            }
        }

        $newImage->metadata = $imageMeta;
        $newImage->update();

        return response()->json(['image' => $newImage]);
    }

    public function preview($imageId)
    {
        $image = \App\ImageStore::findOrFail($imageId);

        return $image->serve();
    }

    public function download($imageId)
    {
        $image = \App\ImageStore::findOrFail($imageId);

        return $image->serveForceDownload();
    }

    public function update()
    {
        $image = \App\ImageStore::findOrFail(request('id'));

        if (request('model', false) and request('modelId', false)) {
            $model = '\\App\\' . ucfirst(request('model'));
            $model = new $model;
            $object = $model->find(request('modelId'));
            $image->attach($object, request('role'), request('order'), request('metadata'));
        } else {
            $image->metadata = request('metadata');
            $image->update();
        }


        return response()->json(['image' => $image]);
    }

    public function reorder()
    {
        $model = '\\App\\' . ucfirst(request('model'));
        $model = new $model;
        $object = $model->find(request('modelId'));
        $object->reorderImagesByRole(request('ids'), request('role'));
    }

    public function associations()
    {
        $model = '\\App\\' . ucfirst(request('model'));
        $model = new $model;
        $object = $model->find(request('modelId'));

        $traits = (new \ReflectionClass($object))->getTraits();

        if(!is_null($object) and array_key_exists('ColorTools\HasImages', $traits)) {
            return response()->json(['images' => $object->images]);
        } else {
            return response()->json(['images' => []]);
        }
    }
}
