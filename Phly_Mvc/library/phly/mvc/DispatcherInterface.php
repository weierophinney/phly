<?php
namespace phly\mvc;

interface DispatcherInterface
{
    public function dispatch(EventInterface $e);
}
