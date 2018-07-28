<?php


namespace DSMPackageSearch\Tests;

use \DSMPackageSearch\Config;
use \DSMPackageSearch\Source\SourceHelper;
use \PHPUnit\Framework\TestCase;
use \DSMPackageSearch\Package\SearchResult;
use \DSMPackageSearch\Source\Source;

// load Monolog library
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;

class SourceHelperTest extends TestCase
{
    private $goodConfig ='/config-files/goodConfig.yaml';
    private $notExistFilesConfig = '/config-files/nonexistConfig.yaml';
    private $badSourceConfig = '/config-files/goodConfigBadOthers.yaml';
    
    public function testParseYaml()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $this->assertTrue(isset($helper));
    }

    /**
        * @expectedException \Exception
        * @expectedExceptionMessageRegExp /^Source list file .*nonexist.yaml not found!$/
    */
    public function testNotExists()
    {
        $config = new Config(__DIR__, $this->notExistFilesConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /^Unable to parse at line.*$/
     */
     public function testBadYaml()
     {
        $config = new Config(__DIR__, $this->badSourceConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
     }

    public function testValidateSourceNull()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $result = $helper->ValidateSource(null);
        $this->assertFalse($result);
    }

    public function testValidateSourceNotString()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $result = $helper->ValidateSource(5);
        $this->assertFalse($result);
    }

    public function testValidateSourceEmptyString()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $result = $helper->ValidateSource("");
        $this->assertFalse($result);
    }

    public function testValidateSourceExists()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $result = $helper->ValidateSource("http://packages.synocommunity.com");
        $this->assertTrue($result);
    }

    public function testValidateSourceNotExists()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $result = $helper->ValidateSource("http://packages.synology.com");
        $this->assertFalse($result);
    }

    public function testVerifyAndGetSourceNull()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $source = null;
        $result = $helper->VerifyAndGetSource(null, $source);
        $this->assertFalse($result);
    }

    public function testVerifyAndGetSourceEmpty()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $source = null;
        $result = $helper->VerifyAndGetSource("", $source);
        $this->assertFalse($result);
    }

    public function testVerifyAndGetSourceExists()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $source = null;
        $result = $helper->VerifyAndGetSource("synocommunity", $source);
        $this->assertTrue($result);
        $this->assertEquals("http://packages.synocommunity.com", $source->url);
    }

    public function testVerifyAndGetSourceNotExists()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $source = null;
        $result = $helper->VerifyAndGetSource("synology", $source);
        $this->assertFalse($result);
        $this->assertEquals(null, $source);
    }

    public function testVerifyAndGetSourceNotString()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $source = null;
        $result = $helper->VerifyAndGetSource(5, $source);
        $this->assertFalse($result);
    }

    public function testGetSources()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $sources = $helper->GetSources();
        $this->assertNotNull($sources);
        $this->assertEquals(3, count($sources));
    }

    public function testGetUnsupportedSources()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $sources = $helper->GetUnsupportedSources();
        $this->assertNotNull($sources);
        $this->assertEquals(1, count($sources));
    }

    public function testCustomUserAgent()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $source= null;
        $result = $helper->VerifyAndGetSource("synologyitalia", $source);
        $this->assertTrue($result);
        $this->assertTrue(isset($source->customUserAgent));
    }

    public function testUrlEncoded()
    {
        $source = new Source();
        $source->url = "http://packages.synocommunity.com/file with spaces.spk";
        $this->assertEquals("http%3A%2F%2Fpackages.synocommunity.com%2Ffile+with+spaces.spk", $source->urlEncoded());
    }

    public function testUrlWithoutProtocolHttp()
    {
        $source = new Source();
        $source->url = "http://packages.synocommunity.com";
        $this->assertEquals("packages.synocommunity.com", $source->urlWithoutProtocol());
    }

    public function testUrlWithoutProtocolHttps()
    {
        $source = new Source();
        $source->url = "https://packages.synocommunity.com";
        $this->assertEquals("packages.synocommunity.com", $source->urlWithoutProtocol());
    }

    public function testUrlWithoutProtocolNoProtocolDefined()
    {
        $source = new Source();
        $source->url = "packages.synocommunity.com";
        $this->assertEquals("packages.synocommunity.com", $source->urlWithoutProtocol());
    }

    public function testValidateSourceNameProperName()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $source= null;
        $result = $helper->ValidateSourceName("synocommunity");
        $this->assertTrue($result);
       
    }

    public function testValidateSourceNameNotProperName()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $source= null;
        $result = $helper->ValidateSourceName("x");
        $this->assertFalse($result);
    }

    public function testValidateSourceNameNull()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $result = $helper->ValidateSourceName(null);
        $this->assertFalse($result);
    }

    public function testValidateSourceNameNotString()
    {
        $config = new Config(__DIR__, $this->goodConfig);
        $log = new Logger('main_logger');
        $helper = new SourceHelper($config, $log);
        $result = $helper->ValidateSourceName(5);
        $this->assertFalse($result);
    }
}