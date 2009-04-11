<?php

class Phly_Mvc_Response_Renderer_ZendView implements Phly_Mvc_Response_Renderer_IRenderer
{
    protected $_view;

    public function render(Phly_Mvc_Response_IResponse $response)
    {
    }

    public function setView(Zend_View_Interface $view)
    {
        $this->_view = $view;
        return $this;
    }

    public function getView()
    {
        if (null === $this->_view) {
            $this->setView(new Zend_View());
        }
        return $this->_view;
    }
}
