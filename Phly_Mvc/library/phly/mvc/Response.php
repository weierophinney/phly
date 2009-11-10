<?php
namespace phly\mvc;

class Response implements ResponseInterface
{
    protected $_code = 200;
    protected $_headers = array();
    protected $_values = array();
    protected $_renderer;

    public function sendOutput(EventInterface $e)
    {
        $values  = $this->getValues();
        $body = $this->getRenderer()->render($this, $e);
        if (!headers_sent()) {
            header('', false, $this->getCode());
            foreach ($this->getHeaders() as $header => $content) {
                header($header . ': ' . $content);
            }
        }
        echo $body;
    }

    public function setCode($code)
    {
        $this->_code = (int) $code;
        return $this;
    }

    public function getCode()
    {
        return $this->_code;
    }

    public function addHeader($name, $value, $append = false)
    {
        if ($append && isset($this->_headers[$name])) {
            $this->_headers[$name] .= '; ' . $value;
            return $this;
        }

        $this->_headers[$name] = $value;
        return $this;
    }

    public function getHeaders()
    {
        return $this->_headers;
    }

    public function assign($name, $value = null, $context = 'content')
    {
        if (is_array($name)) {
            if (null !== $value) {
                $context = $value;
            }
            if (!isset($this->_values[$context])) {
                $this->_values[$context] = array();
            }
            $this->_values[$context] += $name;
            return $this;
        }

        if (!isset($this->_values[$context])) {
            $this->_values[$context] = array();
        }
        $this->_values[$context][(string) $name] = $value;
        return $this;
    }

    public function getValues()
    {
        return $this->_values;
    }

    public function setRenderer(RendererInterface $renderer)
    {
        $this->_renderer = $renderer;
        return $this;
    }

    public function getRenderer()
    {
        if (null === $this->_renderer) {
            $this->setRenderer(new Renderer());
        }
        return $this->_renderer;
    }
}
