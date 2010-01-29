<?php
namespace Phly\Nodelist;

/**
 * Define and execute closure
 **/
class Closure
{
    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidClosureException;
        }
        $this->_callback = $callback;
    }

    public function execute()
    {
        $args= func_get_args();
        call_user_func_array($this->_callback, $args);
    }
}
