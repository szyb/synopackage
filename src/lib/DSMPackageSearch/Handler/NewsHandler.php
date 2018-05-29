<?php


namespace DSMPackageSearch\Handler;
use \DSMPackageSearch\Output\HtmlOutput;
use \DSMPackageSearch\News\NewsHelper;

class NewsHandler extends AbstractHandler
{
    public function title()
    {
        return "News";
    }
    public function canHandle()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET' && 
            (count($_GET) == 0  || array_key_exists('news', $_GET) && !empty(trim($_GET['news'])))
        );
    }

    public function handle(Type $var = null)
    {
        $output = new HtmlOutput($this->config);
        $this->SetTitle($output);
        $output->setTemplate('html_news');
        $newsHelper = new NewsHelper($this->config);

        if (array_key_exists('page', $_GET) && !empty(trim($_GET['page'])))
        {
            $pageNumber = intval($_GET['page']);
            $newsHelper->SetPage($pageNumber);
        }

        $output->setVariable('news', $newsHelper->GetItems());
        if ($newsHelper->GetTotalPages() > 1)
        {
            $output->setVariable('pages', $newsHelper->GetPagesDetails());
        }
        $output->output();
    }
}