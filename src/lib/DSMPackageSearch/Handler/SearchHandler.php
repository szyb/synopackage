<?php

namespace DSMPackageSearch\Handler;
use \DSMPackageSearch\Output\HtmlOutput;
use \DSMPackageSearch\Package\PackageHelper;
use \DSMPackageSearch\Device\DeviceList;
use \DSMPackageSearch\CookieManager;
use \DSMPackageSearch\Device\DSMVersionList;

class SearchHandler extends AbstractHandler
{
    public function title()
    {
        return "Search";
    }
    public function canHandle()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET' && array_key_exists('search', $_GET)
            && !empty(trim($_GET['search']))
            );
    }

    public function handle(Type $var = null)
    {
        $selectedModel = null;
        $selectedVersion =  null;
        if (isset($_GET['model']) == true)
            $selectedModel = $_GET['model'];
        else 
            $selectedModel = CookieManager::GetCookieOrDefault('ModelName', $this->config->site['defaultModel']);
        if (isset($_GET['version']) == true)
            $selectedVersion = $_GET['version'];
        else
            $selectedVersion = CookieManager::GetCookieOrDefault('DSMVersion', $this->config->site['latestVersion']);
        
        if (isset($_GET['keyword']) == true)
            $keyword = $_GET['keyword'];
        else 
            $keyword = null;

        $isBetaQuery = null;
        $isBeta = false;
        if (isset($_GET['isBeta'])==true)
            $isBetaQuery = $_GET['isBeta'];
        else
            $isBetaQuery = CookieManager::GetCookieOrDefault('isBeta', false);

        if ($isBetaQuery == "on" || $isBetaQuery == "true")
            $isBeta = true;
            

        $output = new HtmlOutput($this->config);
        $this->SetTitle($output);
        $packageHelper = new PackageHelper($this->config, $this->log);
        $deviceList = new DeviceList($this->config);
        $DSMVersions = new DSMVersionList($this->config);
        $DSMVersionList = $DSMVersions->GetDSMVersions($selectedVersion);
        $modelList = $deviceList->GetDevices($selectedModel);
        $output->setVariable("DSMVersionList", $DSMVersionList);
        $output->setVariable("ModelList", $modelList);
        if ($isBeta == true)
        {
            $output->setVariable("isBetaChecked", 'yes');
            $output->setVariable('isBetaFilter', $isBeta);
        }
        else
            $output->setVariable("isBetaChecked", 'no');

        $data = array(
            'model' => $selectedModel,
            'version' => $selectedVersion,
            'keyword' => $keyword,
            'isBeta'  => $isBeta
        );

        if (
               array_key_exists('version', $_GET)
            && array_key_exists('model', $_GET)
            && array_key_exists('keyword', $_GET)
            && !empty(trim($_GET['version']))
            && !empty(trim($_GET['model']))
            && !empty(trim($_GET['keyword']))
            )
        {
            // $version = $_GET['version'];
            if ($packageHelper->GetVersionDetails($selectedVersion, $major, $minor, $build) == false)
            {
                $this->log->error("Invalid query: Could not identify DSM version", $data);
                $output->setTemplate("html_general_error");
                $output->setVariable("errorMessage", "Invalid query: Could not identify DSM version");
                $output->setVariable("backLink", "?search=1");
            }
            else if ($packageHelper->ValidateModel($selectedModel) == false)
            {
                $this->log->error("Invalid query: Could not identify model", $data);
                $output->setTemplate("html_general_error");
                $output->setVariable("errorMessage", "Invalid query: Could not identify model");
                $output->setVariable("backLink", "?search=1");
            }
            else
            {
                $results = $packageHelper->SearchPackage($selectedVersion, $selectedModel, $isBeta, $keyword);

                $output->setTemplate('html_search_results');
                $output->setVariable('results', $results);
                $output->setVariable('ModelName', $selectedModel);
                $output->setVariable('Arch', $deviceList->GetArch($selectedModel));
                $output->setVariable('DSMVersion', $selectedVersion);
                $output->setVariable('isCallBack', true);
                $output->setVariable('keyword', $keyword);
                
                if ($this->config->site['logSearchQueries'] == true)
                {
                    $this->log->debug('search query', $data);
                }
            }        
            $output->output();
        }
        else
        {
            $output->setTemplate("html_search_results");
            $output->output();
        }


        
    }
}