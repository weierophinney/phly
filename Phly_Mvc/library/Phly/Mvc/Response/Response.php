<?php

class Phly_Mvc_Response_Response implements Phly_Mvc_Response_IResponse
{
    protected $_event;

    protected $_layout;

    protected $_metadata = array();

    protected $_renderer;

    protected $_views = array();

    public function send(Phly_Mvc_Event $e)
    {
        $this->setEvent($e);
        $renderer = $this->getRenderer();
        $renderer->render($this);
    }

    public function setEvent(Phly_Mvc_Event $e)
    {
        $this->_event = $e;
        return $this;
    }

    public function getEvent()
    {
        return $this->_event;
    }

    public function addView($name, $vars = null)
    {
        $this->_views[$name] = $vars;
        return $this;
    }

    public function getViews()
    {
        return $this->_views;
    }

    public function hasView($name)
    {
        return array_key_exists($name, $this->_views);
    }

    public function getViewVars($name)
    {
        if ($this->hasView($name)) {
            return $this->_views[$name];
        }
        return array();
    }

    public function removeView($name)
    {
        if ($this->hasView($name)) {
            unset($this->_views[$name]);
        }
        return $this;
    }

    public function clearViews()
    {
        $this->_views = array();
        return $this;
    }

    public function setMetadata($name, $value)
    {
        if ($this->hasMetadata($name)) {
            $this->removeMetadata($name);
        }
        $this->addMetadata($name, $value);
        return $this;
    }

    public function addMetadata($name, $value)
    {
        if (!array_key_exists($name, $this->_metadata)) {
            $this->_metadata[$name] = array();
        }
        $this->_metadata[$name][] = (string) $value;
        return $this;
    }

    public function hasMetadata($name)
    {
        return array_key_exists($name, $this->_metadata);
    }

    public function getMetadata($name = null)
    {
        if (null === $name) {
            return $this->_metadata;
        }
        if (empty($this->_metadata[$name])) {
            return array();
        }

        return $this->_metadata[$name];
    }

    public function removeMetadata($name)
    {
        if (!empty($this->_metadata[$name])) {
            unset($this->_metadata[$name]);
        }
        return $this;
    }

    public function clearMetadata()
    {
        $this->_metadata = array();
    }

    public function setLayout($name)
    {
        $this->_layout = (string) $name;
        return $this;
    }

    public function getLayout()
    {
        return $this->_layout;
    }

    public function setRenderer($rendererOrClass)
    {
        if (is_string($rendererOrClass)) {
            $rendererOrClass = new $rendererOrClass();
        } 
        
        if (!$rendererOrClass instanceof Phly_Mvc_Response_Renderer_IRenderer) {
            throw new Phly_Mvc_Exception('Invalid renderer provided to response');
        }

        $this->_renderer = $rendererOrClass;
        return $this;
    }

    public function getRenderer()
    {
        if (null === $this->_renderer) {
            $this->setRenderer('Phly_Mvc_Response_Renderer_ZendView');
        }
        return $this->_renderer;
    }
}
