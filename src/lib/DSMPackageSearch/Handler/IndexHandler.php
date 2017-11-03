<?php

namespace DSMPackageSearch\Handler;
use \DSMPackageSearch\Output\HtmlOutput;

class IndexHandler extends AbstractHandler
{
    public function title()
    {
        return "Index";
    }
    public function canHandle()
    {
        return false; //off
        //return ($_SERVER['REQUEST_METHOD'] == 'GET');
    }

    public function handle(Type $var = null)
    {
        $output = new HtmlOutput($this->config);
        $this->SetTitle($output);
        $output->setTemplate('html_index');
        $output->setVariable('version', $this->config->site['websiteVersion']);
        $output->output();
    }
}