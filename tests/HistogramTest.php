<?php

use ColorTools\Image;
use ColorTools\Histogram;

class HistogramTest extends PHPUnit\Framework\TestCase
{
    private $testImgPath = './samples/test-small.jpg';

    public static $analysis = null;
    public static $histogram = null;

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

    public function testCheckThatTheClassWorks()
    {
        HistogramTest::$analysis = Image::create($this->testImgPath)->getAnalysis();
        HistogramTest::$histogram = HistogramTest::$analysis->histogram;

        $this->assertInstanceOf('ColorTools\Histogram', HistogramTest::$histogram);
        $this->assertArrayHasKey('histogram', HistogramTest::$analysis->time);
    }

    public function testCreateFromAnalyzeObject()
    {
        $histogramFromAnalyzeObject = Histogram::create(HistogramTest::$analysis);
        $this->assertEquals(HistogramTest::$histogram->a, $histogramFromAnalyzeObject->a);
        $this->assertEquals(HistogramTest::$histogram->r, $histogramFromAnalyzeObject->r);
        $this->assertEquals(HistogramTest::$histogram->g, $histogramFromAnalyzeObject->g);
        $this->assertEquals(HistogramTest::$histogram->b, $histogramFromAnalyzeObject->b);
        $this->assertEquals(HistogramTest::$histogram->l, $histogramFromAnalyzeObject->l);
    }

    public function testCreateFromPixels()
    {
        $histogramFromPixelsArray = Histogram::create(HistogramTest::$analysis->sampledPixels);
        $this->assertEquals(HistogramTest::$histogram->a, $histogramFromPixelsArray->a);
        $this->assertEquals(HistogramTest::$histogram->r, $histogramFromPixelsArray->r);
        $this->assertEquals(HistogramTest::$histogram->g, $histogramFromPixelsArray->g);
        $this->assertEquals(HistogramTest::$histogram->b, $histogramFromPixelsArray->b);
        $this->assertEquals(HistogramTest::$histogram->l, $histogramFromPixelsArray->l);
    }

    public function testCreateFromImage()
    {
        $histogramFromImageObject= Histogram::create(Image::create($this->testImgPath));
        $this->assertEquals(HistogramTest::$histogram->a, $histogramFromImageObject->a);
        $this->assertEquals(HistogramTest::$histogram->r, $histogramFromImageObject->r);
        $this->assertEquals(HistogramTest::$histogram->g, $histogramFromImageObject->g);
        $this->assertEquals(HistogramTest::$histogram->b, $histogramFromImageObject->b);
        $this->assertEquals(HistogramTest::$histogram->l, $histogramFromImageObject->l);
    }

    public function testCreateFromImageParams()
    {
        // create from file path
        $histogramFromImageParam = Histogram::create($this->testImgPath);
        $this->assertEquals(HistogramTest::$histogram->a, $histogramFromImageParam->a);
        $this->assertEquals(HistogramTest::$histogram->r, $histogramFromImageParam->r);
        $this->assertEquals(HistogramTest::$histogram->g, $histogramFromImageParam->g);
        $this->assertEquals(HistogramTest::$histogram->b, $histogramFromImageParam->b);
        $this->assertEquals(HistogramTest::$histogram->l, $histogramFromImageParam->l);

        // create from file content
        $histogramFromImageParam = Histogram::create(file_get_contents($this->testImgPath));
        $this->assertEquals(HistogramTest::$histogram->a, $histogramFromImageParam->a);
        $this->assertEquals(HistogramTest::$histogram->r, $histogramFromImageParam->r);
        $this->assertEquals(HistogramTest::$histogram->g, $histogramFromImageParam->g);
        $this->assertEquals(HistogramTest::$histogram->b, $histogramFromImageParam->b);
        $this->assertEquals(HistogramTest::$histogram->l, $histogramFromImageParam->l);

        // create from gd resource
        $histogramFromImageParam = Histogram::create(imagecreatefromjpeg($this->testImgPath));
        $this->assertEquals(HistogramTest::$histogram->a, $histogramFromImageParam->a);
        $this->assertEquals(HistogramTest::$histogram->r, $histogramFromImageParam->r);
        $this->assertEquals(HistogramTest::$histogram->g, $histogramFromImageParam->g);
        $this->assertEquals(HistogramTest::$histogram->b, $histogramFromImageParam->b);
        $this->assertEquals(HistogramTest::$histogram->l, $histogramFromImageParam->l);

        // create from url
        if(!(getenv('ONLINE') or getenv('EXTENDED'))) {
            $this->markTestIncomplete('URL test skipped - run with ONLINE or EXTENDED environment variables');
        }

        $this->assertInstanceOf('ColorTools\Histogram', Histogram::create(getenv('TEST_URL')));
    }

    public function testHistogramContents()
    {
        $this->assertNotEmpty(HistogramTest::$histogram->a);
        $this->assertNotEmpty(HistogramTest::$histogram->r);
        $this->assertNotEmpty(HistogramTest::$histogram->g);
        $this->assertNotEmpty(HistogramTest::$histogram->b);
        $this->assertNotEmpty(HistogramTest::$histogram->l);
    }

    public function testArray()
    {
        $this->assertEquals(HistogramTest::$histogram->a, HistogramTest::$histogram->toArray()['a']);
        $this->assertEquals(HistogramTest::$histogram->r, HistogramTest::$histogram->toArray()['r']);
        $this->assertEquals(HistogramTest::$histogram->g, HistogramTest::$histogram->toArray()['g']);
        $this->assertEquals(HistogramTest::$histogram->b, HistogramTest::$histogram->toArray()['b']);
        $this->assertEquals(HistogramTest::$histogram->l, HistogramTest::$histogram->toArray()['l']);
    }

    public function testCreateFromArray()
    {
        $histogramFromArray = Histogram::create(HistogramTest::$histogram->toArray());
        $this->assertEquals(HistogramTest::$histogram->a, $histogramFromArray->a);
        $this->assertEquals(HistogramTest::$histogram->r, $histogramFromArray->r);
        $this->assertEquals(HistogramTest::$histogram->g, $histogramFromArray->g);
        $this->assertEquals(HistogramTest::$histogram->b, $histogramFromArray->b);
        $this->assertEquals(HistogramTest::$histogram->l, $histogramFromArray->l);
    }

    public function testSerialize()
    {
        $this->assertEquals(HistogramTest::$histogram->a, json_decode(HistogramTest::$histogram->serialize())->a);
        $this->assertEquals(HistogramTest::$histogram->r, json_decode(HistogramTest::$histogram->serialize())->r);
        $this->assertEquals(HistogramTest::$histogram->g, json_decode(HistogramTest::$histogram->serialize())->g);
        $this->assertEquals(HistogramTest::$histogram->b, json_decode(HistogramTest::$histogram->serialize())->b);
        $this->assertEquals(HistogramTest::$histogram->l, json_decode(HistogramTest::$histogram->serialize())->l);
    }

    public function testCreateFromSerialized()
    {
        $histogramFromSerialized = Histogram::create(HistogramTest::$histogram->serialize());
        $this->assertEquals(HistogramTest::$histogram->a, $histogramFromSerialized->a);
        $this->assertEquals(HistogramTest::$histogram->r, $histogramFromSerialized->r);
        $this->assertEquals(HistogramTest::$histogram->g, $histogramFromSerialized->g);
        $this->assertEquals(HistogramTest::$histogram->b, $histogramFromSerialized->b);
        $this->assertEquals(HistogramTest::$histogram->l, $histogramFromSerialized->l);
    }

    public function testBuildHistogram()
    {
        $this->assertStringStartsWith('<?xml', HistogramTest::$histogram->buildHistogram('a'));
        $this->assertStringStartsWith('<?xml', HistogramTest::$histogram->buildHistogram('r'));
        $this->assertStringStartsWith('<?xml', HistogramTest::$histogram->buildHistogram('g'));
        $this->assertStringStartsWith('<?xml', HistogramTest::$histogram->buildHistogram('b'));
        $this->assertStringStartsWith('<?xml', HistogramTest::$histogram->buildHistogram('l'));
        $this->assertStringStartsWith('<?xml', HistogramTest::$histogram->buildHistogram('c'));
    }

}