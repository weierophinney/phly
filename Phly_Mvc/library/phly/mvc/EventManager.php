<?php
namespace phly\mvc;
use \phly\pubsub\Provider as Provider; 

class EventManager extends Provider
{
    protected $_router;
    protected $_dispatcher;
    protected $_response;
    protected $_errorHandler;

    public function __construct($options = null)
    {
        if ($options instanceof \Zend_Config) {
            $options = $options->toArray();
        }
        if (is_array($options)) {
            foreach ($options as $key => $value) {
                $method = 'set' . $key;
                if (method_exists($method)) {
                    $this->$method($value);
                }
            }
        }

        $router     = $this->getRouter();
        $dispatcher = $this->getDispatcher();
        $response   = $this->getResponse();
        $errors     = $this->getErrorHandler();

        $this->subscribe('mvc.routing', $router, 'route');
        $this->subscribe('mvc.dispatching', $dispatcher, 'dispatch');
        $this->subscribe('mvc.response', $response, 'sendOutput');
        $this->subscribe('mvc.error', $errors, 'handle');
    }

    public function setRouter(RouterInterface $router) 
    {
        $this->_router = $router;
        return $this;
    }

    public function getRouter()
    {
        if (null === $this->_router) {
            $this->setRouter(new Router());
        }
        return $this->_router;
    }

    public function setDispatcher(DispatcherInterface $dispatcher) 
    {
        $this->_dispatcher = $dispatcher;
        return $this;
    }

    public function getDispatcher()
    {
        if (null === $this->_dispatcher) {
            $this->setDispatcher(new Dispatcher());
        }
        return $this->_dispatcher;
    }

    public function setResponse(ResponseInterface $response) 
    {
        $this->_response = $response;
        return $this;
    }

    public function getResponse()
    {
        if (null === $this->_response) {
            $this->setResponse(new Response());
        }
        return $this->_response;
    }

    public function setErrorHandler(ErrorHandlerInterface $errorHandler) 
    {
        $this->_errorHandler = $errorHandler;
        return $this;
    }

    public function getErrorHandler()
    {
        if (null === $this->_errorHandler) {
            $this->setErrorHandler(new ErrorHandler());
        }
        return $this->_errorHandler;
    }
}
