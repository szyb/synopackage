<?php
namespace DSMPackageSearch;
use \DSMPackageSearch\Config\Config;
use \DSMPackageSearch\Tools\ExecutionTime;


class DownloadManager
{
    private $config;
    private $log;

    public function __construct(\DSMPackageSearch\Config $config, \Monolog\Logger $log)
    {
        $this->config = $config;
        $this->log = $log;
    }

    public function DownloadContent($resourceUrl, &$errorMessage)
    {
        // $headers = array(
        //     'User-Agent:'.$userAgent
        // );
        $mark = new ExecutionTime();
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $resourceUrl);
        if (isset($this->config->site['curlProxy'])==true)
            curl_setopt($ch, CURLOPT_PROXY, $this->config->site['curlProxy']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->config->site['curlTimeout']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// read more about HTTPS http://stackoverflow.com/questions/31162706/how-to-scrape-a-ssl-or-https-url/31164409#31164409
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $mark->start();
        $result = curl_exec($ch);
        $mark->end();

        if ($this->config->site['logExecutionTime'] == true)
        {
            $data = array();
            $data["resource"] = $resourceUrl;
            $data["time"] = $mark->diff();
            $this->log->debug("curl download time", $data);
        }
        
        if (curl_errno($ch))
        {
            $errorMessage = curl_error($ch);
        }

        return $result;
    }

    public function PostRequest($resourceUrl, $postParams, $userAgent, &$errorMessage)
    {
        $headers = array(
            'User-Agent:'.$userAgent
        );
        $mark = new ExecutionTime();
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $resourceUrl);
        if (isset($this->config->site['curlProxy'])==true)
            curl_setopt($ch, CURLOPT_PROXY, $this->config->site['curlProxy']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->config->site['curlTimeout']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// read more about HTTPS http://stackoverflow.com/questions/31162706/how-to-scrape-a-ssl-or-https-url/31164409#31164409
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postParams));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $mark->start();
        $result = curl_exec($ch);
        $mark->end();

        if ($this->config->site['logExecutionTime'] == true)
        {
            $data = $postParams;
            $data["resourceUrl"] = $resourceUrl;
            $data["time"] = $mark->diff();
            $this->log->debug("curl post time", $data);
        }

        if (curl_errno($ch))
        {
            $errorMessage = curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
    
}