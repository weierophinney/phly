<?php
class Phly_Mvc_Subscriber_HordeRoutes implements Phly_Mvc_Router_IRouter
{
    /**
     * @var Horde_Routes_Mapper
     */
    protected $_router;

    /**
     * Route the current request
     * 
     * @param  Phly_Mvc_Event $e 
     * @return void
     */
    public function route(Phly_Mvc_Event $e)
    {
        $url = $e->requestEnv->getPathInfo();
        $map = $this->getRouter()->match($url);

        foreach ($map as $key => $value) {
            $e->$key = $value;
        }
    }

    /**
     * Set the router
     * 
     * @param  Horde_Routes_Mapper $router 
     * @return Phly_Mvc_Subscriber_HordeRoutes
     */
    public function setRouter(Horde_Routes_Mapper $router)
    {
        $this->_router = $router;
        return $this;
    }

    /**
     * Retrieve router object
     * 
     * @return Horde_Routes_Mapper
     */
    public function getRouter()
    {
        return $this->_router;
    }
}
