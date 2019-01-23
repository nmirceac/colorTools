<?php namespace ColorTools;

trait HasImages
{
    // protected $excludedRoles;

    /**
     * @return mixed
     */
    public function images() {
        if(!empty($this->excludedRoles)) {
            $query = $this->imagesRelationship();
            foreach($this->excludedRoles as $excludedRole) {
                $query->wherePivot('role', '!=', $this->excludedRoles);
            }
            return $query;
        }
        return $this->imagesRelationship();
    }

    /**
     * @return mixed
     */
    public function imagesRelationship() {
        return $this->morphToMany(\App\ImageStore::class,
            'association',
            'image_associations',
            'association_id',
            'image_id'
        )->withPivot(\App\ImageStore::$withPivot);
    }

    /**
     * @param $role
     * @return mixed
     */
    public function imageByRole($role)
    {
        return $this->imagesRelationship()->wherePivot('role', $role)->first();
    }

    /**
     * @param $role
     * @return mixed
     */
    public function imagesByRole($role)
    {
        return $this->imagesRelationship()->wherePivot('role', $role)->get();
    }

    /**
     * @param $imageId
     * @param bool $delete
     */
    public function clearImage($imageId, $delete = false)
    {
        if($delete) {
            $this->imagesRelationship()->where('id', $imageId)->delete();
        } else {
            $this->imagesRelationship()->detach($imageId);
        }

    }

    /**
     * @param $role
     * @param bool $delete
     */
    public function clearImagesByRole($role, $delete = false)
    {
        if($delete) {
            $this->imagesByRole($role)->delete();
        } else {
            $this->imagesByRole($role)->detach();
        }

    }

    /**
     * @param bool $delete
     */
    public function clearImages($delete = false)
    {
        if($delete) {
            $this->images()->delete();
        } else {
            $this->images()->detach();
        }

    }

    /**
     * @param bool $delete
     */
    public function clearAllImages($delete = false)
    {
        if($delete) {
            $this->imagesRelationship()->delete();
        } else {
            $this->imagesRelationship()->detach();
        }
    }

    /**
     * @param $imageIds
     * @param bool $role
     * @throws \Exception
     */
    public function reorderImagesByRole($imageIds, $role=false)
    {
        if(!empty($imageIds)) {
            $imageIds = array_values($imageIds);
            if(empty($role)) {
                $role = $this->imagesRelationship()->find($imageIds[0])->pivot->role;
            }
        }

        $images = $this->imagesByRole($role);

        if($images->count()!=count($imageIds)) {
            throw new \Exception('Wrong image order count - sent order for '.count($imageIds).' '.
                str_plural('image', count($imageIds)).' instead of '.$images->count());
        }

        foreach($imageIds as $order=>$imageId) {
            $this->imagesRelationship()->find($imageId)->pivot->update(['order' => ($order+1)]);
        }
    }
}
