<?php
namespace phlytest\pubsub\handlers;

class Overloadable
{
    public function __call($method, $args)
    {
        return $method;
    }
}
