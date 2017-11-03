<?php

namespace DSMPackageSearch\Source;

use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Yaml\Exception\ParseException;
use \DSMPackageSearch\Source\Source;

class SourceHelper
{
    private $log;
    private $config;
    private $yamlSources;

    private $sources;
    private $unsupportedUrls;
    
    public function __construct(\DSMPackageSearch\Config $config, \Monolog\Logger $logger)
    {
        $this->config = $config;
        $this->log = $logger;
        $this->yamlSources = $this->config->paths["sourceUrls"];
       
        if (!file_exists($this->yamlSources)) {
            throw new \Exception('Source list file ' . $this->yamlSources . ' not found!');
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
             $sourceList = Yaml::parse(file_get_contents($this->yamlSources));
         } catch (ParseException $e) {
             throw new \Exception($e->getMessage());
         }
         $this->sources = array();
         foreach ($sourceList as $key => $value)
         {
             if ($key == "urls")
             {
                 $idx = 0;
                 foreach ($value as $url)
                 {
                     $this->sources[$idx] = new Source();
                     $this->sources[$idx]->name = $url['source']['name'];
                     $this->sources[$idx]->url = $url['source']['url'];
                     $this->sources[$idx]->www = $url['source']['www'];
                     if (isset($url['source']['customUserAgent'])==true)
                         $this->sources[$idx]->customUserAgent = $url['source']['customUserAgent'];
                     else
                         $this->sources[$idx]->customUserAgent = null;
                     $idx++;
                 }
             } else if ($key == "unsupportedUrls")
             {
                 $idx = 0;
                 foreach ($value as $url)
                 {
                     $this->unsupportedUrls[$idx] = new Source();
                     $this->unsupportedUrls[$idx]->name = $url['source']['name'];
                     $this->unsupportedUrls[$idx]->url = $url['source']['url'];
                     $this->unsupportedUrls[$idx]->www = $url['source']['www'];
                     $this->unsupportedUrls[$idx]->customUserAgent = null;
                     $idx++;
                 }
             }
         }
     }

     public function ValidateSource($url)
     {
         if (!isset($url) || trim($url)==='')
             return false;
         if (is_string($url)==false)
             return false;
 
         $source = new Source();
         $source->url = $url;
         foreach ($this->sources as $key => $definedSource)
         {
             if ($source->urlWithoutProtocol() == $definedSource->urlWithoutProtocol())
             {
                 return true;
             }
         }
         return false;
     }

     public function ValidateSourceName($name)
     {
        if (!isset($name) || trim($name)==='')
            return false;
        if (is_string($name)==false)
            return false;
        
        foreach ($this->sources as $key => $definedSource)
        {
            if ($name == $definedSource->name)
            {
                return true;
            }
        }
        return false;
     }
     

    public function VerifyAndGetSource($name, &$source)
    {
        if (!isset($name) || trim($name)==='')
            return false;
        if (is_string($name)==false)
            return false;

        foreach ($this->sources as $key => $definedSource)
        {
            if ($name == $definedSource->name)
            {
                $source = $definedSource;
                return true;
            }
        }
        $source = null;
        return false;
    }

    public function GetSources()
    {
        return $this->sources;
    }

    public function GetUnsupportedSources()
    {
        return $this->unsupportedUrls;
    }
}
