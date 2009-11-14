<?php
namespace phlytest\pubsub\handlers;

class Invokable
{
    public function __invoke()
    {
        return __FUNCTION__;
    }
}
