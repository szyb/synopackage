<?php

namespace DSMPackageSearch\Tests;

use \DSMPackageSearch\Config;
use \DSMPackageSearch\Changelog\ChangelogHelper;
use \DSMPackageSearch\Changelog\Changelog;
use \PHPUnit\Framework\TestCase;

class ChangelogTest extends TestCase
{
    private $goodConfig ='/config-files/goodConfig.yaml';
    private $notExistFilesConfig = '/config-files/nonexistConfig.yaml';
    private $badSourceConfig = '/config-files/goodConfigBadOthers.yaml';

    public function testYaml()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $changelogHelper = new ChangelogHelper($config);
        $this->assertNotNull($changelogHelper);
    }

     /**
        * @expectedException \Exception
        * @expectedExceptionMessageRegExp /^Changelog file .*nonexist.yaml not found!$/
    */
    public function testNotExists()
    {
        $config = new Config(__DIR__, $this->notExistFilesConfig);
        $changelogHelper = new ChangelogHelper($config);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /^Unable to parse at line.*$/
     */
     public function testBadYaml()
     {
        $config = new Config(__DIR__, $this->badSourceConfig);
        $changelogHelper = new ChangelogHelper($config);
     }

    public function testCompareVersionEqualCompare()
    {
        $changelogA = new Changelog();
        $changelogA->version = "0.8.0";

        $result = ChangelogHelper::CompareVersion($changelogA, $changelogA);
        $this->assertEquals(0, $result);
    }

    public function testCompareVersionReleaseCompare()
    {
        $changelogA = new Changelog();
        $changelogA->version = "0.8.0";

        $changelogB = new Changelog();
        $changelogB->version = "0.8.1";

        $result = ChangelogHelper::CompareVersion($changelogA, $changelogB);
        $this->assertEquals(1, $result);
        $result = ChangelogHelper::CompareVersion($changelogB, $changelogA);
        $this->assertEquals(-1, $result);
    }

    public function testCompareVersionMinorCompare()
    {
        $changelogA = new Changelog();
        $changelogA->version = "0.9.0";

        $changelogB = new Changelog();
        $changelogB->version = "0.8.1";

        $result = ChangelogHelper::CompareVersion($changelogA, $changelogB);
        $this->assertEquals(-1, $result);
        $result = ChangelogHelper::CompareVersion($changelogB, $changelogA);
        $this->assertEquals(1, $result);
    }

    public function testCompareVersionMajorCompare()
    {
        $changelogA = new Changelog();
        $changelogA->version = "1.0.0";

        $changelogB = new Changelog();
        $changelogB->version = "0.8.1";

        $result = ChangelogHelper::CompareVersion($changelogA, $changelogB);
        $this->assertEquals(-1, $result);
        $result = ChangelogHelper::CompareVersion($changelogB, $changelogA);
        $this->assertEquals(1, $result);
    }

    public function testCompareVersionInEqualVersionsCompare()
    {
        $changelogA = new Changelog();
        $changelogA->version = "1.0";

        $changelogB = new Changelog();
        $changelogB->version = "0.8.1";

        $result = ChangelogHelper::CompareVersion($changelogA, $changelogB);
        $this->assertEquals(-1, $result);
        $result = ChangelogHelper::CompareVersion($changelogB, $changelogA);
        $this->assertEquals(1, $result);
    }

    public function testCompareVersionEqualVersionsDifferentLength()
    {
        $changelogA = new Changelog();
        $changelogA->version = "1.0";

        $changelogB = new Changelog();
        $changelogB->version = "1.0.0";

        $result = ChangelogHelper::CompareVersion($changelogA, $changelogB);
        $this->assertEquals(0, $result);
    }

    public function testCompareVersionInEqualVersionsDifferentLength()
    {
        $changelogA = new Changelog();
        $changelogA->version = "1.0";

        $changelogB = new Changelog();
        $changelogB->version = "1.0.1";

        $result = ChangelogHelper::CompareVersion($changelogA, $changelogB);
        $this->assertEquals(1, $result);
        $result = ChangelogHelper::CompareVersion($changelogB, $changelogA);
        $this->assertEquals(-1, $result);
    }

    public function testCheckVersionMismatchBadValue()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $changelogHelper = new ChangelogHelper($config);
        $result = $changelogHelper->CheckVersionMismatch();
        $this->assertTrue($result);
    }

    public function testCheckVersionMismatchBProperValue()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $changelogHelper = new ChangelogHelper($config);
        $config->site = array('websiteVersion' => "0.8.5");
        $result = $changelogHelper->CheckVersionMismatch();
        $this->assertFalse($result);
    }

   
}