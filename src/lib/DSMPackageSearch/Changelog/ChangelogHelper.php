<?php

namespace DSMPackageSearch\Changelog;
use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Yaml\Exception\ParseException;
use \DSMPackageSearch\Changelog\Changelog;
use \DSMPackageSearch\Infrastructure\PageDetails;
use \DSMPackageSearch\Infrastructure\PagingAbstract;

class ChangelogHelper extends PagingAbstract
{
    private $config;
    private $changelogFile;
    private $changelogs;

    public function __construct(\DSMPackageSearch\Config $config)
    {
        $this->config = $config;
        $this->changelogFile = $this->config->paths["changelog"];
        if (!file_exists($this->changelogFile)) {
            throw new \Exception('Changelog file ' . $this->changelogFile . ' not found!');
        }
        try {
            $this->parseYaml();
            parent::__construct(5, $this->changelogs);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function parseYaml()
    {
        try {
            $changelogYaml = Yaml::parse(file_get_contents($this->changelogFile));
        } catch (ParseException $e) {
            throw new \Exception($e->getMessage());
        }
        $this->changelogs = array();
        $idx = 0;
        foreach ($changelogYaml as $key => $value)
        {
            $cl = new Changelog();
            $cl->version = $value['version'];
            $cl->releaseDate = $value['date'];
            if (isset($value['new']) == true)
            {
                $cl->categoryNew = array();
                $idx2 = 0;
                foreach ($value['new'] as $info)
                {
                    $cl->categoryNew[$idx2] = $info;
                    $idx2++;
                }
            }
            if (isset($value['fixed']) == true)
            {
                $cl->categoryFixed = array();
                $idx2 = 0;
                foreach ($value['fixed'] as $info)
                {
                    $cl->categoryFixed[$idx2] = $info;
                    $idx2++;
                }
            }
            if (isset($value['improved']) == true)
            {
                $cl->categoryImproved = array();
                $idx2 = 0;
                foreach ($value['improved'] as $info)
                {
                    $cl->categoryImproved[$idx2] = $info;
                    $idx2++;
                }
            }
            if (isset($value['new sources']) == true)
            {
                $cl->categoryNewSources = array();
                $idx2 = 0;
                foreach ($value['new sources'] as $info)
                {
                    $cl->categoryNewSources[$idx2] = $info;
                    $idx2++;
                }
            }
            if (isset($value['removed sources']) == true)
            {
                $cl->categoryRemovedSources = array();
                $idx2 = 0;
                foreach ($value['removed sources'] as $info)
                {
                    $cl->categoryRemovedSources[$idx2] = $info;
                    $idx2++;
                }
            }
            $this->changelogs[$idx] = $cl;
            $idx++;
           
        }
        
    }

    public function CheckVersionMismatch()
    {
        if (isset($this->changelogs) == true && count($this->changelogs))
        {
            $data = $this->changelogs;
            usort($data, array("\DSMPackageSearch\ChangeLog\ChangelogHelper", "CompareVersion"));
            if ($data[0]->version != $this->config->site['websiteVersion'])
                return true;
        }
        return false;
    }

    static function CompareVersion($a, $b)
    {
        if ($a == $b)
            return 0;
        $splitA = explode(".", $a->version);
        $splitB = explode(".", $b->version);
        $countA = count($splitA);
        $countB = count($splitB);
        $max = 0;
        if ($countA > $countB)
            $max = $countA;
        else   
            $max = $countB;
        for ($i = 0; $i < $max; $i++)
        {
            if ($i < $countA && $i < $countB)
            {
                if (intval($splitA[$i]) < intval($splitB[$i]))
                    return 1;
                else if (intval($splitA[$i]) > intval($splitB[$i]))
                    return -1;
            }
            else if ($countA < $countB && intval($splitB[$i]) != 0)
                return 1;
            else if ($countA > $countB && intval($splitA[$i]) != 0)
                return -1;
        }
        return 0;
    }
}