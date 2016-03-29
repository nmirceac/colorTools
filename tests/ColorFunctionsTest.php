<?php

use ColorTools\Color;

class ColorFunctionsTest extends PHPUnit_Framework_TestCase
{
    public function testSetRGBValues() {
        $random = Color::create(rand(0, 0xffffff));

        $random->setRed(15);
        $this->assertEquals($random->getRed(), 15);
        $random->red = 25;
        $this->assertEquals($random->red, 25);
        $random->r = 35;
        $this->assertEquals($random->r, 35);

        $random->setGreen(115);
        $this->assertEquals($random->getGreen(), 115);
        $random->green = 125;
        $this->assertEquals($random->green, 125);
        $random->g = 135;
        $this->assertEquals($random->g, 135);

        $random->setBlue(215);
        $this->assertEquals($random->getBlue(), 215);
        $random->blue = 225;
        $this->assertEquals($random->blue, 225);
        $random->b = 235;
        $this->assertEquals($random->b, 235);

        $this->assertEquals($random->getRgb(), ['red'=>35, 'green'=>135, 'blue'=>235]);
    }

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

        $this->assertEquals(Color::create('#d003c2')->complement()->hex, '#03ce11');
    }

    public function testTriad()
    {
        // first color in the triad - no param - or 0, or '' or false (whatever evals to false)
        $this->assertEquals(Color::create('#d003c2')->triad(1)->hex, '#c0ce03');
        // second color in the triad - 'second' or true or 2 - anything that evals to true
        $this->assertEquals(Color::create('#d003c2')->triad(2)->hex, '#03c0ce');
    }

    public function testTetrad()
    {
        // first color in the tetrad - no param - or 1
        $this->assertEquals(Color::create('#d003c2')->tetrad(1)->hex, '#ce7603');
        // second color in the tetrad - 2
        $this->assertEquals(Color::create('#d003c2')->tetrad(2)->hex, '#03ce11');
        // second color in the tetrad - 2
        $this->assertEquals(Color::create('#d003c2')->tetrad(3)->hex, '#035bce');
    }

    public function testMixing()
    {
        // mixing white with red
        $this->assertEquals(Color::create('white')->mix('red', 50)->name, 'salmon');
        // even works with decimals (unless the weight is 1)
        $this->assertEquals(Color::create('white')->mix('red', 0.5)->name, 'salmon');
        // with red weight of 100
        $this->assertEquals(Color::create('white')->mix('red', 100)->name, 'red');
        // with red weight of 100

        // works with other color objects also
        $black = Color::create('black');
        $this->assertEquals(Color::create('white')->mix($black, 50)->hex, '#808080');
    }

    public function testTinting()
    {
        // tinting red 100%
        $this->assertEquals(Color::create('red')->tint(100)->name, 'white');
        // tinting red 10% - the default value
        $this->assertEquals(Color::create('red')->tint()->hex, '#ff1a1a');
        // tinting red 50% with decimals
        $this->assertEquals(Color::create('red')->tint(0.5)->hex, '#ff8080');
    }

    public function testShading()
    {
        // shading blue 100%
        $this->assertEquals(Color::create('blue')->shade(100)->name, 'black');
        // shading blue 10% - the default value
        $this->assertEquals(Color::create('blue')->shade()->hex, '#0000e6');
        // shading blue 50% with decimals
        $this->assertEquals(Color::create('blue')->shade(0.5)->hex, '#000080');
    }

    public function testDoGrayscale()
    {
        // grayscaling blue
        $this->assertEquals(Color::create('blue')->grayscale()->name, 'gray');
        // grayscaling random color - they RGB channels are identical
        $grayscaleRgb = Color::create(rand(0, 0xffffff))->grayscale()->getRgb();
        $this->assertEquals(max($grayscaleRgb), min($grayscaleRgb));
    }

    public function testSpin()
    {
        // creating red => 0
        $color = Color::create('red');
        $this->assertEquals($color->name, 'red');
        // spinning +120 => 120
        $color -> spin(120);
        $this->assertEquals($color->name, 'lime');
        // spinning -240 => 240
        $color -> spin(-240);
        $this->assertEquals($color->name, 'blue');
        // spinning +120 => 360 = 0
        $color -> spin(120);
        $this->assertEquals($color->name, 'red');
        // spinning +420 => 60
        $color -> spin(420);
        $this->assertEquals($color->name, 'yellow');
        // spinning +120 => 180
        $color -> spin(120);
        $this->assertEquals($color->name, 'aqua');
        // spinning +120 => 300
        $color -> spin(120);
        $this->assertEquals($color->name, 'fuchsia');
        // checking HSL
        $this->assertEquals($color->getHsl()['hue'], '300');
        // spinning +60 => 0
        $color -> spin(60);
        // random spin check
        $randomSpin = rand(10, 300);
        $color -> spin($randomSpin);
        $this->assertEquals($color->getHsl()['hue'], $randomSpin);
    }

    public function testSaturation()
    {
        // saturating random color with 10
        $randomColor = Color::create()->setHsl(rand(0, 360), rand(30, 60), rand(30, 60));
        $saturation = $randomColor->getHsl()['saturation'];
        $randomColor->saturate(10);
        $this->assertGreaterThanOrEqual($randomColor->getHsl()['saturation']-($saturation+0.1), 0.1);

        // saturating random color with 20
        $randomColor = Color::create()->setHsl(rand(0, 360), rand(30, 60), rand(30, 60));
        $saturation = $randomColor->getHsl()['saturation'];
        $randomColor->saturate(0.2);
        $this->assertGreaterThanOrEqual($randomColor->getHsl()['saturation']-($saturation+0.2), 0.1);

        // desaturating random color with 20
        $randomColor = Color::create()->setHsl(rand(0, 360), rand(30, 60), rand(30, 60));
        $saturation = $randomColor->getHsl()['saturation'];
        $randomColor->saturate(-0.2);
        $this->assertGreaterThanOrEqual($randomColor->getHsl()['saturation']-($saturation-0.2), 0.1);

        // desaturating random color with 10 using the desaturate method
        $randomColor = Color::create()->setHsl(rand(0, 360), rand(30, 60), rand(30, 60));
        $saturation = $randomColor->getHsl()['saturation'];
        $randomColor->desaturate(10);
        $this->assertGreaterThanOrEqual($randomColor->getHsl()['saturation']-($saturation-0.1), 0.1);
    }

    public function testLighten()
    {
        // lighten random color with 10
        $randomColor = Color::create()->setHsl(rand(0, 360), rand(30, 60), rand(30, 60));
        $lightness = $randomColor->getHsl()['lightness'];
        $randomColor->lighten(10);
        $this->assertGreaterThanOrEqual($randomColor->getHsl()['lightness']-($lightness+0.1), 0.1);

        // lighten random color with 20
        $randomColor = Color::create()->setHsl(rand(0, 360), rand(30, 60), rand(30, 60));
        $lightness = $randomColor->getHsl()['lightness'];
        $randomColor->lighten(0.2);
        $this->assertGreaterThanOrEqual($randomColor->getHsl()['lightness']-($lightness+0.2), 0.1);

        // darken random color with 20
        $randomColor = Color::create()->setHsl(rand(0, 360), rand(30, 60), rand(30, 60));
        $lightness = $randomColor->getHsl()['lightness'];
        $randomColor->lighten(-0.2);
        $this->assertGreaterThanOrEqual($randomColor->getHsl()['lightness']-($lightness-0.2), 0.1);

        // darken random color with 10 using the desaturate method
        $randomColor = Color::create()->setHsl(rand(0, 360), rand(30, 60), rand(30, 60));
        $lightness = $randomColor->getHsl()['lightness'];
        $randomColor->darken(10);
        $this->assertGreaterThanOrEqual($randomColor->getHsl()['lightness']-($lightness-0.1), 0.1);
    }

    public function testMultiply()
    {
        // less tests - http://lesscss.org/functions/#color-blending-multiply
        $this->assertEquals(Color::create('#ff6600')->multiply('#000000')->hex, '#000000');
        $this->assertEquals(Color::create('#ff6600')->multiply('#333333')->hex, '#331400');
        $this->assertEquals(Color::create('#ff6600')->multiply('#666666')->hex, '#662900');
        $this->assertEquals(Color::create('#ff6600')->multiply('#999999')->hex, '#993d00');
        $this->assertEquals(Color::create('#ff6600')->multiply('#cccccc')->hex, '#cc5200');
        $this->assertEquals(Color::create('#ff6600')->multiply('#ffffff')->hex, '#ff6600');
        $this->assertEquals(Color::create('#ff6600')->multiply('#ff0000')->hex, '#ff0000');
        $this->assertEquals(Color::create('#ff6600')->multiply('#00ff00')->hex, '#006600');
        $this->assertEquals(Color::create('#ff6600')->multiply('#0000ff')->hex, '#000000');
    }

    public function testScreen()
    {
        // less tests - http://lesscss.org/functions/#color-blending-screen
        $this->assertEquals(Color::create('#ff6600')->screen('#000000')->hex, '#ff6600');
        $this->assertEquals(Color::create('#ff6600')->screen('#333333')->hex, '#ff8533');
        $this->assertEquals(Color::create('#ff6600')->screen('#666666')->hex, '#ffa366');
        $this->assertEquals(Color::create('#ff6600')->screen('#999999')->hex, '#ffc299');
        $this->assertEquals(Color::create('#ff6600')->screen('#cccccc')->hex, '#ffe0cc');
        $this->assertEquals(Color::create('#ff6600')->screen('#ffffff')->hex, '#ffffff');
        $this->assertEquals(Color::create('#ff6600')->screen('#ff0000')->hex, '#ff6600');
        $this->assertEquals(Color::create('#ff6600')->screen('#00ff00')->hex, '#ffff00');
        $this->assertEquals(Color::create('#ff6600')->screen('#0000ff')->hex, '#ff66ff');
    }

    public function testOverlay()
    {
        // less tests - http://lesscss.org/functions/#color-blending-overlay
        $this->assertEquals(Color::create('#ff6600')->overlay('#000000')->hex, '#ff0000');
        $this->assertEquals(Color::create('#ff6600')->overlay('#333333')->hex, '#ff2900');
        $this->assertEquals(Color::create('#ff6600')->overlay('#666666')->hex, '#ff5200');
        $this->assertEquals(Color::create('#ff6600')->overlay('#999999')->hex, '#ff7a00');
        $this->assertEquals(Color::create('#ff6600')->overlay('#cccccc')->hex, '#ffa300');
        $this->assertEquals(Color::create('#ff6600')->overlay('#ffffff')->hex, '#ffcc00');
        $this->assertEquals(Color::create('#ff6600')->overlay('#ff0000')->hex, '#ff0000');
        $this->assertEquals(Color::create('#ff6600')->overlay('#00ff00')->hex, '#ffcc00');
        $this->assertEquals(Color::create('#ff6600')->overlay('#0000ff')->hex, '#ff0000');
    }

    public function testSoftLight()
    {
        // less tests - http://lesscss.org/functions/#color-blending-softlight
        $this->assertEquals(Color::create('#ff6600')->softlight('#000000')->hex, '#ff2900');
        $this->assertEquals(Color::create('#ff6600')->softlight('#333333')->hex, '#ff4100');
        $this->assertEquals(Color::create('#ff6600')->softlight('#666666')->hex, '#ff5a00');
        $this->assertEquals(Color::create('#ff6600')->softlight('#999999')->hex, '#ff7200');
        $this->assertEquals(Color::create('#ff6600')->softlight('#cccccc')->hex, '#ff8a00');
        $this->assertEquals(Color::create('#ff6600')->softlight('#ffffff')->hex, '#ffa100');
        $this->assertEquals(Color::create('#ff6600')->softlight('#ff0000')->hex, '#ff2900');
        $this->assertEquals(Color::create('#ff6600')->softlight('#00ff00')->hex, '#ffa100');
        $this->assertEquals(Color::create('#ff6600')->softlight('#0000ff')->hex, '#ff2900');
    }

    public function testHardLight()
    {
        // less tests - http://lesscss.org/functions/#color-blending-hardlight
        $this->assertEquals(Color::create('#ff6600')->hardlight('#000000')->hex, '#000000');
        $this->assertEquals(Color::create('#ff6600')->hardlight('#333333')->hex, '#662900');
        $this->assertEquals(Color::create('#ff6600')->hardlight('#666666')->hex, '#cc5200');
        $this->assertEquals(Color::create('#ff6600')->hardlight('#999999')->hex, '#ff8533');
        /*
         * Yes - lesscss.org thinks that #ff6600 -> hardlight -> #cccccc => ff2900
         * but that is probably a typo...
         * Try it yourself:
         * .bg-blend {
         *    background-image: url(http://www.colorhexa.com/cccccc.png);
         *    background-color: #ff6600;
         *    background-blend-mode: hard-light;
         *  }
         *
         * Opened an issue - probably it will be sorted soon
         * https://github.com/less/less-docs/issues/401
         *
         */
        $this->assertEquals(Color::create('#ff6600')->hardlight('#cccccc')->hex, '#ffc299');
        $this->assertEquals(Color::create('#ff6600')->hardlight('#ffffff')->hex, '#ffffff');
        $this->assertEquals(Color::create('#ff6600')->hardlight('#ff0000')->hex, '#ff0000');
        $this->assertEquals(Color::create('#ff6600')->hardlight('#00ff00')->hex, '#00ff00');
        $this->assertEquals(Color::create('#ff6600')->hardlight('#0000ff')->hex, '#0000ff');

        //this is also the opposite of overlay
        $randomColor1 = Color::create(rand(0, 0xffffff));
        $randomColor2 = Color::create(rand(0, 0xffffff));
        $this->assertEquals(Color::create($randomColor1)->overlay($randomColor2)->hex,
                            Color::create($randomColor2)->hardlight($randomColor1)->hex);
    }

    public function testDifference()
    {
        // less tests - http://lesscss.org/functions/#color-blending-difference
        $this->assertEquals(Color::create('#ff6600')->difference('#000000')->hex, '#ff6600');
        $this->assertEquals(Color::create('#ff6600')->difference('#333333')->hex, '#cc3333');
        $this->assertEquals(Color::create('#ff6600')->difference('#666666')->hex, '#990066');
        $this->assertEquals(Color::create('#ff6600')->difference('#999999')->hex, '#663399');
        $this->assertEquals(Color::create('#ff6600')->difference('#cccccc')->hex, '#3366cc');
        $this->assertEquals(Color::create('#ff6600')->difference('#ffffff')->hex, '#0099ff');
        $this->assertEquals(Color::create('#ff6600')->difference('#ff0000')->hex, '#006600');
        $this->assertEquals(Color::create('#ff6600')->difference('#00ff00')->hex, '#ff9900');
        $this->assertEquals(Color::create('#ff6600')->difference('#0000ff')->hex, '#ff66ff');
    }

    public function testExclusion()
    {
        // less tests - http://lesscss.org/functions/#color-blending-exclusion
        $this->assertEquals(Color::create('#ff6600')->exclusion('#000000')->hex, '#ff6600');
        $this->assertEquals(Color::create('#ff6600')->exclusion('#333333')->hex, '#cc7033');
        $this->assertEquals(Color::create('#ff6600')->exclusion('#666666')->hex, '#997a66');
        $this->assertEquals(Color::create('#ff6600')->exclusion('#999999')->hex, '#668599');
        $this->assertEquals(Color::create('#ff6600')->exclusion('#cccccc')->hex, '#338fcc');
        $this->assertEquals(Color::create('#ff6600')->exclusion('#ffffff')->hex, '#0099ff');
        $this->assertEquals(Color::create('#ff6600')->exclusion('#ff0000')->hex, '#006600');
        $this->assertEquals(Color::create('#ff6600')->exclusion('#00ff00')->hex, '#ff9900');
        $this->assertEquals(Color::create('#ff6600')->exclusion('#0000ff')->hex, '#ff66ff');
    }

    public function testAverage()
    {
        // less tests - http://lesscss.org/functions/#color-blending-average
        $this->assertEquals(Color::create('#ff6600')->average('#000000')->hex, '#803300');
        $this->assertEquals(Color::create('#ff6600')->average('#333333')->hex, '#994d1a');
        $this->assertEquals(Color::create('#ff6600')->average('#666666')->hex, '#b36633');
        $this->assertEquals(Color::create('#ff6600')->average('#999999')->hex, '#cc804d');
        $this->assertEquals(Color::create('#ff6600')->average('#cccccc')->hex, '#e69966');
        $this->assertEquals(Color::create('#ff6600')->average('#ffffff')->hex, '#ffb380');
        $this->assertEquals(Color::create('#ff6600')->average('#ff0000')->hex, '#ff3300');
        $this->assertEquals(Color::create('#ff6600')->average('#00ff00')->hex, '#80b300');
        $this->assertEquals(Color::create('#ff6600')->average('#0000ff')->hex, '#803380');
    }

    public function testNegation()
    {
        // less tests - http://lesscss.org/functions/#color-blending-negation
        $this->assertEquals(Color::create('#ff6600')->negate('#000000')->hex, '#ff6600');
        $this->assertEquals(Color::create('#ff6600')->negate('#333333')->hex, '#cc9933');
        $this->assertEquals(Color::create('#ff6600')->negate('#666666')->hex, '#99cc66');
        $this->assertEquals(Color::create('#ff6600')->negate('#999999')->hex, '#66ff99');
        $this->assertEquals(Color::create('#ff6600')->negate('#cccccc')->hex, '#33cccc');
        $this->assertEquals(Color::create('#ff6600')->negate('#ffffff')->hex, '#0099ff');
        $this->assertEquals(Color::create('#ff6600')->negate('#ff0000')->hex, '#006600');
        $this->assertEquals(Color::create('#ff6600')->negate('#00ff00')->hex, '#ff9900');
        $this->assertEquals(Color::create('#ff6600')->negate('#0000ff')->hex, '#ff66ff');
    }

    public function testContrast()
    {
        $this->assertEquals(Color::create('white')->findConstrast()->name, 'black');
        $this->assertEquals(Color::create('black')->findConstrast()->name, 'white');
        $this->assertEquals(Color::create('brown')->findConstrast()->name, 'white');
        $this->assertEquals(Color::create('pink')->findConstrast()->name, 'black');
    }

}