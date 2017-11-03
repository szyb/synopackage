<?php

namespace DSMPackageSearch\Handler;
use \DSMPackageSearch\Output\HtmlOutput;
use \DSMPackageSearch\Package\PackageHelper;
use \DSMPackageSearch\Device\DSMVersionList;
use \DSMPackageSearch\Device\DeviceList;
use \DSMPackageSearch\CookieManager;

class SourceListHandler extends AbstractHandler
{
    public function title()
    {
        return "Source list";
    }
    public function canHandle()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET' && array_key_exists('sourceList', $_GET) && !empty(trim($_GET['sourceList'])));
    }

    public function handle(Type $var = null)
    {
        $output = new HtmlOutput($this->config);
        $this->SetTitle($output);
        $packageHelper = new PackageHelper($this->config, $this->log);

        $sources = $packageHelper->GetSources();
        $unsuppoertedSourceList = $packageHelper->GetUnsupportedSources();
        
        if ($sources == null || count($sources) == 0)
        {
            $output->setTemplate("html_source_none");
        }
        else
        {
            $DSMVersions = new DSMVersionList($this->config);
            $DeviceList = new DeviceList($this->config);

            $selectedVersion = CookieManager::GetCookieOrDefault('DSMVersion', $this->config->site['latestVersion']);
            $selectedModel = CookieManager::GetCookieOrDefault('ModelName', $this->config->site['defaultModel']);
            $isBeta = CookieManager::GetCookieOrDefault('isBeta', false);
            if ($isBeta == "true")
                $isBeta = true;
            else
                $isBeta = false;

            $DSMVersionList = $DSMVersions->GetDSMVersions($selectedVersion);
            $modelList = $DeviceList->GetDevices($selectedModel);
            $output->setTemplate('html_source_list');
            $output->setVariable("sourceList", $sources );
            $output->setVariable("unsupportedSourceList", $unsuppoertedSourceList);
            $output->setVariable("showOptions", true);
            $output->setVariable("DSMVersionList", $DSMVersionList);
            $output->setVariable("ModelList", $modelList);
            if ($isBeta == true)
                $output->setVariable("isBetaChecked", $isBeta);
        }
        $output->output();
    }
}