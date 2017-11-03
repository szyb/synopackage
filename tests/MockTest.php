<?php

namespace DSMPackageSearch\Tests;
use \DSMPackageSearch\Config;
use \PHPUnit\Framework\TestCase;

// load Monolog library
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;

class MockTest extends TestCase
{
    private $goodConfig ='/config-files/goodConfig.yaml';

    //this test does not test anything, just a sample how to use mock
    public function testMock()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $mock = $this->getMockBuilder("DSMPackageSearch\\DownloadManager")
            ->setConstructorArgs(array($config, $log))
            ->getMock();
        $mock->expects($this->once())
            ->method("DownloadContent")
            ->will($this->returnValue("content"));
        $errorMessage = null;
        $result = $mock->DownloadContent(null, $errorMessage);
        $this->assertNull($errorMessage);
        $this->assertEquals("content", $result);
    }

}