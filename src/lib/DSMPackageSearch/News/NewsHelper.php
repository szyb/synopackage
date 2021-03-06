<?php

namespace DSMPackageSearch\News;
use \DSMPackageSearch\News\News;
use \DSMPackageSearch\Infrastructure\PageDetail;
use \DSMPackageSearch\Infrastructure\PagingAbstract;
use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Yaml\Exception\ParseException;
use \DateTime;
use \DateTimeZone;

class NewsHelper extends PagingAbstract
{
    private $config;
    private $newsFile;
    private $allNews;
    private $newsPerPage;

    public function __construct(\DSMPackageSearch\Config $config)
    {
        $this->config = $config;
        $this->newsFile = $this->config->paths["news"];
        $this->newsPerPage = intval($this->config->site['newsPerPage']);
        
        if (!file_exists($this->newsFile)) {
            throw new \Exception('Source list file ' . $this->newsFile . ' not found!');
        }
        try {
            $this->parseYaml();
            parent::__construct($this->newsPerPage, $this->allNews);
        } catch (\Exception $e) {
            throw $e;
        }
    }
    private function parseYaml()
    {
        try {
            $newsYaml = Yaml::parse(file_get_contents($this->newsFile));
        } catch (ParseException $e) {
            throw new \Exception($e->getMessage());
        }
        $this->allNews = array();
        $idx = 0;
        foreach ($newsYaml as $key => $value)
        {
            if ($this->config->site['testingOnly'] == true || 
                ($this->config->site['testingOnly'] == false && isset($value['message']['testingOnly']) != true))
            {
                if (isset($value['message']['publishDate']) == true)
                {
                    $timezone = new DateTimeZone('Europe/Warsaw');
                    $date = DateTime::createFromFormat('d.m.Y H:i:s', $value['message']['publishDate'], $timezone);
                    $now = new DateTime();
                    $now->setTimezone($timezone);
                    if ($now < $date)
                        continue;
                }
                $n = new News();
                $n->title = $value['message']['title'];
                $n->date = $value['message']['date'];
                $n->body = $value['message']['body'];

                // $n->body = str_ireplace('&lt;', '<', $value['message']['body']);
                // $n->body = str_ireplace('&gt;', '>', $n->body);
                $this->allNews[$idx] = $n;
                $idx++;
            }
        }
    }
}