<?php
namespace DSMPackageSearch\Source;

class Source
{
    public $name;
    public $url;
    public $www;
    public $customUserAgent;

    public function urlEncoded(){
        return urlencode($this->url);
    }

    public function urlWithoutProtocol()
    {
        $pos = strpos($this->url, "//");
        $urlWithoutProtocol = "";
        if ($pos !== false)
        {
            $urlWithoutProtocol = substr($this->url, $pos + 2);
        }
        else
        {
            $urlWithoutProtocol = $this->url;
        }
        return $urlWithoutProtocol;
    }
}