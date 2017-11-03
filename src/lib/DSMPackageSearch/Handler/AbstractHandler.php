<?php

namespace DSMPackageSearch\Handler;

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

    abstract public function canHandle();
    abstract public function handle();
    abstract public function title();

    
}
