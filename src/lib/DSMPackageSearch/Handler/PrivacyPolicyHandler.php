<?php

namespace DSMPackageSearch\Handler;
use \DSMPackageSearch\Output\HtmlOutput;

class PrivacyPolicyHandler extends AbstractHandler
{
    public function title()
    {
        return "Privacy policy";
    }
    public function canHandle()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET'&& 
            (count($_GET) == 0  || array_key_exists('privacyPolicy', $_GET) && !empty(trim($_GET['privacyPolicy'])))
    );
    }

    public function handle(Type $var = null)
    {
        $output = new HtmlOutput($this->config);
        $this->SetTitle($output);
        $output->setTemplate('html_privacy_policy');
        $output->output();
    }
}