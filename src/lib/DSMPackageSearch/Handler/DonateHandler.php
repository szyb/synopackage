<?php


namespace DSMPackageSearch\Handler;
use \DSMPackageSearch\Output\HtmlOutput;

class DonateHandler extends AbstractHandler
{
    public function title()
    {
        return "Donate";
    }
    public function canHandle()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET' && 
            (count($_GET) == 0  || array_key_exists('donate', $_GET) && !empty(trim($_GET['donate'])))
        );
    }

    public function handle(Type $var = null)
    {
        $output = new HtmlOutput($this->config);
        $this->SetTitle($output);
        $output->setTemplate('html_donate');        
        $output->output();
    }
}