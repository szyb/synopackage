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
        $mark = new ExecutionTime();
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, str_replace (' ', '%20',$resourceUrl));
        if (isset($this->config->site['curlProxy'])==true)
            curl_setopt($ch, CURLOPT_PROXY, $this->config->site['curlProxy']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->config->site['curlTimeout']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// read more about HTTPS http://stackoverflow.com/questions/31162706/how-to-scrape-a-ssl-or-https-url/31164409#31164409
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
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
        
        if ($this->isValidIcon($result) == false)
            $result = null;        

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
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
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

    private function isValidIcon(string $result)
    {
        if ($result == null || strlen($result) == 0)
            return false;
        $isValidIcon = false;
                
        $resultHeaderPng = substr($result, 0, 8);
        $resultHeaderGif = substr($result, 0, 3);
        $resultHeaderJfif = substr($result, 6, 4);

        if ($resultHeaderPng == chr(137).chr(80).chr(78).chr(71).chr(13).chr(10).chr(26).chr(10)) 
            $isValidIcon = true;
        else if ($resultHeaderGif === "GIF")
            $isValidIcon = true;
        else if ($resultHeaderJfif === "JFIF")
            $isValidIcon = true;
        
        return $isValidIcon;
    }
    
}