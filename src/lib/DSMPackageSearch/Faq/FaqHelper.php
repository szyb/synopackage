<?php

namespace DSMPackageSearch\Faq;
use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Yaml\Exception\ParseException;
use \DSMPackageSearch\Faq\Faq;

class FaqHelper
{
    public $config;
    public $faqFile;
    public $faq;

    public function __construct(\DSMPackageSearch\Config $config)
    {
        $this->config = $config;
        $this->faqFile = $this->config->paths["faq"];
        if (!file_exists($this->faqFile)) {
            throw new \Exception('Source list file ' . $this->faqFile . ' not found!');
        }
        try {
            $this->parseYaml();
        } catch (\Exception $e) {
            throw $e;
        }
    }
    private function parseYaml()
    {
        try {
            $faqYaml = Yaml::parse(file_get_contents($this->faqFile));
        } catch (ParseException $e) {
            throw new \Exception($e->getMessage());
        }
        $this->faq = array();
        $idx = 0;
        foreach ($faqYaml as $key => $value)
        {
            $f = new Faq();
            $f->name = $value['name'];
            $f->question = $value['question'];
            $f->answer = $value['answer'];
            $this->faq[$idx] = $f;
            $idx++;

        }
    }
}