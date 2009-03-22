<?php
class Phly_Mvc_EventManager
{
    /**
     * @var Zend_Loader_Autoloader
     */
    protected $_autoloader;

    /**
     * @var array class methods
     */
    protected $_classMethods;

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * Constructor
     * 
     * @param  null|string|array|Zend_Config $spec 
     * @return void
     * @throws Phly_Mvc_Exception for invalid parameters
     */
    public function __construct($spec = null)
    {
        require_once 'Zend/Loader/Autoloader.php';
        $this->_autoloader = Zend_Loader_Autoloader::getInstance();
        $this->_autoloader->registerNamespace('Phly_');

        if (is_string($spec)) {
            $this->_loadConfig($spec);
        } elseif (is_array($spec)) {
            $this->setOptions($spec);
        } elseif ($spec instanceof Zend_Config) {
            $this->setOptions($spec->toArray());
        } elseif (null !== $spec) {
            throw new Phly_Mvc_Exception('Invalid specification provided to constructor');
        }
    }

    /**
     * Retrieve autoloader instance
     * 
     * @return Zend_Loader_Autoloader
     */
    public function getAutoloader()
    {
        return $this->_autoloader;
    }

    /**
     * Set options
     * 
     * @param  array $options 
     * @return Phly_Mvc_EventManager
     */
    public function setOptions(array $options)
    {
        $options         = array_change_key_case($options, CASE_LOWER);
        $this->_options += $options;

        $methods = $this->_getClassMethods();
        foreach ($options as $key => $value) {
            $method = 'set' . strtolower($key);

            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * Retrieve registered options
     * 
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Retrieve class methods
     * 
     * @return array
     */
    protected function _getClassMethods()
    {
        if (null === $this->_classMethods) {
            $methods = get_class_methods($this);
            foreach ($methods as $key => $method) {
                $methods[$key] = strtolower($method);
            }
            $this->_classMethods = $methods;
        }
        return $this->_classMethods;
    }

    /**
     * Load configuration file of options
     * 
     * @param  string $file
     * @return array
     * @throws Phly_Mvc_Exception When invalid configuration file is provided 
     */
    protected function _loadConfig($file)
    {
        $environment = $this->getEnvironment();
        $suffix      = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        switch ($suffix) {
            case 'ini':
                $config = new Zend_Config_Ini($file, $environment);
                break;
                
            case 'xml':
                $config = new Zend_Config_Xml($file, $environment);
                break;
                
            case 'php':
            case 'inc':
                $array  = include $file;
                $config = new Zend_Config($array);
                break;
                
            default:
                throw new Phly_Mvc_Exception('Invalid configuration file provided; unknown config type');
        }
        
        return $config->toArray();
    }
}
