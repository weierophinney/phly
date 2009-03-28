<?php

class Phly_Mvc_Dispatcher_IncludePath implements Phly_Mvc_Dispatcher_IDispatcher
{
    /**
     * @var Zend_Loader_PluginLoader
     */
    protected $_loader;

    /**
     * Dispatch a controller action
     * 
     * @param  Phly_Mvc_Event $e 
     * @return mixed
     */
    public function dispatch(Phly_Mvc_Event $e)
    {
        $controllerName = 'index';
        if (isset($e->controller)) {
            $controllerName = $e->controller;
        }
        $actionName = 'index';
        if (isset($e->action)) {
            $actionName = $e->action;
        }

        $class = $this->getControllerClass($controllerName);
        $class = $this->loadClass($class);

        $controller = new $class($e);

        $method = $this->getActionMethod($actionName);
        
        return $controller->$method();
    }

    /**
     * Resolve controller class name
     * 
     * @param  string $name 
     * @return string
     */
    public function getControllerClass($name)
    {
        $class = str_replace(array('-', '.'), ' ', $name);
        $class = ucwords($class);
        $class = str_replace(' ', '', $class);
        $class = str_replace('/', ' ', $class);
        $class = ucwords($class);
        $class = str_replace(' ', '_', $class);
        return $class;
    }

    /**
     * Resolve action method name
     * 
     * @param  string $name 
     * @return string
     */
    public function getActionMethod($name)
    {
        $method = str_replace(array('-', '.'), ' ', $name);
        $method = ucwords($method);
        $method = str_replace(' ', '', $method);
        return $method . 'Action';
    }

    /**
     * Set plugin loader to use for class resolution
     * 
     * @param  Zend_Loader_PluginLoader $loader 
     * @return Phly_Mvc_Dispatcher_IncludePath
     */
    public function setPluginLoader(Zend_Loader_PluginLoader $loader)
    {
        $this->_loader = $loader;
        return $this;
    }

    /**
     * Get plugin loader instance
     * 
     * @return Zend_Loader_PluginLoader
     */
    public function getPluginLoader()
    {
        if (null === $this->_loader) {
            $this->setPluginLoader(new Zend_Loader_PluginLoader());
        }
        return $this->_loader;
    }

    /**
     * Attempt to resolve a controller class from final segment
     * 
     * @param  string $class 
     * @return string
     */
    public function loadClass($class)
    {
        $loader = $this->getPluginLoader();
        $final  = $loader->load($class);
        return $final;
    }
}
