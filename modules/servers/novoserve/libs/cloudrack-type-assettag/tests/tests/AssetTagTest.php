<?php

use NovoServe\Cloudrack\Types\AssetTag;
use NovoServe\Cloudrack\Types\InvalidAssetTagException;
use NovoServe\Cloudrack\Types\InvalidAssetTagLocationException;
use PHPUnit\Framework\TestCase;

class AssetTagTest extends TestCase
{
    public function testNormalTag()
    {
        $assetTag = new AssetTag('123-123');
        $this->assertEquals('123-123', $assetTag->__toString());
    }

    public function testCountryTag()
    {
        $assetTag = new AssetTag('NL-123-123');
        $this->assertEquals('NL-123-123', $assetTag->__toString());
    }

    public function testDevTag()
    {
        $assetTag = new AssetTag('DEV-123-123');
        $this->assertEquals('DEV-123-123', $assetTag->__toString());
    }

    public function testInvalidTag()
    {
        $this->expectException(InvalidAssetTagException::class);
        new AssetTag('0123-123');
    }

    public function testUnknownCountryTag()
    {
        $this->expectException(InvalidAssetTagLocationException::class);
        new AssetTag('UA-123-123');
    }

    public function testBoolean()
    {
        $this->expectException(InvalidAssetTagException::class);
        new AssetTag(true);
    }

    public function testInteger()
    {
        $this->expectException(InvalidAssetTagException::class);
        new AssetTag(666);
    }

    public function testString()
    {
        $this->expectException(InvalidAssetTagException::class);
        new AssetTag('novoserve');
    }
}
