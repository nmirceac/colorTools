<?php namespace ColorTools;

trait HasImages
{
    public function images() {
        return $this->morphToMany(\App\ImageStore::class,
            'association',
            'image_associations',
            'association_id',
            'image_id'
        )->withPivot(\App\ImageStore::$withPivot);
    }
}
