<?php

use ColorTools\Image;
use ColorTools\Palette;


class AnalyzeTest extends PHPUnit\Framework\TestCase
{
    public static $image = null;
    public static $analysis = null;
    private $testImgPath = './samples/test-medium.jpg';

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

    public function testAnalyze()
    {
        if(!file_exists($this->testImgPath)) {
            $this->markTestSkipped('Samples are missing - run php getSamples.php');
        }

        AnalyzeTest::$image = Image::create($this->testImgPath);
        AnalyzeTest::$analysis = AnalyzeTest::$image->getAnalysis();

        $this->assertEquals(AnalyzeTest::$analysis->precision, 2);
        $this->assertEquals(round(AnalyzeTest::$analysis->luma, 2), 0.41);
    }

    public function testSampledPixels()
    {
        $this->assertInstanceOf('ColorTools\Color', AnalyzeTest::$analysis->sampledPixels[0]);
    }
    public function testSimilarColorPixels()
    {
        $this->assertInstanceOf('ColorTools\Color', AnalyzeTest::$analysis->similarColorPixels[0]);
    }

    public function testSimilarColorImageObject()
    {
        $this->assertInstanceOf('ColorTools\Image',
            AnalyzeTest::$analysis->getSimilarColorImage());
    }

    public function testSampledPixelsImageObject()
    {
        $this->assertInstanceOf('ColorTools\Image',
            AnalyzeTest::$analysis->getSampledPixelsImage());
    }

    public function testAnalysisTime()
    {
        $this->assertArrayHasKey('pixelSampling', AnalyzeTest::$analysis->time);
        $this->assertArrayHasKey('gettingLuma', AnalyzeTest::$analysis->time);
        $this->assertArrayHasKey('gettingColors', AnalyzeTest::$analysis->time);
    }

    public function testCustomPrecision()
    {
        AnalyzeTest::$analysis = AnalyzeTest::$image->getAnalysis([
            'palette' => Palette::PALETTE_COLOR_TOOLS,
            'precision' => 10
        ]);
        $this->assertEquals(AnalyzeTest::$analysis->precision, 10);
    }

    public function testColorInPaletteColorTools()
    {
        $palette = new Palette(Palette::PALETTE_COLOR_TOOLS);
        $randomPixel = rand(0, AnalyzeTest::$analysis->sampledPixelsCount - 1);
        $this->assertContains(AnalyzeTest::$analysis->similarColorPixels[$randomPixel]->int, $palette->collection);
        AnalyzeTest::$analysis = null;
    }

    public function testColorInPaletteBrianMcDo()
    {
        $palette = Palette::PALETTE_BRIAN_MCDO;
        $analysis = AnalyzeTest::$image->getAnalysis(['palette' => $palette, 'precision' => 50]);
        $randomPixel = rand(0, $analysis->sampledPixelsCount - 1);

        $this->assertContains($analysis->similarColorPixels[$randomPixel]->int, Palette::create($palette)->collection);
    }

    public function testColorInPaletteRgb3()
    {
        $palette = Palette::PALETTE_RGB3;
        $analysis = AnalyzeTest::$image->getAnalysis(['palette' => $palette, 'precision' => 50]);
        $randomPixel = rand(0, $analysis->sampledPixelsCount - 1);

        $this->assertContains($analysis->similarColorPixels[$randomPixel]->int, Palette::create($palette)->collection);
    }

    public function testColorInPaletteNes()
    {
        $palette = Palette::PALETTE_NES;
        $analysis = AnalyzeTest::$image->getAnalysis(['palette' => $palette, 'precision' => 50]);
        $randomPixel = rand(0, $analysis->sampledPixelsCount - 1);

        $this->assertContains($analysis->similarColorPixels[$randomPixel]->int, Palette::create($palette)->collection);
    }

    public function testColorInPaletteApple()
    {
        $palette = Palette::PALETTE_APPLE;
        $analysis = AnalyzeTest::$image->getAnalysis(['palette' => $palette, 'precision' => 50]);
        $randomPixel = rand(0, $analysis->sampledPixelsCount - 1);

        $this->assertContains($analysis->similarColorPixels[$randomPixel]->int, Palette::create($palette)->collection);
    }
}