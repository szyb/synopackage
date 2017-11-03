<?php


namespace DSMPackageSearch\Handler;
use \DSMPackageSearch\Output\HtmlOutput;
use \DSMPackageSearch\CookieManager;

class CheckTestingHandler extends AbstractHandler
{
    public function title()
    {
        return "Checking testing mode";
    }
    public function canHandle()
    {
        if (isset($this->config->site['testingOnly']) == true)
        {
            if ($this->config->site['testingOnly'] == true)
            {
                $cookie = CookieManager::GetCookieOrDefault('allowTest', null);
                $isSetAllowTest = isset($cookie);
                return $isSetAllowTest == false;
            }
            return false;
        }
        return false;
    }

    public function handle(Type $var = null)
    {
        $output = new HtmlOutput($this->config);
        $this->SetTitle($output);
        $output->setTemplate('html_check_testing');
        $output->output();
    }
}