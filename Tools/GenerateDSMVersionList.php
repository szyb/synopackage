<?php
/// run it in CLI mode:

function isVersionNumber($val)
{
    $pattern = '/\d\.\d(\.\d){0,1}/';
    $matches = null;
    preg_match($pattern, $val, $maches);
    return isset($maches[0]);
}

function isNumber($val)
{
    $pattern = '/(\d){1,10}/';
    $matches = null;
    preg_match($pattern, $val, $maches);
    return isset($maches[0]);
}

$versionsHtml = file_get_contents('https://usdl.synology.com/download/DSM/release/');
$dom = new domDocument;
@$dom->loadHtml($versionsHtml);
if ($dom == null)
{
    echo 'null';
}
else
{
    $dom->preserveWhiteSpace = false;
    $xpath = new DOMXPath($dom);
    $values=$xpath->query('//a');
    $idx = 0;
    $versionList = array();
    foreach($values as $value)
    {
        $version = substr($value->nodeValue, 0, strlen($value->nodeValue) - 1);
        if (isVersionNumber($version) == true)
        {
            // echo $version.':';

            $buildsHtml = file_get_contents('https://usdl.synology.com/download/DSM/release/'.$version.'/');
            $dom2 = new domDocument;
            @$dom2->loadHtml($buildsHtml);
            $xpath2 = new DOMXPath($dom2);
            $values2=$xpath2->query('//a');
            foreach($values2 as $value2)
            {
                $build = substr($value2->nodeValue, 0, strlen($value2->nodeValue) - 1);
                // echo $build.':'.isNumber($build).',';
                if (isNumber($build) == true)
                {
                    echo $version.'-'.$build.',';
                    $versionList[$idx] = $version.'-'.$build;
                    $idx++;
                }
            }
            echo "\n";
        }
    }
    $stringToWrite = "DSMVersions:\n";
    foreach ($versionList as $ver)
    {
        $stringToWrite.="   - ".$ver."\n";
    }
    file_put_contents("DSMVersions.yaml", $stringToWrite);
}

?>