<?php
namespace DSMPackageSearch\Device;
use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Yaml\Exception\ParseException;

class DSMVersionList
{
    private $config;
    private $yamlFilepath;
    private $DSMversionList = array();

    /**
     * @param \SSpkS\Config $config Config object
     * @throws \Exception if file is not found or parsing error.
     */
    public function __construct(\DSMPackageSearch\Config $config)
    {
        $this->config = $config;
        $this->yamlFilepath = $this->config->paths['versions'];
        if (!file_exists($this->yamlFilepath)) {
            throw new \Exception('Versions file ' . $this->yamlFilepath . ' not found!');
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
            $versionList = Yaml::parse(file_get_contents($this->yamlFilepath));
        } catch (ParseException $e) {
            throw new \Exception($e->getMessage());
        }
        $idx = 0;
        $sortkey = array();
        $this->DSMversionList = array();
        //TODO: user preference or latest version here:
        $latestVersion = $this->config->site['latestVersion'];
        foreach ($versionList as $key => $value)
        {
            if ($key == "DSMVersions")
            {
                $idx = 0;
                foreach ($value as $version)
                {
                    $this->DSMversionList[$idx] =  array(                    
                        'version' => $version,
                        'isSelected' => false
                    );
                    $sortkey[$idx] = $version;
                    
                    $idx++;
                }
                break;
            }
        }
        array_multisort($sortkey, SORT_DESC , $this->DSMversionList);
    }

    
    public function GetDSMVersions($selectedVersion)
    {
        if ($selectedVersion == null)
            return $this->DSMversionList;
        else
        {
            $this->CleanSelected();
            foreach ($this->DSMversionList as $key=>$version)
            {
                if ($version['version'] == $selectedVersion)
                {
                    $this->DSMversionList[$key]['isSelected'] = true;
                    break;
                }
            }
            return $this->DSMversionList;
        }
    }

    private function CleanSelected()
    {
        foreach ($this->DSMversionList as $key=>$version)
        {
            $this->DSMversionList[$key]['isSelected'] = false;
        }
    }

    public function GetVersionDetails($version, &$major, &$minor, &$build)
    {
        $pattern = '/^(?<major>\d)\.(?<minor>\d)(\.\d){0,1}\-(?<build>(\d){1,5})$/';
        if (preg_match($pattern, $version, $matches) == 1)
        {
            $major = $matches['major'];
            $minor = $matches['minor'];
            $build = $matches['build'];
            return true;            
        }
        return false;    
    }

    public function GetVersionByBuild($build)
    {
        foreach ($this->DSMversionList as $key=>$version)
        {
            $b = null;
            $major = null;
            $minor = null;
            $this->GetVersionDetails($version["version"], $major, $minor, $b);
            if ($b == $build)
                return $version["version"];
        }
    }
}
