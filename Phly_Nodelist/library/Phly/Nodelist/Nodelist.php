<?php
namespace Phly\Nodelist;
use \ArrayObject;
use \ArrayIterator;

/**
 * Nodelist (analogous to dojo.Nodelist)
 **/
class Nodelist extends ArrayObject
{
    protected $_list;
    protected $_methods = array();
    protected $_isString = false;
    protected $_originalValue;

    public function __construct($list, $flags = ArrayObject::STD_PROP_LIST, $it = 'ArrayIterator')
    {
        $this->_originalValue = $list;
        if (is_string($list)) {
            $this->_isString = true;
            $list = str_split($list);
        } elseif (!is_array($list) && (!$list instanceof \Traversable)) {
            throw new InvalidListTypeException();
        }
        parent::__construct($list, $flags, $it);
    }

    public function toString()
    {
        if ($this->_isString) {
            return $this->_originalValue;
        }
        return '';
    }

    public function toArray()
    {
        return $this->getArrayCopy();
    }

    public function getOriginal()
    {
        return $this->_originalValue;
    }

    public function push($value, $key = null)
    {
        if (is_string($key) && !empty($key)) {
            $this[$key] = $value;
            return $this;
        }
        $array = $this->getArrayCopy();
        array_push($array, $value);
        $this->exchangeArray($array);
        return $this;
    }

    public function pop()
    {
        $array = $this->getArrayCopy();
        $value = array_pop($array);
        $this->exchangeArray($array);
        return $value;
    }

    public function unshift($value, $key = null)
    {
        if (is_string($key) && !empty($key)) {
            $this[$key] = $value;
            return $this;
        }
        $array = $this->getArrayCopy();
        array_unshift($array, $value);
        $this->exchangeArray($array);
        return $this;
    }

    public function shift()
    {
        $array = $this->getArrayCopy();
        $value = array_shift($array);
        $this->exchangeArray($array);
        return $value;
    }

    /**
     * Iterate over all items in the Nodelist and perform a callback on each item.
     *
     * The callback will be passed the current value, key, and the Nodelist as 
     * arguments.
     * 
     * @param  callback $closure 
     * @return Nodelist
     */
    public function each($closure)
    {
        if (!is_callable($closure)) {
            throw new InvalidClosureException();
        }
        foreach ($this as $key => $value) {
            $closure($value, $key, $this);
        }
        return $this;
    }

    /**
     * Method overloading
     *
     * If method name matches a registered method, calls it. An instance of the 
     * current Nodelist is always passed as the final argument to the method.
     * 
     * @param  string $method 
     * @param  array $args 
     * @return Nodelist
     */
    public function __call($method, $args)
    {
        if (!array_key_exists($method, $this->_methods)) {
            throw new InvalidClosureException('No matching method');
        }
        foreach ($this as $key => $value) {
            $itemArgs = $args;
            array_push($itemArgs, $value, $key, $this);
            call_user_func_array($this->_methods[$method], $itemArgs);
        }
        return $this;
    }

    /**
     * Monkey patch a method into the Nodelist
     * 
     * @param  mixed $name 
     * @param  callback $closure 
     * @return Nodelist
     */
    public function addMethod($name, $closure)
    {
        if (!is_string($name) || empty($name)) {
            throw new \InvalidArgumentException('Invalid method name provided');
        }
        if (!is_callable($closure)) {
            throw new \InvalidArgumentException('Invalid closure provided');
        }
        $this->_methods[$name] = $closure;
        return $this;
    }

    public function getMethodClosures()
    {
        return $this->_methods;
    }
}
