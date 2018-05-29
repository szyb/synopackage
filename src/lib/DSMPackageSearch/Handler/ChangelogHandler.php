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
        
        if (array_key_exists('page', $_GET) && !empty(trim($_GET['page'])))
        {
            $pageNumber = intval($_GET['page']);
            $changelogHelper->SetPage($pageNumber);
        }
        $output->setVariable('changelogs', $changelogHelper->GetItems());
        if ($changelogHelper->GetTotalPages() > 1)
        {
            $output->setVariable('site', 'changelog');
            $output->setVariable('pages', $changelogHelper->GetPagesDetails());
        }

        $output->output();
    }
}