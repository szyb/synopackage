<?php


namespace DSMPackageSearch\Handler;
use \DSMPackageSearch\Output\HtmlOutput;
use \DSMPackageSearch\Faq\FaqHelper;

class FaqHandler extends AbstractHandler
{
    public function title()
    {
        return "FAQ";
    }
    public function canHandle()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET' && 
            (count($_GET) == 0  || array_key_exists('faq', $_GET) && !empty(trim($_GET['faq'])))
        );
    }

    public function handle(Type $var = null)
    {
        $output = new HtmlOutput($this->config);
        $this->SetTitle($output);
        $output->setTemplate('html_faq');
        $faqHelper = new FaqHelper($this->config);
        $output->setVariable('faq', $faqHelper->faq);
        $output->output();
    }
}