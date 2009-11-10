<?php
namespace phly\mvc;

interface RendererInterface
{
    public function render(ResponseInterface $response, EventInterface $e = null);
}
