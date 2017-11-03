<?php

namespace DSMPackageSearch\Tests;
use \PHPUnit\Framework\TestCase;

use \DSMPackageSearch\Config;
use \DSMPackageSearch\Device\DeviceList;
use \DSMPackageSearch\Tests\TestTools;

class DeviceListTest extends TestCase
{
    private $goodConfig = '/config-files/goodConfig.yaml';
    private $notExistFilesConfig = '/config-files/nonexistConfig.yaml';
    private $badSourceConfig = '/config-files/goodConfigBadOthers.yaml';

    public function testParseYaml()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $deviceList = new DeviceList($config);
        $this->assertNotNull($deviceList);
    }
    
    /**
        * @expectedException \Exception
        * @expectedExceptionMessageRegExp /^DeviceList file .*nonexist.yaml not found!$/
    */
    public function testNotExists()
    {
        $config = new Config(__DIR__, $this->notExistFilesConfig);
        $deviceList = new DeviceList($config);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /^Unable to parse at line.*$/
     */
     public function testBadYaml()
     {
        $config = new Config(__DIR__, $this->badSourceConfig);
        $deviceList = new DeviceList($config);
     }

     public function testGetFamilyProper()
     {
        $config = new Config(__DIR__, $this->goodConfig);
        $deviceList = new DeviceList($config);
        $result = $deviceList->GetFamily("x86");
        $this->assertEquals("x86_64", $result);
     }

     public function testGetFamilyNotExists()
     {
        $config = new Config(__DIR__, $this->goodConfig);
        $deviceList = new DeviceList($config);
        $result = $deviceList->GetFamily("xxxx");
        $this->assertEquals("xxxx", $result);
     }

     public function testGetArchList()
     {
        $config = new Config(__DIR__, $this->goodConfig);
        $deviceList = new DeviceList($config);
        $result = $deviceList->GetArchList();
        $this->assertTrue(isset($result));
        $this->assertEquals(29, count($result));
     }

     public function testGetArchProper()
     {
        $config = new Config(__DIR__, $this->goodConfig);
        $deviceList = new DeviceList($config);
        $result = $deviceList->GetArch("DS215+");
        $this->assertEquals("alpine4k", $result);
        $result = $deviceList->GetArch("DS414");
        $this->assertEquals("armadaxp", $result);
     }

     public function testGetArchNotExists()
     {
        $config = new Config(__DIR__, $this->goodConfig);
        $deviceList = new DeviceList($config);
        $result = $deviceList->GetArch("xxxx");
        $this->assertEquals(null, $result);
     }

     public function testGetDevicesNotSelected()
     {
        $config = new Config(__DIR__, $this->goodConfig);
        $deviceList = new DeviceList($config);
        $result = $deviceList->GetDevices(null);
        $this->assertEquals(166, count($result));
        //non of it is selected:
        foreach ($result as $val)
        {
            $this->assertFalse($val['isSelected']);
        }
     }

     public function testGetDevicesOneSelected()
     {
        $config = new Config(__DIR__, $this->goodConfig);
        $deviceList = new DeviceList($config);
        $result = $deviceList->GetDevices("DS215j");
        $this->assertEquals(166, count($result));
        //one is selected
        $selectedCount = 0;
        foreach ($result as $val)
        {
            if ($val['isSelected'] == true)
            {
                $this->assertEquals("DS215j", $val['name']);
                $selectedCount++;
            }
        }
        $this->assertEquals(1, $selectedCount);
     }

     public function testGetDevicesCleanSelected()
     {
        $config = new Config(__DIR__, $this->goodConfig);
        $deviceList = new DeviceList($config);
        $result = $deviceList->GetDevices("DS215j");
        $this->assertEquals(166, count($result));
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
        TestTools::InvokeMethod($deviceList, 'CleanSelected');

        //check:
        $result = $deviceList->GetDevices(null);
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