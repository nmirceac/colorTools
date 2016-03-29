<?php

use ColorTools\Image;
use ColorTools\Color;

class ImageTest extends PHPUnit_Framework_TestCase
{
    public function testCheckThatTheClassWorks()
    {
        #$jpegString=file_get_contents(dirname(__FILE__).'/../samples/test.jpeg');
        $jpegString=file_get_contents('../samples/test.jpeg');
        $jpegString=Image::create($jpegString);
        $this->assertEquals($jpegString->getImageType(), 'string');
        $this->assertEquals($jpegString->getImagePath(), null);
        $this->assertEquals($jpegString->type, 'jpeg');
        $this->assertEquals($jpegString->mime, 'image/jpeg');
        $this->assertEquals($jpegString->width, 30);
        $this->assertEquals($jpegString->height, 30);

    }




    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    Cannot find property
     */
    public function testGettingAnInvalidProperty2()
    {
        $color = new Color();
        $color -> width;
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    What are you trying to do here with
     */
    public function testSettingAnInvalidProperty2()
    {
        $color = new Color();
        $color -> cheese = 'brie';
    }
}