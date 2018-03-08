<?php
namespace DSMPackageSearch;

use \DSMPackageSearch\Source\Source;


class CacheManager
{
    private static $useCache = true;
    public static function SetUseCache($val)
    {
        self::$useCache = $val;
    }

    private static $cronMode = false;
    private static $cronModeStartsAt = null;
    public static function SetCronMode()
    {
        self::$cronMode = true;
        self::$cronModeStartsAt = microtime(true);
    }

    public static function getFullPath($cacheFolder, $url, $model, $build, $isBeta)
    {
        if ($isBeta == "true" || $isBeta == "1" || $isBeta == "on")
            $channel = "beta";
        else
            $channel = "stable";
        $source = new Source();
        $source->url = $url;
        $fileName = str_replace('/', '_', $source->urlWithoutProtocol())."_".$model."_".$build."_".$channel.".cache";
        $fullPath = $cacheFolder.$fileName;
        return $fullPath;
    }

    public static function GetPackageCacheContent($cacheFolder, $cacheExpiration, $url, $model, $build, $isBeta) 
    {
        if (self::$useCache == false || self::$cronMode == true)
            return null;

        $fullPath = CacheManager::GetFullPath($cacheFolder, $url, $model, $build, $isBeta);

        if (file_exists($fullPath)) {
            $fh = fopen($fullPath, 'r');
            $cacheTime = trim(fgets($fh));
            $timeLength = strlen($cacheTime);

            // if data was cached recently, return cached data
            if ($cacheTime > strtotime($cacheExpiration)) {
                $result = fread($fh, filesize($fullPath) - $timeLength);
                fclose($fh);
                if ($result == null) //handle with empty response
                    $result = "";
                return $result;
            }
            // else delete cache file

            self::Rotate($fullPath);

            fclose($fh);
            unlink($fullPath);
        }

        return null;
    }

    public static function GetResponseStringFromCacheFile($fullPath)
    {
        if (file_exists($fullPath)) {
            $fh = fopen($fullPath, 'r');
            $cacheTime = trim(fgets($fh));
            $timeLength = strlen($cacheTime);
            $result = fread($fh, filesize($fullPath) - $timeLength);
            fclose($fh);
            if ($result == null) //handle with empty response
               $result = "";
            return $result;
        } 
    }

    public static function SavePackageCacheContent($cacheFolder, $url, $model, $build, $isBeta, $data) 
    {
        if (self::$useCache == false && self::$cronMode == false)
            return;
        
        $fullPath = CacheManager::GetFullPath($cacheFolder, $url, $model, $build, $isBeta);

        $fh = fopen($fullPath, 'w');
        fwrite($fh, time() . "\n");
        fwrite($fh, $data);
        fclose($fh);
    }

    public static function SaveIcoToCache($downloadManager, $cacheFolder, $cacheFolderForCronMode, $cacheExpiration, $url, $packageName, $base64EncodedIcoContent)
    {
        $source = new Source();
        $source->url = $url;
        $fileName = preg_replace("/([^\w\d\-~,;\[\]\(\)\r\n])/", '_', $source->urlWithoutProtocol()."_".$packageName).".png";
        $fullPath = $cacheFolder.$fileName;
        $fullPathForCronMode = realpath($cacheFolderForCronMode).DIRECTORY_SEPARATOR.$fileName;

        if (file_exists($fullPath) && self::$cronMode == false)
        {
            $ct = filemtime($fullPath);
            if ($ct < strtotime($cacheExpiration))
            {
                unlink($fullPath);
                file_put_contents($fullPath, base64_decode($base64EncodedIcoContent));
            }
        }
        else
        {
            if (self::$cronMode == true && file_exists($fullPathForCronMode))
            {
                $ct = filemtime($fullPathForCronMode);
                if ($ct > self::$cronModeStartsAt)
                    return $fullPathForCronMode;
            }
            file_put_contents($fullPathForCronMode, base64_decode($base64EncodedIcoContent));
        }
        if (self::$cronMode == true)
            return $fullPathForCronMode;
        else
            return $fullPath;
    }

    public static function SaveThumbnailToCache($downloadManager, $cacheFolder, $cacheFolderForCronMode, $cacheExpiration, $urlBase, $packageName, $urlThumbnail)
    {
        $source = new Source();
        $source->url = $urlBase;
        $fileName = preg_replace("/([^\w\d\-~,;\[\]\(\)\r\n])/", '_', $source->urlWithoutProtocol()."_".$packageName).".png";
        $fullPath = $cacheFolder.$fileName;
        $fullPathForCronMode = realpath($cacheFolderForCronMode).DIRECTORY_SEPARATOR.$fileName;

        $pattern = '/^http/';
        if (preg_match($pattern, $urlThumbnail, $matches) != 1)
        {
            //missing protocol
            $index = stripos( $urlBase, "://");
            $urlThumbnail = substr($urlBase, 0, $index).":".$urlThumbnail;
        }
        if (file_exists($fullPathForCronMode) && self::$cronMode == false)
        {
            $ct = filemtime($fullPathForCronMode);
            $exp = strtotime($cacheExpiration);
            if ($ct < strtotime($cacheExpiration))
            {
                unlink($fullPathForCronMode);
                $thumb = $downloadManager->DownloadContent($urlThumbnail, $errorMessage);
                file_put_contents($fullPathForCronMode, $thumb);
            }
        }
        else
        {
            if (self::$cronMode == true && file_exists($fullPathForCronMode))
            {
                $ct = filemtime($fullPathForCronMode);
                $ext = strtotime($cacheExpiration);
                $diffDays = ($ct - $ext) / 86400;
                if ($ct > self::$cronModeStartsAt || $diffDays > 15) 
                    return $fullPathForCronMode;
            }
            $thumb = $downloadManager->DownloadContent($urlThumbnail, $errorMessage);
            file_put_contents($fullPathForCronMode, $thumb);
        }
        if (self::$cronMode == true)
            return $fullPathForCronMode;
        else
            return $fullPath;
    }

    public static function Rotate($fullPath)
    {
        $file = $fullPath;
        $numerOfRotations = 1;
        for($i = 0; $i< $numerOfRotations; $i++)
        {
            if (file_exists($file))
            {
                $ct = filemtime($file);
                $now = time();
                $diffDays = ($now - $ct) / 86400;
                if ($diffDays > 1)
                    copy($file, $file.$i);
            }
            $file = $fullPath.$i;            
        }
    }

}