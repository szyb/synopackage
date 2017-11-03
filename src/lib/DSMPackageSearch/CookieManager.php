<?php
namespace DSMPackageSearch;

class CookieManager 
{

    public static function GetCookieOrDefault($cookieName, $default)
    {
        $cookieValue = (isset($_COOKIE[$cookieName])==true ? $_COOKIE[$cookieName] : null);
        if ($cookieValue == null)
            return $default;
        else
            return $cookieValue;
    }
}