<?php

namespace DSMPackageSearch\Handler;
use \DSMPackageSearch\Output\HtmlOutput;

class ContactHandler extends AbstractHandler
{
    public function title()
    {
        return "Contact";
    }
    public function canHandle()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET' && array_key_exists('contact', $_GET) && !empty(trim($_GET['contact'])));
    }

    public function handle(Type $var = null)
    {
        $output = new HtmlOutput($this->config);
        $this->SetTitle($output);
        $output->setTemplate('html_contact');
        $output->output();
    }
}