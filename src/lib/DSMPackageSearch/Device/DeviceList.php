<?php

namespace DSMPackageSearch\Device;

use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Yaml\Exception\ParseException;
use \DSMPackageSearch\CookieManager;

class DeviceList
{
    private $config;
    private $yamlFilepath;
    private $devices = array();

    /**
     * @param \SSpkS\Config $config Config object
     * @throws \Exception if file is not found or parsing error.
     */
    public function __construct(\DSMPackageSearch\Config $config)
    {
        $this->config = $config;
        $this->yamlFilepath = $this->config->paths['models'];
        if (!file_exists($this->yamlFilepath)) {
            throw new \Exception('DeviceList file ' . $this->yamlFilepath . ' not found!');
        }
        try {
            $this->parseYaml();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Parse Yaml file with device data.
     *
     * @throws \Exception if Yaml couldn't be parsed.
     */
    private function parseYaml()
    {
        try {
            /** @var array $archlist */
            $archlist = Yaml::parse(file_get_contents($this->yamlFilepath));
        } catch (ParseException $e) {
            throw new \Exception($e->getMessage());
        }
        $idx = 0;
        $sortkey = array();

        foreach ($archlist as $family => $archlist) {
            foreach ($archlist as $arch => $archmodels) {
                foreach ($archmodels as $model) {
                    $this->devices[$idx] = array(
                        'arch'          => $arch,
                        'name'          => $model,
                        'family'        => $family,
                        'isSelected'    => false
                    );
                    $sortkey[$idx] = $model;
                    $idx++;
                }
            }
        }
        array_multisort($sortkey, SORT_NATURAL | SORT_FLAG_CASE, $this->devices);
    }

    /**
     * Returns the architecture family for given $arch
     *
     * @param string $arch Architecture
     * @return string Family or $arch if not found
     */
    public function GetFamily($arch)
    {
        foreach ($this->devices as $d) {
            if ($d['arch'] == $arch) {
                return $d['family'];
            }
        }
        return $arch;
    }

    public function GetArchList()
    {
        $archList = array();
        $idx = 0;
        foreach ($this->devices as $d)
        {
            if (in_array($d['arch'], $archList ) == false)
            {
                $archList[$idx] = $d['arch'];
                $idx++;
            }
        }
        asort($archList);
        return $archList;
    }

    public function GetArch($model)
    {
        foreach ($this->devices as $d)
        {
            if ($d['name'] == $model)
                return $d['arch'];
        }
        return null;
    }

    /**
     * Returns the list of devices and their architectures.
     *
     * @return array List of devices and architectures.
     */
    public function GetDevices($selectedModel)
    {
        if ($selectedModel == null)
            return $this->devices;
        else
        {
            $this->CleanSelected();
            foreach ($this->devices as $key=>$device)
            {
                if ($device['name'] == $selectedModel)
                {
                    $this->devices[$key]['isSelected'] = true;
                    break;
                }
            }
            return $this->devices;
        }
    }

    private function CleanSelected()
    {
        foreach($this->devices as $key => $device)
        {
            $this->devices[$key]['isSelected'] = false;
        }
    }
}
