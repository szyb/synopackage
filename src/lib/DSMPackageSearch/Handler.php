<?php

namespace DSMPackageSearch;

class Handler
{
    private $config;
    private $handlerList;
    private $log;

    public function __construct(\DSMPackageSearch\Config $config, $log)
    {
        $this->config = $config;
        $this->log = $log;

        // ordered by priority (top to bottom)
        $this->handlerList = array(
            'ContactHandler',
            'CheckTestingHandler',
            'NewsHandler',
            'SearchHandler',
            'SourceListHandler',
            'BrowseSourceHandler',
            'FaqHandler',
            'ChangelogHandler',
            'PrivacyPolicyHandler',
            'DonateHandler',
            'IndexHandler',
            'NotFoundHandler'
        );
       

    }

    public function handle()
    {

        foreach ($this->handlerList as $possibleHandler) {
            // Add namespace to class name
            $possibleHandler = '\\DSMPackageSearch\\Handler\\' . $possibleHandler;
            $handler = new $possibleHandler($this->config, $this->log);
            if ($handler->canHandle()) {
                $handler->handle();
                break;
            }
        }
       
    }
}

?>