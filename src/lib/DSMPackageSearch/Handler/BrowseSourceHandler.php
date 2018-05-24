<?php

namespace DSMPackageSearch\Handler;
use \DSMPackageSearch\Output\HtmlOutput;
use \DSMPackageSearch\Package\PackageHelper;
use \DSMPackageSearch\Device\DeviceList;
use \DSMPackageSearch\CookieManager;

class BrowseSourceHandler extends AbstractHandler
{
    public function title()
    {
        return "Browse source";
    }

    public function canHandle()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET' && array_key_exists('browseSource', $_GET) 
            && !empty(trim($_GET['browseSource'])));
    }

    public function handle(Type $var = null)
    {
        $output = new HtmlOutput($this->config);
        $this->SetTitle($output);
        $packageHelper = new PackageHelper($this->config, $this->log);
        $deviceList = new DeviceList($this->config);

        $requestedSource = $_GET['browseSource'];
        $version = CookieManager::GetCookieOrDefault('DSMVersion', $this->config->site['latestVersion']);
        $model = CookieManager::GetCookieOrDefault("ModelName", $this->config->site['defaultModel']);
        $isBeta = CookieManager::GetCookieOrDefault('isBeta', false);
        if ($isBeta == "true")
            $isBeta = true;
        else
            $isBeta = false;

        $arch = $deviceList->GetArch($model);
        $major = null;
        $minor = null;
        $build = null;
        if ($packageHelper->GetVersionDetails($version, $major, $minor, $build) == false)
        {
            //fatal error?
        }

        $source = null;
        if ($packageHelper->VerifyAndGetSource($requestedSource, $source))
        {
            //get data here:
            $errorMessage = null;
            $packageList = $packageHelper->GetPackages($source->url, $arch, $model, $major, $minor, $build, $isBeta, $source->customUserAgent, $customUserAgent, $errorMessage);
            $output->setVariable("sourceUrl", $source->url);
            $output->setVariable("sourceWWW", $source->www);
            if ($errorMessage != null)
            {
                $output->setTemplate('html_browse_source_none');
                $output->setVariable("ModelName", $model);
                $output->setVariable("Arch", $arch);
                $output->setVariable("DSMVersion", $version);
                if ($isBeta == true)
                    $output->setVariable("isBetaChecked", 'yes');
                else    
                    $output->setVariable("isBetaChecked", 'no');
                $output->setVariable('errorMessage', $errorMessage);
            }
            else
            {
                $output->setTemplate("html_browse_source");
                $output->setVariable("packages", $packageList);
                $output->setVariable("ModelName", $model);
                if ($isBeta == true)
                    $output->setVariable("isBetaChecked", 'yes');
                else    
                    $output->setVariable("isBetaChecked", 'no');
                $output->setVariable("Arch", $arch);
                $output->setVariable("DSMVersion", $version);
                $output->setVariable("TotalPackages", count($packageList));
            }            

        }
        else
        {
            $output->setVariable("errorMessage", "Bad request: could not identify source name");
            // $log->error("Bad request: could not identify source name: ".$requestedSource);
            $output->setTemplate("html_browse_source_error");
        }        
        $output->output();
    }
}