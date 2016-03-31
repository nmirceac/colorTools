<?php

use ColorTools\Image;

class ImageTest extends PHPUnit_Framework_TestCase
{
    private $testImgPath = './samples/test-small.jpg';

    public function testThatCheckImageClassWorks()
    {
        $image=Image::create($this->testImgPath);
        $this->assertInstanceOf('ColorTools\Image', $image);
    }

    public function testCheckImageFromString()
    {
        $imageString=file_get_contents($this->testImgPath);
        $image=Image::create($imageString);
        $this->assertEquals($image->getImageType(), 'string');
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
        $this->assertEquals($image->getImageType(), 'file');
        $this->assertEquals($image->getImagePath(), $imagePath);
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
        $this->assertEquals($image->getImageType(), 'url');
        $this->assertEquals($image->getImagePath(), $imageUrl);
        $this->assertEquals($image->type, 'png');
        $this->assertEquals($image->mime, 'image/png');
        $this->assertEquals($image->width, 50);
        $this->assertEquals($image->height, 46);
    }

    public function testCheckImageFromGdResource()
    {
        $gdResource = imagecreatefromgif('./samples/test.gif');
        $image=Image::create($gdResource);
        $this->assertEquals($image->getImageType(), 'gd');
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
        $this->assertEquals($image->getImageType(), 'imagick');
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
        $this->assertEquals($image->getImageType(), 'file');
        $this->assertEquals($image->getImagePath(), $imagePath);
        $this->assertEquals($image->type, 'jpeg');
        $this->assertEquals($image->mime, 'image/jpeg');
        $this->assertEquals($image->width, 2464);
        $this->assertEquals($image->height, 1632);
    }

    public function testCheckImageFromNoExtension()
    {
        $imagePath='./samples/test-just-a-file';
        $image=Image::create($imagePath);
        $this->assertEquals($image->getImageType(), 'file');
        $this->assertEquals($image->getImagePath(), $imagePath);
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