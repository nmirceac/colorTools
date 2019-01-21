﻿# Welcome to ColorTools!

## How to install?

 1. composer require nmirceac/color-tools
 2. add \ColorTools\ColorToolsServiceProvider::class to your config/app.php's service providers section
 3. php artisan vendor:publish
 4. php artisan migrate
 5. check config/colortools.php (just in case)
 6. php artisan colortools:setup
 7. extend \ColorTools\ImageStore as an app model (see example below)
 8. add the \ColorTools\HasImages
 9. check the examples below
 10. enjoy! 

Hi! I'm your first Markdown file in **StackEdit**. If you want to learn about StackEdit, you can read me. If you want to play with Markdown, you can edit me. Once you have finished with me, you can create new files by opening the **file explorer** on the left corner of the navigation bar.

## Samples
### \App\ImageStore example


```php
    <?php namespace App;
    
    class ImageStore extends \ColorTools\ImageStore {
        protected $appends = ['basename', 'details', 'thumbnail'];
    
        public function user()
        {
            return $this->morphedByMany(User::class,
                'association',
                'image_associations',
                'association_id',
                'image_id'
            )->withPivot(self::$withPivot);
        }
    
        public function getThumbnailAttribute()
        {
            return $this->getUrl(function(\ColorTools\Image $image) {
                $image->fit(450, 450);
            });
        }
    
        public function fit($width, $height)
        {
            return $this->getUrl(function(\ColorTools\Image $image) use ($width, $height) {
                $image->fit($width, $height);
            });
        }
    }
```


### Associated Model example

```php
    <?php
    
    namespace App;
    
    use App\Jobs\SendResetPasswordEmail;
    use Illuminate\Notifications\Notifiable;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use ColorTools\HasImages;
    
    class User extends Authenticatable
    {
        use SoftDeletes;
        use Notifiable;
        use HasImages;
    
        protected $appends = ['image', 'details'];
        ...
        ...
        ...
	    public function image()
	    {
	        return $this->images()->first();
	    }

	    public function getImageAttribute()
	    {
	        return $this->image();
	    }
	}
```

### Upload Controller Method


```php
    public function upload()  
    {  
      if(!request()->hasFile('image')) {  
	      return response()->json([  
		      'error' => 'Missing image file'  
	      ]);  
      }  
      
      $image = \App\ImageStore::createFromRequest(request(), 'image');  
      
      $data = json_decode(request('data'), true);  
      if(isset($data['model']) and isset($data['id'])) {  
	      $model = '\\App\\'.$data['model'];  
	      $model = $model::find($data['id']);  
      }  
      
      if(isset($model) and $model) {  
	      $image->attach($model);  
      }  
      
      return response()->json(['image'=>$image]);  
    }
```

# Haha

StackEdit stores your files in your browser, which means all your files are automatically saved locally and are accessible **offline!**

