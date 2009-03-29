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
     * @var Phly_Mvc_Dispatcher_IDispatcher
     */
    protected $_dispatcher;

    /**
     * @var string application environment
     */
    protected $_environment;

    /**
     * @var Phly_Mvc_Event
     */
    protected $_event;

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * @var Phly_Mvc_PubSubProvider
     */
    protected $_pubSub;

    /**
     * @var Phly_Mvc_Request_Request
     */
    protected $_request;

    /**
     * @var Phly_Mvc_Router_IRouter
     */
    protected $_router;

    /**
     * Constructor
     * 
     * @param  null|string|array|Zend_Config $spec 
     * @param  null|string $env Application environment
     * @return void
     * @throws Phly_Mvc_Exception for invalid parameters
     */
    public function __construct($spec = null, $env = null)
    {
        require_once 'Zend/Loader/Autoloader.php';
        $this->_autoloader = Zend_Loader_Autoloader::getInstance();
        $this->_autoloader->registerNamespace('Phly_');

        if (null !== $env) {
            $this->setEnvironment($env);
        }

        if (is_string($spec)) {
            $config = $this->_loadConfig($spec);
            $this->setOptions($config);
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
     * Set application environment
     * 
     * @param  string $env 
     * @return Phly_Mvc_EventManager
     */
    public function setEnvironment($env)
    {
        $this->_environment = (string) $env;
        return $this;
    }

    /**
     * Retrieve environment
     * 
     * @return string
     */
    public function getEnvironment()
    {
        return $this->_environment;
    }

    /**
     * Set event object
     * 
     * @param  Phly_Mvc_Event $event 
     * @return Phly_Mvc_EventManager
     */
    public function setEvent(Phly_Mvc_Event $event)
    {
        $this->_event = $event;
        return $this;
    }

    /**
     * Retrieve event object
     * 
     * @return Phly_Mvc_Event
     */
    public function getEvent()
    {
        if (null === $this->_event) {
            $this->setEvent(new Phly_Mvc_Event);
        }
        return $this->_event;
    }

    /**
     * Retrieve registered events
     * 
     * @return array
     */
    public function getTopics()
    {
        return $this->getPubSubProvider()->getTopics();
    }

    /**
     * Set the pub-sub provider that contains all events and callbacks
     * 
     * @param  Phly_Mvc_PubSubProvider $pubsub 
     * @return Phly_Mvc_EventManager
     */
    public function setPubSubProvider(Phly_Mvc_PubSubProvider $pubsub)
    {
        $this->_pubSub = $pubsub;
        return $this;
    }

    /**
     * Retrieve the pub-sub provider (which contains all events and callbacks)
     * 
     * @return Phly_Mvc_PubSubProvider
     */
    public function getPubSubProvider()
    {
        if (null === $this->_pubSub) {
            $pubSub = new Phly_Mvc_PubSubProvider();
            $pubSub->subscribe('mvc.routing', $this->getRouter(), 'route');
            $pubSub->subscribe('mvc.action', $this->getDispatcher(), 'dispatch');
            $pubSub->subscribe('mvc.response', $this->getResponse(), 'send');
            $pubSub->subscribe('mvc.error', $this, 'handleException');
            $this->setPubSubProvider($pubSub);
        }
        return $this->_pubSub;
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
     * Handle events 
     * 
     * @return void
     */
    public function handle(Phly_Mvc_Event $event = null)
    {
        if (null === $event) {
            $event  = $this->getEvent();
        }
        $event->setEventManager($this)
              ->setRequest($this->getRequest())
              ->setResponse($this->getResponse());

        $pubSub = $this->getPubSubProvider();
        foreach ($this->getTopics() as $topic) {
            if ('mvc.error' == $topic) {
                continue;
            }
            $pubSub->publish($topic, $event);
        }
    }

    /**
     * Set the request object
     * 
     * @param  Phly_Mvc_Request_Request $request 
     * @return Phly_Mvc_EventManager
     */
    public function setRequest(Phly_Mvc_Request_Request $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Retrieve the request object
     *
     * Lazy loads Phly_Mvc_Request_Http by default if no request object 
     * currently registered.
     * 
     * @return Phly_Mvc_Request_Request
     */
    public function getRequest()
    {
        if (null === $this->_request) {
            $this->setRequest(new Phly_Mvc_Request_Http());
        }
        return $this->_request;
    }

    /**
     * Set the response object
     * 
     * @param  Phly_Mvc_Response_Response $response 
     * @return Phly_Mvc_EventManager
     */
    public function setResponse(Phly_Mvc_Response_IResponse $response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * Retrieve the response object
     *
     * Lazy loads Phly_Mvc_Response_Http by default if no response object 
     * currently registered.
     * 
     * @return Phly_Mvc_Response_IResponse
     */
    public function getResponse()
    {
        if (null === $this->_response) {
            $this->setResponse(new Phly_Mvc_Response_Http());
        }
        return $this->_response;
    }

    /**
     * Set router
     * 
     * @param  Phly_Mvc_Router_IRouter $router 
     * @return Phly_Mvc_EventManager
     */
    public function setRouter(Phly_Mvc_Router_IRouter $router)
    {
        $this->_router = $router;
        return $this;
    }

    /**
     * Retrieve router
     * 
     * @return Phly_Mvc_Router_IRouter
     */
    public function getRouter()
    {
        if (null === $this->_router) {
            $this->setRouter(new Phly_Mvc_Subscriber_HordeRoutes());
        }
        return $this->_router;
    }

    /**
     * Set dispatcher to use
     * 
     * @param  Phly_Mvc_Dispatcher_IDispatcher $dispatcher 
     * @return Phly_Mvc_EventManager
     */
    public function setDispatcher(Phly_Mvc_Dispatcher_IDispatcher $dispatcher)
    {
        $this->_dispatcher = $dispatcher;
        return $this;
    }

    /**
     * Retrieve dispatcher to use with action event
     * 
     * @return Phly_Mvc_Dispatcher_IDispatcher
     */
    public function getDispatcher()
    {
        if (null === $this->_dispatcher) {
            $this->setDispatcher(new Phly_Mvc_Dispatcher_IncludePath());
        }
        return $this->_dispatcher;
    }

    public function handleException()
    {
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
