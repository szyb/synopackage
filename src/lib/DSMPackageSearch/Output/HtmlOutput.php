<?php

namespace DSMPackageSearch\Output;

use \Mustache_Engine;
use \Mustache_Loader_FilesystemLoader;
use \Mustache_Logger_StreamLogger;

class HtmlOutput
{
    private $config;
    private $mustache;
    private $tplVars;
    private $template;

    public function __construct(\DSMPackageSearch\Config $config)
    {
        $this->config = $config;
        $tplBase  = $this->config->basePath . DIRECTORY_SEPARATOR . $this->config->relativePaths['themes'];
        $tplBase .= $this->config->site['theme'] . DIRECTORY_SEPARATOR . 'templates';

        $this->mustache = new Mustache_Engine(array(
            'loader'          => new Mustache_Loader_FilesystemLoader($tplBase),
            'partials_loader' => new Mustache_Loader_FilesystemLoader($tplBase . '/partials'),
            'charset'         => 'utf-8',
            'logger'          => new Mustache_Logger_StreamLogger('php://stderr'),
        ));
        $this->setVariable('siteName', $this->config->site['name']);
        $this->setVariable('baseUrl', $this->config->baseUrl);
        $this->setVariable('baseUrlRelative', $this->config->baseUrlRelative);
        $this->setVariable('themeUrl', $this->config->baseUrlRelative . $this->config->relativePaths['themes'] . $this->config->site['theme'] . '/');
        $this->setVariable('requestUri', $_SERVER['REQUEST_URI']);
        $this->setVariable('websiteVersion', $this->config->site['websiteVersion']);
        if (stripos($this->config->baseUrl, "localhost" ) == false || isset($this->config->site['googleStatisticsCode']) == true)
        {
            $this->setVariable('googleStatisticsCode', $this->config->site['googleStatisticsCode']);
        }
        if (isset($this->config->site['donateUrls']) == true)
        {
            $this->setVariable('donationActive', true);
            $this->setVariable('donateUrls', $this->config->site['donateUrls']);
        }
        if (isset($this->config->site['testingOnly'])== true && $this->config->site['testingOnly'] == true)
            $this->setVariable('betaHeader', true);
    }
    public function setVariable($name, $value)
    {
        $this->tplVars[$name] = $value;
    }
    public function setTemplate($tplName)
    {
        $this->template = $tplName;
    }
    public function output()
    {
        $tpl = $this->mustache->loadTemplate($this->template);
        echo $tpl->render($this->tplVars);
    }
}

?>