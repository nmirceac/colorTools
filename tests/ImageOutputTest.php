<?php

use ColorTools\Image;

class ImageOutputTest extends PHPUnit\Framework\TestCase
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

    public function testCheckImageContentString()
    {
        $imagePath = $this->testImgPath;
        $content = file_get_contents($imagePath);
        $image = Image::create($imagePath);
        $this->assertEquals($image->getImageContent(), $content);
    }

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

    /**
     * @runInSeparateProcess
     */
    public function testCheckGdDisplayType()
    {
        $image = Image::create(imagecreatefromjpeg($this->testImgPath));
        $this->assertEquals(Image::create($image->displayImage('jpeg'))->type, 'jpeg');
        $this->assertEquals(Image::create($image->displayImage('png'))->type, 'png');
        $this->assertEquals(Image::create($image->displayImage('gif'))->type, 'gif');
    }

    /**
     * @runInSeparateProcess
     */
    public function testCheckImagickDisplayType()
    {
        $image = Image::create(new Imagick($this->testImgPath));
        $this->assertEquals(Image::create($image->displayImage('jpeg'))->type, 'jpeg');
        $this->assertEquals(Image::create($image->displayImage('png'))->type, 'png');
        $this->assertEquals(Image::create($image->displayImage('gif'))->type, 'gif');
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    Headers already sent by
     */
    public function testCheckDisplayHeaderException()
    {
        $image = Image::create($this->testImgPath);
        $image->displayImage();
    }


}