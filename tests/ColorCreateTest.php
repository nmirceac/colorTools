<?php

use ColorTools\Color;

class ColorCreateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    I really don't know what this
     */
    public function testCreateFailsColor()
    {
        // really let me know, please
        $color = Color::create('I really like cheese... A lot!');
    }

    public function testCreateHex()
    {
        $hex = '#123456';

        // hex
        $color = Color::create(0x123456);
        $this->assertEquals($color->hex, $hex);

        // int - making a point that int and hex are not different
        $color = Color::create(123456);
        $this->assertNotEquals($color->hex, $hex);

        // string
        $color = Color::create($hex);
        $this->assertEquals($color->hex, $hex);

        // string without #
        $color = Color::create('123456');
        $this->assertEquals($color->hex, $hex);

        // dirty string
        $color = Color::create("\n #\t 123456 \r".PHP_EOL);
        $this->assertEquals($color->hex, $hex);


        // also some of the nice a-f
        $color = Color::create('#abcdef');
        $this->assertEquals($color->hex, '#abcdef');

        // 4096 color format
        $color = Color::create('#abc');
        $this->assertEquals($color->hex, '#aabbcc');

        // assuming that what's missing is 0
        $color = Color::create('ab');
        $this->assertEquals($color->hex, '#0000ab');

        $color = Color::create('abcd');
        $this->assertEquals($color->hex, '#00abcd');

        $color = Color::create('abcde');
        $this->assertEquals($color->hex, '#0abcde');

        $color = Color::create()->setHex('#123123');;
        $this->assertEquals($color->hex, '#123123');

        // using the output of a different color
        $color1 = Color::create('#123123');
        $color2 = Color::create($color1->hex);
        $this->assertEquals($color2->hex, '#123123');
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    Not sure what this hex string is
     */
    public function testCreateHexFailsLong()
    {
        // this hex is too long
        $color = Color::create('#0123abcd');
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage     This integer is out of range
     */
    public function testCreateHexFailsOutOfRange()
    {
        // this hex is also too long
        $color = Color::create(0x1231230);
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage     This integer is out of range
     */
    public function testCreateHexFailsOutOfRangeInt()
    {
        // this ing is too big
        $color = Color::create(16800000);
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage     This integer is out of range
     */
    public function testCreateHexFailsNegative()
    {
        // this int is too cool
        $color = Color::create(-5);
    }

    public function testCreateRgb()
    {
        // create from red, green, blue arguments
        $color = Color::create(1, 2, 3);
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        // create from red, green, blue array
        $color = Color::create(['red'=>1, 'green'=>2, 'blue'=>3]);
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        // create from red, green, blue, alpha array
        $color = Color::create(['red'=>1, 'green'=>2, 'blue'=>3, 'alpha'=>'ville']);
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        // create from r, g, b array
        $color = Color::create(['r'=>1, 'g'=>2, 'b'=>3]);
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        // create from r, g, b, a array
        $color = Color::create(['r'=>1, 'g'=>2, 'b'=>3, 'a'=>'forever young']);
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        // create keyless array with 3 values
        $color = Color::create([1, 2, 3]);
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        // or 4 values
        $color = Color::create([1, 2, 3, 'some alpha stuff that I am ignoring']);
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        // csv string with 3 elements
        $color = Color::create('1, 2,   3');
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        // or 4 elements
        $color = Color::create('1, 2,   3, 0.5');
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        // but if you want to use a string, rather use this format
        $color = Color::create('rgb(1, 2, 3);');
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        // it can even be a little bit dirty
        $color = Color::create('rgb (1,  2  , 3)');
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        // it can even be a little bit or invalid
        $color = Color::create('rgba (1,  2  , 3)');
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        $color = Color::create('rgb (1,  2  , 3)');
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        $color = Color::create('rgb (1,  2  , 3, 50);');
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        // using the setRgb method
        $color = Color::create()->setRgb(1, 2, 3);
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        $color = Color::create()->setRgb(['red'=>1, 'green'=>2, 'blue'=>3]);
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        $color = Color::create()->setRgb(['red'=>1, 'green'=>2, 'blue'=>3, 'alpha'=>'ville']);
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        $color = Color::create()->setRgb(['r'=>1, 'g'=>2, 'b'=>3]);
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        $color = Color::create()->setRgb(['r'=>1, 'g'=>2, 'b'=>3, 'a'=>'forever young']);
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        $color = Color::create()->setRgb([1, 2, 3]);
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        $color = Color::create()->setRgb([1, 2, 3, 'some alpha stuff that I am ignoring']);
        $this->assertEquals($color->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        // using the output of a different color
        $color1 = Color::create(['red'=>1, 'green'=>2, 'blue'=>3]);
        $color2 = Color::create($color1->getRgb());
        $this->assertEquals($color2->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);

        $color3 = Color::create($color1->rgb);
        $this->assertEquals($color2->getRgb(), ['red'=>1, 'green'=>2, 'blue'=>3]);
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    There is something wrong with this blue
     */
    public function testCreateRgbFailsRange()
    {
        // the rgb values are out of range
        $color = Color::create(['red'=>1, 'green'=>2, 'blue'=>1000]);
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    There is something wrong with this green
     */
    public function testCreateRgbFailsRange2()
    {
        // the rgb values are out of range
        $color = Color::create(['red'=>1, 'green'=>-2, 'blue'=>5]);
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    I cannot make sense of this array
     */
    public function testCreateRgbFailsParams()
    {
        // you need (for now) to specify all the rgb channels
        $color = Color::create(['red'=>1, 'blue'=>5]);
    }


    public function testCreateHsl()
    {
        // create from hsl string
        $color = Color::create('hsl(180, 50, 50)');
        $this->assertEquals($color->getHsl(), ['hue'=>180, 'saturation'=>0.5, 'lightness'=>0.5]);

        // create from hsla string
        $color = Color::create('hsla(180, 50, 50, 50)');
        $this->assertEquals($color->getHsl(), ['hue'=>180, 'saturation'=>0.5, 'lightness'=>0.5]);

        // works with %
        $color = Color::create('hsl(180, 50%, 50%)');
        $this->assertEquals($color->getHsl(), ['hue'=>180, 'saturation'=>0.5, 'lightness'=>0.5]);

        // or decimals
        $color = Color::create('hsl(180, 0.5, 0.5)');
        $this->assertEquals($color->getHsl(), ['hue'=>180, 'saturation'=>0.5, 'lightness'=>0.5]);

        // same here
        $color = Color::create()->setHsl(180, 50, 50);
        $this->assertEquals($color->getHsl(), ['hue'=>180, 'saturation'=>0.5, 'lightness'=>0.5]);

        $color = Color::create()->setHsl(180, '50%', '50%');
        $this->assertEquals($color->getHsl(), ['hue'=>180, 'saturation'=>0.5, 'lightness'=>0.5]);

        $color = Color::create()->setHsl(180, 0.5, 0.5);
        $this->assertEquals($color->getHsl(), ['hue'=>180, 'saturation'=>0.5, 'lightness'=>0.5]);

        // even with the output of another hsl
        $color1 = Color::create('hsl(180, 50, 50)');
        $color2 = Color::create($color1->hsl);
        $this->assertEquals($color2->getHsl(), ['hue'=>180, 'saturation'=>0.5, 'lightness'=>0.5]);
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    This hue is out of range
     */
    public function testCreateHslFailsParamsH()
    {
        $color = Color::create('hsl(400, 50, 50)');
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    This saturation is out of range
     */
    public function testCreateHslFailsParamsS()
    {
        $color = Color::create('hsl(200, -50, 50)');
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    This lightness is out of range
     */
    public function testCreateHslFailsParamsL()
    {
        $color = Color::create('hsl(200, 50, 150)');
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    Can't really understand this HSL string
     */
    public function testCreateHslFailsParamsInvalid()
    {
        $color = Color::create('hsl(200, 50)');
    }

    public function testCreateHsv()
    {
        // create from hsv string
        $color = Color::create('hsv(180, 50, 50)');
        $this->assertEquals($color->getHsv(), ['hue'=>180, 'saturation'=>0.5, 'value'=>0.5]);

        // create from hsva string
        $color = Color::create('hsva(180, 50, 50, 50)');
        $this->assertEquals($color->getHsv(), ['hue'=>180, 'saturation'=>0.5, 'value'=>0.5]);

        // works with %
        $color = Color::create('hsv(180, 50%, 50%)');
        $this->assertEquals($color->getHsv(), ['hue'=>180, 'saturation'=>0.5, 'value'=>0.5]);

        // or decimals
        $color = Color::create('hsv(180, 0.5, 0.5)');
        $this->assertEquals($color->getHsv(), ['hue'=>180, 'saturation'=>0.5, 'value'=>0.5]);

        // same here
        $color = Color::create()->setHsv(180, 50, 50);
        $this->assertEquals($color->getHsv(), ['hue'=>180, 'saturation'=>0.5, 'value'=>0.5]);

        $color = Color::create()->setHsv(180, '50%', '50%');
        $this->assertEquals($color->getHsv(), ['hue'=>180, 'saturation'=>0.5, 'value'=>0.5]);

        $color = Color::create()->setHsv(180, 0.5, 0.5);
        $this->assertEquals($color->getHsv(), ['hue'=>180, 'saturation'=>0.5, 'value'=>0.5]);

        // even with the output of another hsv
        $color1 = Color::create('hsv(180, 50, 50)');
        $color2 = Color::create($color1->hsv);
        $this->assertEquals($color2->getHsv(), ['hue'=>180, 'saturation'=>0.5, 'value'=>0.5]);
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    This hue is out of range
     */
    public function testCreateHsvFailsParamsH()
    {
        $color = Color::create('hsv(400, 50, 50)');
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    This saturation is out of range
     */
    public function testCreateHsvFailsParamsS()
    {
        $color = Color::create('hsv(200, -50, 50)');
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    This value is out of range
     */
    public function testCreateHsvFailsParamsV()
    {
        $color = Color::create('hsv(200, 50, 150)');
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    Can't really understand this HSV string
     */
    public function testCreateHsvFailsParamsInvalid()
    {
        $color = Color::create('hsv(200, 50)');
    }


    public function testCreateCmyk()
    {
        // create from cmyk string using decimals
        $color = Color::create('cmyk(0, 0.6, 1, 0)');
        $this->assertEquals($color->getCmyk(), ['cyan'=>0, 'magenta'=>0.6, 'yellow'=>1, 'black'=>0]);

        // percentages
        $color = Color::create('cmyk(0%, 60%, 100%, 0%)');
        $this->assertEquals($color->getCmyk(), ['cyan'=>0, 'magenta'=>0.6, 'yellow'=>1, 'black'=>0]);

        // or both
        $color = Color::create('cmyk(0, 0.60, 100%, 0)');
        $this->assertEquals($color->getCmyk(), ['cyan'=>0, 'magenta'=>0.6, 'yellow'=>1, 'black'=>0]);

        $color = Color::create()->setCmyk(0, 0.60, 1, 0);
        $this->assertEquals($color->getCmyk(), ['cyan'=>0, 'magenta'=>0.6, 'yellow'=>1, 'black'=>0]);

        // or with the output of another cmyk
        $color1 = Color::create('cmyk(0, 0.60, 100%, 0)');
        $color2 = Color::create($color1->cmyk);
        $this->assertEquals($color2->getCmyk(), ['cyan'=>0, 'magenta'=>0.6, 'yellow'=>1, 'black'=>0]);
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    Can't really understand this CMYK string
     */
    public function testCreateCmykFailsParamsInvalid()
    {
        $color = Color::create('cmyk(50, 50)');
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    This CMYK's cyan is out of range
     */
    public function testCreateCmykFailsParamsC()
    {
        $color = Color::create('cmyk(150, 50, 50, 50)');
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    This CMYK's magenta is out of range
     */
    public function testCreateCmykFailsParamsM()
    {
        $color = Color::create('cmyk(50, 101, 50, 50)');
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    This CMYK's yellow is out of range
     */
    public function testCreateCmykFailsParamsY()
    {
        $color = Color::create('cmyk(50, 50, -50, 50)');
    }

    /**
     * @expectedException           ColorTools\Exception
     * @expectedExceptionMessage    This CMYK's black is invalid
     */
    public function testCreateCmykFailsParamsK()
    {
        $color = Color::create('cmyk(50, 50, 50, asd)');
    }

    public function testCreateFromCssName()
    {
        $color = Color::create('red');
        $this->assertEquals($color->hex, '#ff0000');

        $color = Color::create('Lime');
        $this->assertEquals($color->hex, '#00ff00');

        $color = Color::create('blue');
        $this->assertEquals($color->hex, '#0000ff');

        $color = Color::create('black');
        $this->assertEquals($color->hex, '#000000');
    }

    public function testCreateFromColorName()
    {
        $color = Color::create('Ball blue');
        $this->assertEquals($color->hex, '#21abcd');

        $color = Color::create('Lime');
        $this->assertEquals($color->hex, '#00ff00');

        $color = Color::create('blue');
        $this->assertEquals($color->hex, '#0000ff');

        $color = Color::create('black');
        $this->assertEquals($color->hex, '#000000');
    }
}