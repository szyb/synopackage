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
}