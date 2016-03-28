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
    }

    public function testLuma()
    {
        $this->assertEquals(Color::create('white')->getLuma(), 1);

        $this->assertEquals(Color::create('black')->getLuma(), 0);
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