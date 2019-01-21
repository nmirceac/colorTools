<?php

use ColorTools\Image;
use ColorTools\Store;

class StoreOutputTest extends PHPUnit\Framework\TestCase
{
    private $testImgJpegPath = './samples/test-small.jpg';
    private $testImgPngPath = './samples/test-small.png';
    private $testImgGifPath = './samples/test-small.gif';

    public static $storeJpeg = null;
    public static $storePng = null;
    public static $storeGif = null;

    private $settings = [
        'storeBasePath'=>'./store_test/',
        'publicPattern'=>'./images_test/%hash%',
        'publicPath'=>'images_test'
    ];

    public function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir."/".$object))
                        $this->rrmdir($dir."/".$object);
                    else
                        unlink($dir."/".$object);
                }
            }
            rmdir($dir);
        }
    }

    public function setup()
    {
        $this->assertSamples();
    }

    public function assertSamples()
    {
        if(!file_exists($this->testImgJpegPath)) {
            $this->markTestSkipped('Samples are missing - run php getSamples.php');
        }
    }

    public function testStoreCreate()
    {
        Store::settings($this->settings);
        $this->rrmdir($this->settings['storeBasePath']);

        $store = Store::create($this->testImgJpegPath);
        $this->assertInstanceOf(Store::class, $store);
    }

    public function testStoreStore()
    {
        self::$storePng = Store::create($this->testImgPngPath);
        self::$storePng->store();
        $storePath = $this->settings['storeBasePath'].'/'.substr(self::$storePng->getHash(), 0, 2).'/'.self::$storePng->getHash();
        $this->assertFileExists($storePath);
        $this->assertEquals('png', Image::create($storePath)->type);

        self::$storeJpeg = Store::create($this->testImgJpegPath);
        self::$storeJpeg->store();
        $storePath = $this->settings['storeBasePath'].'/'.substr(self::$storeJpeg->getHash(), 0, 2).'/'.self::$storeJpeg->getHash();
        $this->assertFileExists($storePath);
        $this->assertEquals('jpeg', Image::create($storePath)->type);

        self::$storeGif = Store::create($this->testImgGifPath);
        self::$storeGif->store();
        $storePath = $this->settings['storeBasePath'].'/'.substr(self::$storeGif->getHash(), 0, 2).'/'.self::$storeGif->getHash();
        $this->assertFileExists($storePath);
        $this->assertEquals('gif', Image::create($storePath)->type);
    }

    public function testStoreFindByHash()
    {
        $store = Store::findByHash(self::$storePng->getHash());
        $this->assertInstanceOf(Store::class, $store);
        $this->assertEquals($store->getSize(), self::$storePng->getSize());
        $this->assertEquals($store->getType(), self::$storePng->getType());
        $this->assertEquals('png', self::$storePng->getType());

        $store = Store::findByHash(self::$storeJpeg->getHash());
        $this->assertInstanceOf(Store::class, $store);
        $this->assertEquals($store->getSize(), self::$storeJpeg->getSize());
        $this->assertEquals($store->getType(), self::$storeJpeg->getType());
        $this->assertEquals('jpeg', self::$storeJpeg->getType());

        $store = Store::findByHash(self::$storeGif->getHash());
        $this->assertInstanceOf(Store::class, $store);
        $this->assertEquals($store->getSize(), self::$storeGif->getSize());
        $this->assertEquals($store->getType(), self::$storeGif->getType());
        $this->assertEquals('gif', self::$storeGif->getType());
    }

    public function testStorePublish()
    {
        $store = Store::findByHash(self::$storeJpeg->getHash());
        $this->assertInstanceOf(Store::class, $store);

        $store->publish();

    }


    /*

    public function testCheckImageContentStringSrc()
    {
        $imagePath = $this->testImgPath;
        $content = file_get_contents($imagePath);
        $image = Image::create($imagePath);
        $this->assertEquals($image->getImageSrc(),
            'data:image/'.$image->type.';base64, '.base64_encode($content));
    }

    public function testCheckImageContentFile()
    {
        $imageString = file_get_contents($this->testImgPath);
        $image = Image::create($imageString);
        $this->assertEquals($image->getImageContent(), $imageString);
    }

    public function testCheckImageContentFileStringSrc()
    {
        $imageString = file_get_contents($this->testImgPath);
        $image = Image::create($imageString);
        $this->assertEquals($image->getImageSrc(),
            'data:image/'.$image->type.';base64, '.base64_encode($imageString));
    }

    public function testCheckImageContentUrl()
    {
        if(!(getenv('ONLINE') or getenv('EXTENDED'))) {
            $this->markTestSkipped('URL test skipped - run with ONLINE or EXTENDED environment variables');
        }

        $imageString = file_get_contents(getenv('TEST_URL'));
        $image = Image::create(getenv('TEST_URL'));
        $this->assertEquals($image->getImageContent(), $imageString);
    }

    public function testCheckImageContentUrlStringSrc()
    {
        if(!(getenv('ONLINE') or getenv('EXTENDED'))) {
            $this->markTestIncomplete('URL test skipped - run with ONLINE or EXTENDED environment variables');
        }

        $image = Image::create(getenv('TEST_URL'));
        $this->assertEquals($image->getImageSrc(), getenv('TEST_URL'));
    }

    public function testCheckGdOutputType()
    {
        $image = Image::create(imagecreatefromjpeg($this->testImgPath));
        $this->assertEquals(Image::create($image->getImageContent('jpeg'))->type, 'jpeg');
        $this->assertEquals(Image::create($image->getImageContent('png'))->type, 'png');
        $this->assertEquals(Image::create($image->getImageContent('gif'))->type, 'gif');
    }

    public function testCheckImagickOutputType()
    {
        $image = Image::create(new Imagick($this->testImgPath));
        $this->assertEquals(Image::create($image->getImageContent('jpeg'))->type, 'jpeg');
        $this->assertEquals(Image::create($image->getImageContent('png'))->type, 'png');
        $this->assertEquals(Image::create($image->getImageContent('gif'))->type, 'gif');
    }

    public function testCheckGdDisplayType()
    {
        $image = Image::create(imagecreatefromjpeg($this->testImgPath));
        $this->assertEquals(Image::create($image->displayImage('jpeg'))->type, 'jpeg');
        $this->assertEquals(Image::create($image->displayImage('png'))->type, 'png');
        $this->assertEquals(Image::create($image->displayImage('gif'))->type, 'gif');
    }

    public function testCheckImagickDisplayType()
    {
        $image = Image::create(new Imagick($this->testImgPath));
        $this->assertEquals(Image::create($image->displayImage('jpeg'))->type, 'jpeg');
        $this->assertEquals(Image::create($image->displayImage('png'))->type, 'png');
        $this->assertEquals(Image::create($image->displayImage('gif'))->type, 'gif');
    }

    public function testCheckDisplayHeaderException()
    {
        $image = Image::create($this->testImgPath);
        $image->displayImage();
    }

    */

}