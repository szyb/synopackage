<?php

namespace DSMPackageSearch\Tests;

use \DSMPackageSearch\Config;
use \PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private $goodConfig ='/config-files/goodConfig.yaml';
    private $badConfig = '/config-files/badConfig.yaml';

    public function testYaml()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $this->assertTrue(isset($config));
    }

    /**
        * @expectedException \Exception
        * @expectedExceptionMessageRegExp /^Config file ".*nonexist.yaml" not found!$/
    */
    public function testNonExistYaml()
    {
        new Config(__DIR__, 'nonexist.yaml');
    }

    /**
     * @expectedException \Exception
     */
    public function testBadYaml()
    {
        new Config(__DIR__, $this->badConfig);
    }

    public function testSetUnset()
    {
        $cfg = new Config(__DIR__, $this->goodConfig);
        $cfg->thisIsATest = 123;
        $this->assertTrue(isset($cfg->thisIsATest));
        $this->assertEquals(123, $cfg->thisIsATest);
        unset($cfg->thisIsATest);
        $this->assertFalse(isset($cfg->thisIsATest));
    }

    public function testTraversable()
    {
        $cfg = new Config(__DIR__, $this->goodConfig);
        $cfg->thisIsATest = 123;
        $this->assertContains(123, $cfg);
        foreach ($cfg as $key => $value) {
            $this->assertNotEmpty($key);
        }
    }

}