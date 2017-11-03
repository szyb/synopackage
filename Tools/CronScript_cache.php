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

//config:
$models = array("DS215j", "DS216j", "VirtualDSM", "DS3617xs", "DS415+", "DS1515+", "DS3018xs");
$versionBuilds = array("15152");

//setup:
$config = new Config(__DIR__.$relativeWWWRootFolder, 'conf/config.yaml');
if (!is_dir('logs')) {
    mkdir('logs', 0777, true);
}
$log = new Logger('main_logger');

// create a Json formatter
$formatter = new JsonFormatter();

// create a handler
$debugHandler = new StreamHandler("logs/debug.log", Logger::DEBUG);
$debugHandler->setFormatter($formatter);

$errorHandler = new StreamHandler("logs/error.log", Logger::ERROR, false);
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

foreach ($models as $model)
{
    $arch = $deviceList->GetArch($model);
    if (!isset($arch))
    {
        $log->error("Missing arch for model ".$model);
    }
    else
    {
        echo str_pad($model, 12);
        foreach ($sourceHelper->GetSources() as $source)
        {
            foreach ($versionBuilds as $build)
            {
                $version = $DSMVersionList->GetVersionByBuild($build);
                if (isset($version))
                {
                    $major = null;
                    $minor = null;
                    $b = null;
                    if ($DSMVersionList->GetVersionDetails($version, $major, $minor, $build) == false)
                        continue;
                    $errorMessage = null;
                    $result = $packageHelper->GetPackages($source->url, $arch, $model, $major, $minor, $build, true, $source->customUserAgent, $errorMessage);
                    echo ".";
                    if (isset($errorMessage))
                    {
                        $log->error($errorMessage);
                        echo "x";
                    }
                    $result = $packageHelper->GetPackages($source->url, $arch, $model, $major, $minor, $build, false, $source->customUserAgent, $errorMessage);
                    echo ".";
                    if (isset($errorMessage))
                    {
                        $log->error($errorMessage);
                        echo "x";
                    }
                }
            }
        }
        echo "\n";
    }    
}
?>