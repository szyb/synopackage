<?php

namespace DSMPackageSearch\Package;
use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Yaml\Exception\ParseException;
use \DSMPackageSearch\Source\Source;
use \DSMPackageSearch\Source\SourceHelper;
use \DSMPackageSearch\Device\DeviceList;
use \DSMPackageSearch\Package\SearchResult;
use \DSMPackageSearch\CacheManager;
use \DSMPackageSearch\Tools\ExecutionTime;
use \DSMPackageSearch\DownloadManager;

class PackageHelper
{
    private $config;
    private $log;
        
    private $deviceList;
    
    private $downloadManager;
    private $sourceHelper;

    public function __construct(\DSMPackageSearch\Config $config, \Monolog\Logger $logger)
    {
        $this->config = $config;
        $this->log = $logger;
        $this->downloadManager = new DownloadManager($config, $logger);
        $this->sourceHelper = new SourceHelper($config, $logger);
        
        $this->deviceList = new DeviceList($config);
    }

    public function GetPackages($requestedSource, $arch, $model, $major, $minor, $build, $isBeta, $customUserAgent, &$errorMessage)
    {
        $result = array();
        if ($this->ValidateArch($arch) == true && $this->ValidateModel($model) == true)
        {
            try
            {
                $result = $this->RequestPackageList($requestedSource, $arch, $model, $major, $minor, $build, $isBeta, $customUserAgent, $errorMessage);
            }
            catch (\Exception $e)
            {
                $this->log->error('Error occured in "RequestPackageList": '.$e->message);
            }
        }
        else
        {
            //throw exception?
        }
        return $result;
    }

    public function SearchPackage($version, $model, $isBeta, $keyword)
    {
        $major = null;
        $minor = null;
        $build = null;
        $arch = $this->deviceList->GetArch($model); //already validated
        if ($this->GetVersionDetails($version, $major, $minor, $build) == false || $arch == null)
        {
            throw new \Exception("Validation failed: version or model");
        }
        else
        {
            $results = array();
            $idx = 0;
            $sources = $this->sourceHelper->GetSources();
            foreach($sources as $source)
            {
                $searchResult = new SearchResult();
                $searchResult->url = $source->url;
                $errorMessage = null;
                $packageList = $this->GetPackages($source->url, $arch, $model, $major, $minor, $build, $isBeta, $source->customUserAgent, $errorMessage);
                if (isset($keyword) && $keyword != "" && $keyword != "*")
                    $packageList = $this->FilterResults($packageList, $keyword);
                $searchResult->packages = $packageList;
                $searchResult->packagesFoundCount = count($packageList);
                $searchResult->urlIndex = $idx;
                $searchResult->sourceName = $source->name;
                if ($errorMessage != null)
                    $searchResult->errorMessage = $errorMessage;

                $src = new Source();
                $src->url = $source->url;
                $searchResult->urlEncoded = $src->urlEncoded();
                $results[$idx] = $searchResult;
                
                $idx++;
            }
            usort($results, array("\DSMPackageSearch\Package\PackageHelper", "CompareSearchResult"));
            return $results;
        }
        return null;
    }

    public function FilterResults($packageList, $keyword)
    {
        if (isset($packageList)==false)
            return null;
        if (count($packageList) == 0)
            return null;
        $result = array();
        $idx = 0;
        foreach($packageList as $package)
        {
            $pos = stripos($package->name, $keyword);
            if ($pos !== false)
            {
                $result[$idx] = $package;
                $idx++;
            }
            else
            {
                $pos = stripos($package->description, $keyword);
                if ($pos !== false)
                {
                   $result[$idx] = $package;
                    $idx++; 
                }
            }
        }
        return $result;
    }


    public function ValidateArch($arch)
    {
        $archList = $this->deviceList->GetArchList();
        foreach($archList as $a)
        {
            if ($a == $arch)
                return true;
        }

        return false;
    }

    public function ValidateModel($model)
    {
        $modelList = $this->deviceList->GetDevices(null);
        foreach ($modelList as $m)
        {
            if ($m['name'] == $model)
                return true;
        }
        return false;
    }

    public function GetSources()
    {
        return $this->sourceHelper->GetSources();
    }

    public function GetUnsupportedSources()
    {
        return $this->sourceHelper->GetUnsupportedSources();
    }

    public function VerifyAndGetSource($name, &$source)
    {
        return $this->sourceHelper->VerifyAndGetSource($name, $source);
    }

    public function RequestPackageList($requestedSource, $arch, $model, $major, $minor, $build, $isBeta, $customUserAgent, &$errorMessage)
    {
        //TODO: validate parameters;
        $errorMessage = null;
        $url = $requestedSource;
        $unique = 'synology_'.$arch.'_'.$model;
        if ($isBeta == true)
            $channel = "beta";
        else
            $channel = "stable";

        $data = array(
            'language'                  => 'enu', 
            'unique'                    => $unique, 
            'arch'                      => $arch, 
            'major'                     => $major,
            'minor'                     => $minor,
            'build'                     => $build, 
            'package_update_channel'    => $channel,
            'timezone'                  => 'Brussels'
            );
        $userAgent = $unique;
        if ($customUserAgent != null)
            $userAgent = $customUserAgent;
        $headers = array(
            'User-Agent:'.$userAgent
        );
            $result = null;
            $cachedResult = CacheManager::GetPackageCacheContent($this->config->paths['cache'], $this->config->site['cacheExpiration'], $requestedSource, $model, $build, $isBeta);

            if ($cachedResult == null)
            {
                $result = $this->downloadManager->PostRequest($requestedSource, $data, $userAgent, $errorMessage);
                if (isset($errorMessage) == false)
                {
                    CacheManager::SavePackageCacheContent($this->config->paths['cache'], $requestedSource, $model, $build, $isBeta, $result);
                }
              
            }
            else
            {
                $result = $cachedResult;
            }
        return $this->parseResponse($result, $requestedSource, false);
    }

    public function GetPackagesFromJsonResult($result)
    {
        return $this->parseResponse($result, null, true);
    }

    private function parseResponse($result, $requestedSource, $ignoreCachingIcons)
    {
        $packageList = array();
        $jsonDecoded = json_decode($result, false);
        $error = json_last_error();
        if ($error == JSON_ERROR_CTRL_CHAR)
        {
            //try to fix json and try again
            $result = preg_replace("/\n/", '\n', $result);
            $jsonDecoded = json_decode($result, false);
        }
        else if ($error == JSON_ERROR_SYNTAX)
        {
            // if (stripos($requestedSource, 'synology.nimloth.pl'))
            // {
            //     //temporary hack for synology.nimloth.pl
            //     if (stripos($result, '<form action="https://www.paypal.com/cgi-bin/webscr"') != 0)
            //     {
            //         $result = substr($result, 0, stripos($result, '<form action="https://www.paypal.com/cgi-bin/webscr"'));
            //         $jsonDecoded = json_decode($result);
            //     }
            // }
        }

        if ($jsonDecoded == null)
        {
            return $packageList;
        }
        
        if (isset($jsonDecoded->{'packages'}))
           $packages = $jsonDecoded->{'packages'};
        else
            $packages = $jsonDecoded;
        $idx = 0;
        foreach ($packages as $p)
        {
            $pkg = new Package();
            $pkg->name = $p->{'dname'};
            $pkg->description = $p->{'desc'};
            $pkg->version = $p->{'version'};
            $pkg->package = $p->{'package'};
            $pkg->downloadLink = $p->{'link'};
            if (isset($p->{'beta'}) == true)
                $pkg->isBeta = $p->{'beta'};
            if (isset($p->{'thumbnail'}))
            {
                if ($ignoreCachingIcons == false)
                    $pkg->thumbnail = CacheManager::SaveThumbnailToCache($this->downloadManager, 
                        $this->config->relativePaths['cache'],
                        $this->config->paths['cache'], 
                        $this->config->site['cachePngExpiration'], 
                        $requestedSource, 
                        $pkg->name, 
                        $p->{'thumbnail'}[0]);
            }
            else if (isset($p->{'icon'}))
            {
                if ($ignoreCachingIcons == false)
                    $pkg->thumbnail = CacheManager::SaveIcoToCache($this->downloadManager, 
                        $this->config->relativePaths['cache'],
                        $this->config->paths['cache'], 
                        $this->config->site['cachePngExpiration'], 
                        $requestedSource, 
                        $pkg->name, 
                        $p->{"icon"});
            }

            $packageList[$idx] = $pkg;
            $idx++;
        }
        return $packageList;
    }

    //obsolete: move to DSMVersionList
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

    static function CompareSearchResult($a, $b)
    {
        if ($a == $b)
           return 0;
        $indexA = $a->urlIndex;
        $indexB = $b->urlIndex;
        if ($a->packagesFoundCount > 0 && $b->packagesFoundCount == 0)
            return -1;
        else if ($a->packagesFoundCount == 0 && $b->packagesFoundCount > 0)
            return 1;
        else        
            return $indexA < $indexB ? -1 : 1;
    }
}
