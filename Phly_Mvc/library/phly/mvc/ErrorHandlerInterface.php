<?php
namespace phly\mvc;

interface ErrorHandlerInterface
{
    public function handle(EventInterface $e);
}
