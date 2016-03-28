<?php

use ColorTools\Color;

class ColorFunctionsTest extends PHPUnit_Framework_TestCase
{
    public function testGrayscale()
    {
        $this->assertEquals(Color::create('white')->getGrayscale(), 255);
        $this->assertEquals(Color::create('black')->getGrayscale(), 0);

        $this->assertEquals(Color::create('red')->getGrayscale(), 85);
        $this->assertEquals(Color::create('lime')->getGrayscale(), 85);
        $this->assertEquals(Color::create('blue')->getGrayscale(), 85);

        $this->assertEquals(Color::create('gray')->getGrayscale(), 128);
        $this->assertEquals(Color::create('darkgray')->getGrayscale(), 169);
        $this->assertEquals(Color::create('silver')->getGrayscale(), 192);
        $this->assertEquals(Color::create('lightgray')->getGrayscale(), 211);
    }

    public function testLuma()
    {
        $this->assertEquals(Color::create('white')->getLuma(), 1);
        $this->assertEquals(Color::create('black')->getLuma(), 0);

        $this->assertEquals(Color::create('red')->luma, '21%');
        $this->assertEquals(Color::create('lime')->luma, '72%');
        $this->assertEquals(Color::create('blue')->luma, '7%');

        $this->assertEquals(Color::create('gray')->luma, '50%');
        $this->assertEquals(Color::create('darkgray')->luma, '66%');
        $this->assertEquals(Color::create('silver')->luma, '75%');
        $this->assertEquals(Color::create('lightgray')->luma, '83%');
    }

    public function testInvert()
    {
        $this->assertEquals(Color::create('white')->invert()->name, 'black');
        $this->assertEquals(Color::create('#eeeeee')->invert()->hex, '#111111');
        $this->assertEquals(Color::create('rgb(254, 253, 252)')->invert()->rgb, 'rgb(1, 2, 3)');
    }

    public function testComplement()
    {
        $this->assertEquals(Color::create('red')->complement()->name, 'aqua');
        $this->assertEquals(Color::create('lime')->complement()->name, 'fuchsia');
        $this->assertEquals(Color::create('blue')->complement()->name, 'yellow');

        $this->assertEquals(Color::create('hsl(60, 50, 50)')->complement()->hsl, 'hsl(240, 50%, 50%)');
        $this->assertEquals(Color::create('hsl(120, 50, 50)')->complement()->hsl, 'hsl(300, 50%, 50%)');
        $this->assertEquals(Color::create('hsl(240, 50, 50)')->complement()->hsl, 'hsl(60, 50%, 50%)');
        $this->assertEquals(Color::create('hsl(180, 50, 50)')->complement()->hsl, 'hsl(0, 50%, 50%)');
    }
}