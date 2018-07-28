<?php

namespace DSMPackageSearch\Tests;
use \PHPUnit\Framework\TestCase;

use \DSMPackageSearch\Config;
use \DSMPackageSearch\Device\DSMVersionList;
use \DSMPackageSearch\Tests\TestTools;

class DSMVersionListTest extends TestCase
{
    private $goodConfig = '/config-files/goodConfig.yaml';
    private $notExistFilesConfig = '/config-files/nonexistConfig.yaml';
    private $badSourceConfig = '/config-files/goodConfigBadOthers.yaml';

    public function testParseYaml()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $versionList = new DSMVersionList($config);
        $this->assertNotNull($versionList);
    }
    
    /**
        * @expectedException \Exception
        * @expectedExceptionMessageRegExp /^Versions file .*nonexist.yaml not found!$/
    */
    public function testNotExists()
    {
        $config = new Config(__DIR__, $this->notExistFilesConfig);
        $versionList = new DSMVersionList($config);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /^Unable to parse at line.*$/
     */
     public function testBadYaml()
     {
        $config = new Config(__DIR__, $this->badSourceConfig);
        $versionList = new DSMVersionList($config);
     }

     public function testGetDSMVersionsNotSelected()
     {
        $config = new Config(__DIR__, $this->goodConfig);
        $versionList = new DSMVersionList($config);
        $result = $versionList->GetDSMVersions(null);
        $this->assertTrue(isset($result));
        $this->assertEquals(80, count($result));
        foreach ($result as $val)
        {
            $this->assertFalse($val['isSelected']);
        }
     }

     public function testGetDSMVersionsOneSelected()
     {
        $config = new Config(__DIR__, $this->goodConfig);
        $versionList = new DSMVersionList($config);
        $result = $versionList->GetDSMVersions("6.1.3-15152");
        $this->assertEquals(80, count($result));
        //one is selected
        $selectedCount = 0;
        foreach ($result as $val)
        {
            if ($val['isSelected'] == true)
            {
                $this->assertEquals("6.1.3-15152", $val['version']);
                $selectedCount++;
            }
        }
        $this->assertEquals(1, $selectedCount);
     }

     public function testGetDSMVersionsCleanSelected()
     {
        $config = new Config(__DIR__, $this->goodConfig);
        $versionList = new DSMVersionList($config);
        $result = $versionList->GetDSMVersions("6.1.3-15152");
        $this->assertEquals(80, count($result));
        //one is selected
        $selectedCount = 0;
        foreach ($result as $val)
        {
            if ($val['isSelected'] == true)
            {
                $selectedCount++;
            }
        }
        $this->assertEquals(1, $selectedCount);
        TestTools::InvokeMethod($versionList, 'CleanSelected');

        //check:
        $result = $versionList->GetDSMVersions(null);
        $selectedCount = 0;
        foreach ($result as $val)
        {
            if ($val['isSelected'] == true)
            {
                $selectedCount++;
            }
        }
        $this->assertEquals(0, $selectedCount);

     }

     public function testGetVersionDetailsProperValue1()
    {
        $major = null;
        $minor = null;
        $build = null;
        $config = new Config(__DIR__, $this->goodConfig);
        $versionList = new DSMVersionList($config);
        $result = $versionList->GetVersionDetails("6.1.3-15252", $major, $minor, $build);
        $this->assertTrue($result);
        $this->assertEquals(6, $major, "major");
        $this->assertEquals(1, $minor, "minor");
        $this->assertEquals(15252, $build, "build");
    }

    public function testGetVersionDetailsProperValue2()
    {
        $major = null;
        $minor = null;
        $build = null;
        $config = new Config(__DIR__, $this->goodConfig);
        $versionList = new DSMVersionList($config);
        $result = $versionList->GetVersionDetails("4.0-1300", $major, $minor, $build);
        $this->assertTrue($result);
        $this->assertEquals(4, $major, "major");
        $this->assertEquals(0, $minor, "minor");
        $this->assertEquals(1300, $build, "build");
    }

    public function testGetVersionDetailsBadValue()
    {
        $major = null;
        $minor = null;
        $build = null;
        $config = new Config(__DIR__, $this->goodConfig);
        $versionList = new DSMVersionList($config);
        $result = $versionList->GetVersionDetails("4.0x-1300", $major, $minor, $build);
        $this->assertFalse($result);
        $this->assertNull($major);
        $this->assertNull($minor);
        $this->assertNull($build);
    }

    public function testGetVersionByBuildGoodValue()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $versionList = new DSMVersionList($config);
        $result = $versionList->GetVersionByBuild("8754");
        $this->assertEquals("6.0.3-8754", $result);
    }

    public function testGetVersionByBuildBadValue()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $versionList = new DSMVersionList($config);
        $result = $versionList->GetVersionByBuild("1111");
        $this->assertNull($result);
    }
}