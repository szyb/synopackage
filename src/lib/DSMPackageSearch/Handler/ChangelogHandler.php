<?php

namespace DSMPackageSearch\Handler;
use \DSMPackageSearch\Output\HtmlOutput;
use \DSMPackageSearch\Changelog\ChangelogHelper;

class ChangelogHandler extends AbstractHandler
{
    public function title()
    {
        return "Changelog";
    }
    public function canHandle()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET' && array_key_exists('changelog', $_GET) && !empty(trim($_GET['changelog'])));
    }

    public function handle(Type $var = null)
    {
        $output = new HtmlOutput($this->config);
        $this->SetTitle($output);
        $output->setTemplate('html_changelog');
        $changelogHelper = new ChangelogHelper($this->config);
        if ($changelogHelper->CheckVersionMismatch()==true)
            $output->setVariable("warningVersionMismatch", true);
        $this->HandlePaging($output, $changelogHelper, 'changelogs', 'changelog');
        $output->output();
    }
}