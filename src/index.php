<?php



if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    print('Autoloader not found! Did you follow the instructions from the INSTALL.md?<br />');
    print('(If you want to keep the old version, switch to the <tt>legacy</tt> branch by running: <tt>git checkout legacy</tt>');
    exit();
}

require_once __DIR__ . '/vendor/autoload.php';

use \DSMPackageSearch\Handler;
use \DSMPackageSearch\Output\HtmlOutput;
use \DSMPackageSearch\Config;
use \DSMPackageSearch\CacheManager;
// load Monolog library
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;

try
{   
    //set-up config:
    $config = new Config(__DIR__, 'conf/config.yaml');
    $config->baseUrlRelative = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/')) . '/';
    $config->baseUrl = 'http' . (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS']?'s':'') . '://' . $_SERVER['HTTP_HOST'] . $config->baseUrlRelative;

    //set-up logger
    $log = new Logger('main_logger');
    // create a Json formatter
    $formatter = new JsonFormatter();
    
    // create a handler
    $debugHandler = new StreamHandler($config->paths['monologDebug'], Logger::DEBUG);
    $debugHandler->setFormatter($formatter);

    $errorHandler = new StreamHandler($config->paths['monologError'], Logger::ERROR, false);
    $errorHandler->setFormatter($formatter);
    
    // bind
    $log->pushHandler($debugHandler);
    $log->pushHandler($errorHandler);
    
    CacheManager::SetUseCache($config->site['useCache']);

    $handler = new Handler($config, $log);
    $handler->handle();
}
catch (\Exception $e)
{
    if (isset($log)==true)
    {
        $data = array();
        $data[0] = $e;
        $log->error('error', $data);
    }
    if (isset($config) == true)
    {
        $output = new HtmlOutput($config);
        $output->setTemplate("html_general_error");
        $output->setVariable("errorMessage", "Unexpected error");
        $output->setVariable("backLink", "?news=1");
        $output->output();
    }
    else
    {
        header('Content-type: text/html');
        header('HTTP/1.1 500 Internal Server Error');
        header('Status: 500 Internal Server Error');
    }
   

}
?>