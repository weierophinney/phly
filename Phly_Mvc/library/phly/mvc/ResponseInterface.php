<?php
namespace phly\mvc;

interface ResponseInterface
{
    public function sendOutput(EventInterface $e);

    public function setCode($code);
    public function getCode();
    public function addHeader($name, $value, $append = false);
    public function getHeaders();
    public function assign($name, $value = null, $context = null);
    public function getValues();
    public function setRenderer(RendererInterface $renderer);
    public function getRenderer();
}
