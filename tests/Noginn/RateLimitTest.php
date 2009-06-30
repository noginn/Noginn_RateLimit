<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Noginn_RateLimitTest::main');
}

/**
 * Test class for Noginn_RateLimit.
 *
 * @group Noginn_RateLimit
 */
class Noginn_RateLimitTest extends PHPUnit_Framework_TestCase 
{
    protected $_cache;
    
    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function main()
    {
        $suite = new PHPUnit_Framework_TestSuite('Noginn_RateLimitTest');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $frontendOptions = array(
            'cache_id_prefix' => null,
            'automatic_serialization' => true,
        );
        
        $backendOptions = array(
            'cache_dir' => dirname(__FILE__) . '/_files/RateLimitCache',
        );
        
        $this->_cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->_cache->clean(Zend_Cache::CLEANING_MODE_ALL);
        unset($this->_cache);
    }
    
    public function testIncrement()
    {
        $rateLimit = new Noginn_RateLimit(array('127.0.0.1', 'action'), 1, 1, $this->_cache);
        $rateLimit->increment();
        $this->assertEquals(1, $rateLimit->getRequestsForInterval(0));
    }
    
    public function testRequestsCachedSeparatelyForDifferentKeys()
    {
        $rateLimit = new Noginn_RateLimit(array('127.0.0.1', 'action'), 5, 5, $this->_cache);
        $rateLimit->increment();
        $rateLimit->increment();
        $this->assertEquals(2, $rateLimit->getRequestsForInterval(0));
        
        $rateLimit2 = new Noginn_RateLimit(array('127.0.1.1', 'action'), 5, 5, $this->_cache);
        $rateLimit2->increment();
        $this->assertEquals(1, $rateLimit2->getRequestsForInterval(0));
    }
    
    public function testGetRequestsForIntervalShouldReturnFalseWhenOutOfRange()
    {
        $rateLimit = new Noginn_RateLimit(array('127.0.0.1', 'action'), 5, 5, $this->_cache);
        $this->assertFalse($rateLimit->getRequestsForInterval(-1));
        $this->assertFalse($rateLimit->getRequestsForInterval(6));
    }
    
    public function testGetTotalRequests()
    {
        $rateLimit = new Noginn_RateLimit(array('127.0.0.1', 'action'), 5, 3, $this->_cache);
        $cache = $rateLimit->getCache();
        $cache->save(1, $rateLimit->getCacheId(time() - (3 * 60)));
        $cache->save(1, $rateLimit->getCacheId(time() - (2 * 60)));
        $cache->save(1, $rateLimit->getCacheId(time() - (1 * 60)));
        $cache->save(1, $rateLimit->getCacheId());
        $this->assertEquals(3, $rateLimit->getTotalRequests());
    }
    
    public function testExceeded()
    {
        $rateLimit = new Noginn_RateLimit(array('127.0.0.1', 'action'), 1, 1, $this->_cache);
        $rateLimit->increment();
        $this->assertTrue($rateLimit->exceeded());
    }
    
    public function testNotExceeded()
    {
        $rateLimit = new Noginn_RateLimit(array('127.0.0.1', 'action'), 5, 1, $this->_cache);
        $rateLimit->increment();
        $this->assertFalse($rateLimit->exceeded());
    }
    
    public function testExceededOverTime()
    {
        $rateLimit = new Noginn_RateLimit(array('127.0.0.1', 'action'), 5, 3, $this->_cache);
        $cache = $rateLimit->getCache();
        $cache->save(1, $rateLimit->getCacheId(time() - (3 * 60)));
        $cache->save(2, $rateLimit->getCacheId(time() - (2 * 60)));
        $cache->save(3, $rateLimit->getCacheId(time() - (1 * 60)));
        $cache->save(4, $rateLimit->getCacheId());
        $this->assertTrue($rateLimit->exceeded());
    }
    
    public function testNotExceededOverTime()
    {
        $rateLimit = new Noginn_RateLimit(array('127.0.0.1', 'action'), 5, 3, $this->_cache);
        $cache = $rateLimit->getCache();
        $cache->save(1, $rateLimit->getCacheId(time() - (3 * 60)));
        $cache->save(1, $rateLimit->getCacheId(time() - (2 * 60)));
        $cache->save(1, $rateLimit->getCacheId(time() - (1 * 60)));
        $cache->save(2, $rateLimit->getCacheId());
        $this->assertFalse($rateLimit->exceeded());
    }
}

if (PHPUnit_MAIN_METHOD == 'Noginn_RateLimitTest::main') {
    Noginn_RateLimitTest::main();
}
