<?php

namespace DSMPackageSearch\Handler;
use \DSMPackageSearch\Infrastructure\PagingAbstract;

abstract class AbstractHandler
{
    protected $config;
    protected $log;

    public function __construct(\DSMPackageSearch\Config $config, \Monolog\Logger $logger)
    {
        $this->config = $config;
        $this->log = $logger;
    }

    protected function SetTitle(\DSMPackageSearch\Output\HtmlOutput $output)
    {
        $output->setVariable("Title", $this->title());
    }

    protected function HandlePaging(\DSMPackageSearch\Output\HtmlOutput $output,\DSMPackageSearch\Infrastructure\PagingAbstract $pagingHelper, $itemsVariableName, $siteName)
    {
        if (array_key_exists('page', $_GET) && !empty(trim($_GET['page'])))
        {
            $pageNumber = intval($_GET['page']);
            $pagingHelper->SetPage($pageNumber);
        }
        $output->setVariable($itemsVariableName, $pagingHelper->GetItems());
        if ($pagingHelper->GetTotalPages() > 1)
        {
            $output->setVariable('site', $siteName);
            $output->setVariable('pages', $pagingHelper->GetPagesDetails());
        }
    }

    abstract public function canHandle();
    abstract public function handle();
    abstract public function title();

    
}
