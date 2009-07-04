<?php

/**
 * Application bootstrap
 * 
 * @uses Zend_Application_Bootstrap_Bootstrap
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * The resource autoloader
     *
     * @var Zend_Loader_Autoloader_Resource
     */
    public $resourceAutoloader;
    
    /**
     * Bootstrap the resource autoloader
     *
     * @return void
     */
    protected function _initResourceAutoloader()
    {
        $this->resourceAutoloader = new Zend_Loader_Autoloader_Resource(array(
            'namespace' => '',
            'basePath'  => APPLICATION_PATH,
            'resourceTypes' => array(
                'form' => array(
                    'namespace' => 'Form',
                    'path' => 'forms'
                )
            )
        ));
    }
    
    /**
     * Bootstrap the cache
     *
     * @return void
     */
    protected function _initCache()
    {
        $cache = Zend_Cache::factory('Core', 'File', 
            array(
                'cache_id_prefix' => null,
                'automatic_serialization' => true,
            ), 
            array(
                'cache_dir' => ROOT_PATH . '/data/cache/',
            )
        );
        
        Zend_Registry::set('cache', $cache);
    }
    
    /**
     * Bootstrap the view doctype
     * 
     * @return void
     */
    protected function _initDoctype()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('XHTML1_STRICT');
    }
}
