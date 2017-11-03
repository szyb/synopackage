<?php

namespace DSMPackageSearch\Handler;
use \DSMPackageSearch\Handler\NewsHandler;

class NotFoundHandler extends AbstractHandler
{
    public function title()
    {
        return "Not found";
    }
    public function canHandle()
    {
        return true;
    }

    public function handle()
    {
        $otherHandler = new NewsHandler($this->config, $this->log);
        $otherHandler->handle();
        // $this->SetTitle($output);
        // header('Content-type: text/html');
        // header('HTTP/1.1 404 Not Found');
        // header('Status: 404 Not Found');
    }
}
