# Welcome to ColorTools!

## Contents
1. Intro
2. Classes, methods and examples

# 1. Intro

## How to install?

- composer require nmirceac/color-tools
- add \ColorTools\ColorToolsServiceProvider::class to your config/app.php's service providers section
- php artisan vendor:publish
- php artisan migrate
- check config/colortools.php (just in case)
- php artisan colortools:setup
- extend \ColorTools\ImageStore as an app model (see example below)
- add the \ColorTools\HasImages
- check the examples below
- enjoy! 


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

# 2. Classes, methods and examples

## Color::class

### Color::class - How to instantiate

```php
    // hex
    Color::create('#fff');
    Color::create('#ffffff');
    Color::create(0xffffff);
    Color::create(0x123456);
    Color::create(123456);
    Color::create("\n #\t 123456 \r".PHP_EOL);
    Color::create('ab');
    Color::create('abcd');
    Color::create('abcde');
    Color::create()->setHex('#336699');
    
    // name
    Color::create('red');
    Color::create('white');
    Color::create('blue');
    Color::create('Lime');
    Color::create('Ball blue');

    // rgb
    Color::create('127, 128, 129');
    Color::create(55, 77, 99);
    Color::create('255, 255, 255;');
    Color::create(['red'=>1, 'green'=>2, 'blue'=>3]);
    Color::create(['red'=>1, 'green'=>2, 'blue'=>3, 'alpha'=>0.5]);
    Color::create(['r'=>1, 'g'=>2, 'b'=>3]);
    Color::create(['r'=>1, 'g'=>2, 'b'=>3, 'a'=>0.5]);
    Color::create([1, 2, 3]);
    Color::create([1, 2, 3, 0.5]);
    Color::create('1, 2, 3');
    Color::create('rgb(1, 2, 3)');
    Color::create('rgb (1,  2 ,    3)');
    Color::create('rgba (1,  2 ,    3)');
    Color::create('rgba (1,  2 ,    3, 0.5)');
    Color::create()->setRgb(1, 2, 3);
    
    // hsl
    Color::create('hsl(180, 50, 50)');
    Color::create('hsla(180, 50, 50, 50)');
    Color::create('hsl(180, 50%, 50%)');
    Color::create('hsl(180, 0.5, 0.5)');
    Color::create()->setHsl(180, 50, 50);
    
    // hsv
    Color::create('hsv(180, 50, 50)');
    Color::create('hsva(180, 50, 50, 50)');
    Color::create('hsv(180, 50%, 50%)');
    Color::create('hsv(180, 0.5, 0.5)');
    Color::create()->setHsv(180, 50, 50);
    
    //cmyk
    Color::create('cmyk(0, 0.6, 1, 0)');
    Color::create('cmyk(0%, 60%, 100%, 0%)');
    Color::create('cmyk(0, 0.60, 100%, 0)');
    Color::create()->setCmyk(0, 0.60, 1, 0);
        
    // other color
    $color = Color::create('#123456');
    Color::create($color);

```


### Color::class - getters and such

```php
    $color = Color::create('#123456');
    
    // find similar
    
    $similarColor = $color->findSimilar(Color::COMPARE_FAST);
    $similarColor2 = $color->findSimilar(Color::COMPARE_NORMAL);
    $similarColor3 = $color->findSimilar(Color::COMPARE_GREAT);
    
    // all colors
    
    $color->getAllColors();
    $color->allcolors;
    
    // css colors
    
    $color->getCssColors();
    $color->csscolors;
    
    // name
    $color->getName;
    $color->name;
    
    // full name
    $color->getFullName();
    $color->fullname;
    
    // custom property
    $color->smell = 'sweet';
    echo $color->smell; // sweet
    
    // hex
    $color->getHex();
    $color->hex;
    
    // safe
    $color->getSafeHex();
    $color->safe;
    $color->safeHex;
    
    // rgb
    $color->getRgb();
    $color->rgb;
    
    $color->getRed();
    $color->red;
    $color->r;
    
    $color->getGreen();
    $color->green;
    $color->g;
    
    $color->getBlue();
    $color->blue;
    $color->b;
    
    // grayscale
        
    $color->getGrayscale();
    $color->grayscale;    
    $color->gray;    
    $color->mono;
    
    // luma
    
    $color->getLuma();
    $color->luma;
    
    // hsl
        
    $color->getHsl();
    $color->hsl;
    
    // hsv
    $color->getHsv();
    $color->hsv;
    
    // cmyk
    $color->getCmyk();
    $color->cmyk;
    
    // integer
    $color->value;
    $color->int;

```


### Color::class - setters and modifiers

```php
    $color = Color::create('#123456');
    
    // modifiy rgb
    
    $color->setRed(0);
    $color->red = 55;
    $color->r = 128;
    
    $color->setGreen(0);
    $color->green = 55;
    $color->g = 128;
        
    $color->setBlue(0);
    $color->blue = 55;
    $color->b = 128;
    
    $color->setRgb(15, 25, 35);
    
    // invert
    
    $color->invert();
    echo Color::create('white')->invert()->name; // black 
    
    // complement
    
    $color->complement();
    echo Color::create('red')->complement()->name; // aqua
    
    // triad
    
    $color->triad(1);
    $color->triad(2);
    
    // tetrad
    
    $color->tetrad(1);
    $color->tetrad(2);
    $color->tetrad(3);
   
    // mixing
    
    echo Color::create('white')->mix('red', 50)->name; // salmon
    echo Color::create('white')->mix('red', 0.5)->name; // salmon
    echo Color::create('white')->mix('red', 100)->name; // red
    
    $black = Color::create('black');
    echo Color::create('white')->mix($black, 50)->hex; // #808080
    
    // tinting
    
    echo Color::create('blue')->tint(100)->name; // white
    
    // shading
    
    echo Color::create('blue')->shade(100)->name; // black
    
    // grayscaling
    
    echo Color::create('blue')->grayscale()->name; // gray
    
    // spinning
    
    $color = Color::create('red');
    echo $color->name; // red
    $color -> spin(120);
    echo $color->name; // lime
    $color -> spin(-240);
    echo $color->name; // blue
    $color -> spin(120);
    echo $color->name; // red
    $color -> spin(420);
    echo $color->name; // yellow
    $color -> spin(120);
    echo $color->name; // aqua
    $color -> spin(120);
    echo $color->name; // fuchsia
    echo $color->getHsl()['hue'] // 300;
    
    // saturate / desaturate
    
    $color -> saturate(10);
    $color -> saturate(0.2);
    $color -> saturate(-0.2);
    $color -> desaturate(10);
    
    // lighten / darken
    
    $color -> lighten(10);
    $color -> lighten(0.2);
    $color -> lighten(-0.2);
    $color -> darken(10);
    
    // mutiply - http://lesscss.org/functions/#color-blending-multiply
    
    echo Color::create('#ff6600')->multiply('#000000')->hex; // #000000
    echo Color::create('#ff6600')->multiply('#333333')->hex; // #331400
    echo Color::create('#ff6600')->multiply('#00ff00')->hex; // #006600
    
    // screen - http://lesscss.org/functions/#color-blending-screen
    
    echo Color::create('#ff6600')->screen('#000000')->hex; // #ff6600
    echo Color::create('#ff6600')->screen('#333333')->hex; // #ff8533
    echo Color::create('#ff6600')->screen('#00ff00')->hex; // #ffff00
    
    // soft light - http://lesscss.org/functions/#color-blending-softlight

    echo Color::create('#ff6600')->softlight('#000000')->hex; // #ff2900
    echo Color::create('#ff6600')->softlight('#333333')->hex; // #ff4100
    echo Color::create('#ff6600')->softlight('#666666')->hex; // #ff5a00
    
    // hard light - http://lesscss.org/functions/#color-blending-hardlight

    echo Color::create('#ff6600')->hardlight('#000000')->hex; // #000000;
    echo Color::create('#ff6600')->hardlight('#333333')->hex; // #662900;
    echo Color::create('#ff6600')->hardlight('#666666')->hex; // #cc5200;

    // difference - http://lesscss.org/functions/#color-blending-difference
    
    echo Color::create('#ff6600')->difference('#000000')->hex; // #ff6600
    echo Color::create('#ff6600')->difference('#333333')->hex; // #cc3333
    echo Color::create('#ff6600')->difference('#666666')->hex; // #990066
    
    // exclusion - http://lesscss.org/functions/#color-blending-exclusion
    
    echo Color::create('#ff6600')->exclusion('#000000')->hex; // #ff6600
    echo Color::create('#ff6600')->exclusion('#333333')->hex; // #cc7033
    echo Color::create('#ff6600')->exclusion('#666666')->hex; // #997a66
    
    // average - http://lesscss.org/functions/#color-blending-average
    
    echo Color::create('#ff6600')->average('#000000')->hex; // #803300
    echo Color::create('#ff6600')->average('#333333')->hex; // #994d1a
    echo Color::create('#ff6600')->average('#666666')->hex; // #b36633
    
    // negation - http://lesscss.org/functions/#color-blending-negation
    
    echo Color::create('#ff6600')->negate('#000000')->hex; // #ff6600
    echo Color::create('#ff6600')->negate('#333333')->hex; // #cc9933
    echo Color::create('#ff6600')->negate('#666666')->hex; // #99cc66
    
    // contrast
    echo Color::create('white')->findConstrast()->name; //  black
    echo Color::create('black')->findConstrast()->name; //  white
    echo Color::create('brown')->findConstrast()->name; //  white
    echo Color::create('pink')->findConstrast()->name; //   black

```

## Image::class

### Image::class - How to instantiate

```php
    
    // create from file path
    Image::create('pathTo.jpg');
    Image::create('pathTo.png');
    
    // create from string
    Image::create(file_get_contents('pathTo.jpg'));
    
    // create from URL
    Image::create('http://domain.tld/image.jpg');
    
    // create from GD
    $gdResource = imagecreatefromgif('pathTo.gif');
    Image::create($gdResource);
    
    // create from Imagick
    $imagickResource = new Imagick('pathTo.jpg');
    Image::create($imagickResource);
    
    
```