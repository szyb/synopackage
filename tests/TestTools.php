<?php

namespace DSMPackageSearch\Tests;

class TestTools
{
    /**
    * Call protected/private method of a class.
    *
    * @param object &$object    Instantiated object that we will run method on.
    * @param string $methodName Method name to call
    * @param array  $parameters Array of parameters to pass into method.
    *
    * @return mixed Method return.
    */
    public static function InvokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public static function SetPrivateProperty(&$object, $propertyName, $propertyValue)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflection_property = $reflection->getProperty($propertyName);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($object, $propertyValue);
    }
}