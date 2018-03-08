<?php

//run it in CLI mode

// with trailing "/" at the end:
// $relativeWWWRootFolder = "/../www/DSMPackageSearch/";
 $relativeWWWRootFolder = "/../src/";

require_once __DIR__ . $relativeWWWRootFolder .'vendor/autoload.php';

use DSMPackageSearch\Config;
use DSMPackageSearch\Package\PackageHelper;
use \DSMPackageSearch\Source\Source;
use \DSMPackageSearch\Source\SourceHelper;
use \DSMPackageSearch\Device\DeviceList;
use \DSMPackageSearch\CacheManager;
use \DSMPackageSearch\Device\DSMVersionList;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;


function dirToArray($dir) { 
   
    $result = array(); 
 
    $cdir = scandir($dir); 
    foreach ($cdir as $key => $value) 
    { 
       if (!in_array($value,array(".",".."))) 
       { 
          if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) 
          { 
             $result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value); 
          } 
          else 
          { 
             $result[] = $value; 
          } 
       } 
    } 
    
    return $result; 
 }

function ComparePackageLists($latestPackages, $previousPacakges, $log)
{
    $foundDiff = false;
    $foundPackage = false;
    foreach($latestPackages as $latestPackage)
    {
        $foundPackage = false;
        foreach($previousPacakges as $previousPackage)
        {
            if ($latestPackage->name == $previousPackage->name)
            {
                if ($latestPackage->version != $previousPackage->version)
                {
                    echo $latestPackage->name.": PACKAGE VERSION changed: '".$latestPackage->version."' => '".$previousPackage->version."'\n";
                    $foundDiff = true;
                }
                $foundPackage = true;
            }
            if ($foundPackage == true)
                break;
        }
        if ($foundPackage == false)
        {
            echo $latestPackage->name.": NEW PACKAGE! version '".$latestPackage->version."'\n";
            $foundDiff = true;
        }
    }

    foreach($previousPacakges as $previousPackage)
    {
        $foundPackage = false;
        foreach($latestPackages as $latestPackage)
        {
            if ($latestPackage->name == $previousPackage->name)
            {
                $foundPackage = true;
                break;
            }
        }
        if ($foundPackage == false)
        {
            echo $previousPackage->name.": PACKAGE REMOVED! '".$previousPackage->version."'\n";
        }
    }
}


//setup:
$config = new Config(__DIR__.$relativeWWWRootFolder, 'conf/config.yaml');
if (!is_dir('logs')) {
    mkdir('logs', 0777, true);
}
$log = new Logger('main_logger');

// create a Json formatter
$formatter = new JsonFormatter();

// create a handler
$debugHandler = new StreamHandler("logs/debug_compare.log", Logger::DEBUG);
$debugHandler->setFormatter($formatter);

$errorHandler = new StreamHandler("logs/error_compare.log", Logger::ERROR, false);
$errorHandler->setFormatter($formatter);

// bind
$log->pushHandler($debugHandler);
$log->pushHandler($errorHandler);

echo $config->site["name"]."\n";

$packageHelper = new PackageHelper($config, $log);
$sourceHelper = new SourceHelper($config, $log);
$deviceList = new DeviceList($config);
$DSMVersionList = new DSMVersionList($config);
CacheManager::SetCronMode();
$dir = dirToArray($config->paths["cache"]);
$today = date('d-m-Y', time());

foreach ($dir as $key => $fileName)
{
    $fullPath = $config->paths["cache"]. DIRECTORY_SEPARATOR .$fileName;
    $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
    if ($ext == "cache")
    {
        $ct = date('d-m-Y', filemtime($fullPath));
        if ($ct == $today)
        {
            echo "-------------------------------\n";
            echo $fileName."\n";
            if (file_exists($fullPath."0")) //check if previous result exists
            {
                $latestResult = CacheManager::GetResponseStringFromCacheFile($fullPath);
                $latestPackages = $packageHelper->GetPackagesFromJsonResult($latestResult);
                $previousResult = CacheManager::GetResponseStringFromCacheFile($fullPath."0");
                $previousPacakges = $packageHelper->GetPackagesFromJsonResult($previousResult);
                ComparePackageLists($latestPackages, $previousPacakges, $log);
            }
        }
    }
}

?>