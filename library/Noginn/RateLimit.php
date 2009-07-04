<?php

/**
 * Rate limiter
 *
 * @category Noginn
 * @package Noginn_RateLimit
 * @copyright Copyright (c) 2009 Tom Graham <me@noginn.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Noginn_RateLimit
{
    /**
     * The number of requests made within each interval
     *
     * @var array
     */
    protected $_requests = array();
    
    /**
     * Keys to identify the requester, such as action and IP address
     *
     * @var string
     */
    protected $_keys;
    
    /**
     * The number of requests to allow in the time period
     *
     * @var int
     */
    protected $_limit;
    
    /**
     * The time period in minutes
     *
     * @var int
     */
    protected $_period;
    
    /**
     * The cache back-end used to store the request counts
     *
     * @var Zend_Cache_Core
     */
    protected $_cache;
    
    /**
     * Whether the cache backend if Memcache or not.
     *
     * @var bool
     */
    protected $_memcache = false;
    
    /**
     * Sets up the rate limit.
     *
     * @param mixed $keys 
     * @param int $limit 
     * @param int $period 
     * @param string|Zend_Cache_Core $cache 
     */
    public function __construct($keys, $limit, $period, $cache = 'cache')
    {
        $this->_keys = md5(serialize($keys));
        $this->_limit = (int) $limit;
        $this->_period = (int) $period;
        $this->setCache($cache);
    }
    
    /**
     * Set the cache front-end used to store the counter
     *
     * @param string|Zend_Cache_Core $cache 
     * @return Noginn_RateLimit
     */
    public function setCache($cache)
    {
        if (is_string($cache)) {
            $cache = Zend_Registry::get($cache);
        }
        
        if (!$cache instanceof Zend_Cache_Core) {
            throw new Zend_Exception('Cache not a valid registry key or Zend_Cache_Core instance');
        }
        
        $this->_cache = $cache;
        return $this;
    }
    
    /**
     * Lazy load the cache instance
     *
     * @return Zend_Cache_Core
     */
    public function getCache()
    {
        return $this->_cache;
    }
    
    /**
     * Get the cache ID where the counter is stored
     *
     * @param int $time 
     * @return string
     */
    public function getCacheId($time = null)
    {
        if ($time === null) {
            $time = time();
        }
        
        return 'ratelimit_' . date('YmdHi', $time) . '_' . $this->_keys;
    }
    
    /**
     * Get the total number of requests within the specified period
     *
     * @return int
     */
    public function getTotalRequests()
    {
        if (count($this->_requests) != $this->_period) {
            $requests = 0;
            for ($interval = 0; $interval < $this->_period; $interval++) {
                $requests += $this->getRequestsForInterval($interval);
            }
            return $requests;
        }
        
        return array_sum($this->_requests);
    }

    /**
     * Gets the number of requests for a given interval
     *
     * @param int $interval
     * @return int
     */
    public function getRequestsForInterval($interval)
    {
        if ($interval < 0 || $interval > $this->_period) {
            return false;
        }
        
        if (!isset($this->_requests[$interval])) {
            $cacheId = $this->getCacheId(time() - ($interval * 60));
            $this->_requests[$interval] = (int) $this->getCache()->load($cacheId);
        }

        return $this->_requests[$interval];
    }
    
    /**
     * Increment the counter.
     *
     * @return int The new value
     */
    public function increment()
    {
        // Get the requests for the current interval and increment the stored value
        $this->_requests[0] = $this->getRequestsForInterval(0) + 1;
        
        // Cache the request count
        $this->getCache()->save($this->_requests[0], $this->getCacheId(), array(), $this->_period * 60);
        return $this->_requests[0];
    }
    
    /**
     * Check if the rate limit has been exceeded.
     *
     * @return bool
     */
    public function exceeded()
    {
        $requests = 0;
        
        // Already retrieved the request count
        if (count($this->_requests) == $this->_period && array_sum($this->_requests) >= $this->_limit) {
            return true;
        }

        // Get the number of requests made at each interval
        for ($interval = 0; $interval < $this->_period; $interval++) {
            $requests += $this->getRequestsForInterval($interval);

            // Only make as many cache requests as necessary, return as early as possible
            if ($requests >= $this->_limit) {
                return true;
            }
        }

        return false;
    }
}
