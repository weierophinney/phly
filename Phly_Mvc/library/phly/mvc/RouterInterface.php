<?php
namespace phly\mvc;

interface RouterInterface
{
    public function route(EventInterface $e);
}
