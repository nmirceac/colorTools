<?php

use ColorTools\Image;

class ImageTest extends PHPUnit\Framework\TestCase
{
    private $testImgPath = './samples/test-small.jpg';

    public function setup()
    {
        $this->assertSamples();
    }

    public function assertSamples()
    {
        if(!file_exists($this->testImgPath)) {
            $this->markTestSkipped('Samples are missing - run php getSamples.php');
        }
    }

    public function testThatCheckImageClassWorks()
    {
        $image=Image::create($this->testImgPath);
        $this->assertInstanceOf('ColorTools\Image', $image);
    }

    public function testCheckImageFromString()
    {
        $imageString=file_get_contents($this->testImgPath);
        $image=Image::create($imageString);
        $this->assertEquals($image->getImageType(), Image::IMAGE_TYPE_STRING);
        $this->assertEquals($image->getImagePath(), null);
        $this->assertEquals($image->type, 'jpeg');
        $this->assertEquals($image->mime, 'image/jpeg');
        $this->assertEquals($image->width, 100);
        $this->assertEquals($image->height, 66);
    }

    public function testCheckImageFromPath()
    {
        $imagePath=$this->testImgPath;
        $image=Image::create($imagePath);
        $this->assertEquals($image->getImageType(), Image::IMAGE_TYPE_FILE);
        $this->assertEquals($image->getImagePath(), realpath($imagePath));
        $this->assertEquals($image->type, 'jpeg');
        $this->assertEquals($image->mime, 'image/jpeg');
        $this->assertEquals($image->width, 100);
        $this->assertEquals($image->height, 66);
    }

    public function testCheckImageFromUrl()
    {
        if(!(getenv('ONLINE') or getenv('EXTENDED'))) {
            $this->markTestSkipped('URL test skipped - run with ONLINE or EXTENDED environment variables');
        }

        $imageUrl=getenv('TEST_URL');
        $image=Image::create($imageUrl);
        $this->assertEquals($image->getImageType(), Image::IMAGE_TYPE_URL);
        $this->assertEquals($image->getImagePath(), $imageUrl);
        $this->assertEquals($image->type, 'png');
        $this->assertEquals($image->mime, 'image/png');
        $this->assertEquals($image->width, 100);
        $this->assertEquals($image->height, 66);
    }

    public function testCheckImageFromGdResource()
    {
        $gdResource = imagecreatefromgif('./samples/test.gif');
        $image=Image::create($gdResource);
        $this->assertEquals($image->getImageType(), Image::IMAGE_TYPE_GD);
        $this->assertEquals($image->getImagePath(), null);
        $this->assertEquals($image->type, null);
        $this->assertEquals($image->mime, null);
        $this->assertEquals($image->width, 2464);
        $this->assertEquals($image->height, 1632);
    }

    public function testCheckImageFromImagickObject()
    {
        $imagickResource = new Imagick($this->testImgPath);
        $image=Image::create($imagickResource);
        $this->assertEquals($image->getImageType(), Image::IMAGE_TYPE_IMAGICK);
        $this->assertEquals($image->getImagePath(), null);
        $this->assertEquals($image->type, null);
        $this->assertEquals($image->mime, null);
        $this->assertEquals($image->width, 100);
        $this->assertEquals($image->height, 66);
    }

    public function testCheckImageFromWrongExtension()
    {
        $imagePath='./samples/test-wrong-ext.png';
        $image=Image::create($imagePath);
        $this->assertEquals($image->getImageType(), Image::IMAGE_TYPE_FILE);
        $this->assertEquals($image->getImagePath(), realpath($imagePath));
        $this->assertEquals($image->type, 'jpeg');
        $this->assertEquals($image->mime, 'image/jpeg');
        $this->assertEquals($image->width, 2464);
        $this->assertEquals($image->height, 1632);
    }

    public function testCheckImageFromNoExtension()
    {
        $imagePath='./samples/test-just-a-file';
        $image=Image::create($imagePath);
        $this->assertEquals($image->getImageType(), Image::IMAGE_TYPE_FILE);
        $this->assertEquals($image->getImagePath(), realpath($imagePath));
        $this->assertEquals($image->type, 'jpeg');
        $this->assertEquals($image->mime, 'image/jpeg');
        $this->assertEquals($image->width, 2464);
        $this->assertEquals($image->height, 1632);
    }

    public function testCheckImageObject()
    {
        // Imagick if you like magick
        $this->assertInstanceOf('Imagick', Image::create(new Imagick($this->testImgPath))->getImageObject());

        // gd for everyone else
        $this->assertEquals('gd',
            get_resource_type(Image::create($this->testImgPath)->getImageObject()));

        $this->assertEquals('gd',
            get_resource_type(Image::create(file_get_contents($this->testImgPath))->getImageObject()));

        $this->assertEquals('gd',
            get_resource_type(Image::create(imagecreatefromjpeg($this->testImgPath))->getImageObject()));

        if(!(getenv('ONLINE') or getenv('EXTENDED'))) {
            $this->markTestIncomplete('URL test skipped - run with ONLINE or EXTENDED environment variables');
        }

        $this->assertEquals('gd',
            get_resource_type(Image::create(getenv('TEST_URL'))->getImageObject()));
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    Unknown property
     */
    public function testGettingAnUnkownProperty()
    {
        $image = new Image($this->testImgPath);
        $image -> cheese;
    }
}