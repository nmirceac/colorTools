<?php

use ColorTools\Color;

class ColorTest extends PHPUnit_Framework_TestCase
{
    public function testCheckThatTheClassWorks()
    {
        $color = new Color();

        /*
         * Nothing fancy
         */

        $this->assertTrue(get_class($color) == 'ColorTools\Color');

        /*
         * Check that the default colour is int 0
         */

        $this->assertEquals($color->int, 0);

        /*
         * Check if we can get the color name
         */
        $this->assertEquals($color->name, 'black');

        /*
         * Check if we can get a nice color name
         */
        $this->assertContains('black', $color->fullName);

        /*
         * Check if we can get a link about this color
         */
        $this->assertStringStartsWith('http', $color->url);
    }

    public function testLuma()
    {
        $this->assertEquals(Color::create('white')->getLuma(), 1);

        $this->assertEquals(Color::create('black')->getLuma(), 0);
    }

    public function testSettingCustomProperty()
    {
        $color = new Color('Classic rose');
        $color -> _smell = 'sweet';
        $this->assertEquals($color->_smell, 'sweet');
        // a property that hasn't been set returns null
        $this->assertNull($color->_size, 'sweet');
    }

    public function testConsistencyCheck()
    {
        $color = Color::create('red');
        $this->assertEquals($color->int, Color::create($color)->int);
        $this->assertEquals($color->int, Color::create($color->int)->int);
        $this->assertEquals($color->int, Color::create($color->hex)->int);
        $this->assertEquals($color->int, Color::create($color->rgb)->int);
        $this->assertEquals($color->int, Color::create($color->hsl)->int);
        $this->assertEquals($color->int, Color::create($color->hsv)->int);
        $this->assertEquals($color->int, Color::create($color->cmyk)->int);

        $hex='#00ff00';
        $color = Color::create($hex);
        $this->assertEquals($color->setHex($color->getHex())->hex, $hex, 'Hex');
        $this->assertEquals($color->setRgb($color->getRgb())->hex, $hex, 'RGB');
        $this->assertEquals($color->setHsl($color->getHsl())->hex, $hex, 'HSL');
        $this->assertEquals($color->setHsv($color->getHsv())->hex, $hex, 'HSV');
        $this->assertEquals($color->setCmyk($color->getCmyk())->hex, $hex, 'CMYK');
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    Cannot find property
     */
    public function testGettingAnInvalidProperty()
    {
        $color = new Color();
        $color -> width;
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    What are you trying to do here with
     */
    public function testSettingAnInvalidProperty()
    {
        $color = new Color();
        $color -> cheese = 'brie';
    }
}