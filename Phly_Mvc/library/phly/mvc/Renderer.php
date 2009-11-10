<?php
namespace phly\mvc;

class Renderer implements RendererInterface
{
    protected $_view;
    protected $_layout;

    public function render(ResponseInterface $response, EventInterface $e = null)
    {
        $values  = $response->getValues();
        $globals = array();
        if (isset($values['content'])) {
            $globals = $values['content'];
        }

        $view  = $this->getView();
        $views = array();
        foreach ($values as $context => $vars) {
            $view->clearVars();
            $view->assign($globals + $vars);
            $script = $context . '.phtml';
            $views[$context] = $view->render($script);
        }

        $layout = $this->getLayout();
        $layout->assign($views);
        return $layout->render();
    }

    public function getView()
    {
        if (null === $this->_view) {
            $this->setView(new \Zend_View(array('encoding' => 'utf-8')));
        }
        return $this->_view;
    }

    public function setView(\Zend_View_Interface $view)
    {
        $this->_view = $view;
        return $this;
    }

    public function getLayout()
    {
        if (null === $this->_layout) {
            $this->setLayout(new \Zend_Layout());
        }
        return $this->_layout;
    }

    public function setLayout(\Zend_Layout $layout)
    {
        $this->_layout = $layout;
        return $this;
    }
}
