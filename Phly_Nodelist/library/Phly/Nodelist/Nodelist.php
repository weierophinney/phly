<?php
/**
 * Nodelist: perform operations on all elements of strings, arrays, and Traversable objects
 *
 * @package   Phly
 * @category  Nodelist
 * @author    Matthew Weier O'Phinney
 * @copyright Matthew Weier O'Phinney, 2010
 * @version   $Id$
 **/

/**
 * Define DocBlock
 */
namespace Phly\Nodelist;
use \ArrayObject;
use \ArrayIterator;

/**
 * Nodelist (analogous to dojo.Nodelist)
 *
 * @todo      Special methods for various PHP functions (primarily string)
 * @todo      Support for some, even, odd, etc.
 * @package   Phly
 * @category  Nodelist
 **/
class Nodelist extends ArrayObject
{
    /**
     * List over which to operate
     * @var array|Traversable
     **/
    protected $_list;

    /**
     * User-defined "methods" (closures)
     *
     * @var callback[]
     **/
    protected $_methods = array();

    /**
     * Does the iterable list represent a string?
     * @var bool
     **/
    protected $_isString = false;

    /**
     * Original value passed to nodelist
     * @var string|array|Traversable
     **/
    protected $_originalValue;

    /**
     * Constructor
     *
     * Create a nodelist based on the $list provided. $list may be a string (in 
     * which case operations affect each character), an array, or any 
     * Traversable object.
     *
     * Additionally, you may pass the standard $flags and $it (iterator) 
     * arguments that affect ArrayObject instances.
     *
     * @see    \ArrayObject
     * @param  string|array|Traversable $list
     * @param  int $flags
     * @param  string $it Iterator class
     * @return void
     **/
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

    /**
     * Cast to string
     *
     * If the original value was a string, returns a current string 
     * representation (based on current object state). Otherwise, returns an
     * empty string.
     * 
     * @return string
     */
    public function toString()
    {
        if ($this->_isString) {
            return $this->_originalValue;
        }
        return '';
    }

    /**
     * Return an array representation reflecting current object state
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->getArrayCopy();
    }

    /**
     * Return original value passed to nodelist constructor
     * 
     * @return string|array|Traversable
     */
    public function getOriginal()
    {
        return $this->_originalValue;
    }

    /**
     * Push an item onto the nodelist
     *
     * Either push just a value, or a value with an explicit key, onto the 
     * nodelist.
     * 
     * @param  mixed $value 
     * @param  null|string $key 
     * @return Nodelist
     */
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

    /**
     * Remove (and return) an element from the end of the nodelist
     *
     * Either remove the last element in the nodelist, or an element by key, 
     * and return the value.
     * 
     * @param  null|string $key 
     * @return mixed
     * @throws \InvalidArgumentException for invalid keys
     */
    public function pop($key = null)
    {
        if (null !== $key) {
            if (!$this->offsetExists($key)) {
                throw new \InvalidArgumentException;
            }
            $return = $this[$key];
            unset($this[$key]);
            return $return;
        }
        $array = $this->getArrayCopy();
        $value = array_pop($array);
        $this->exchangeArray($array);
        return $value;
    }

    /**
     * Prepend an item to the nodelist, optionally with an array key
     * 
     * @param  mixed $value 
     * @param  null|string $key 
     * @return Nodelist
     */
    public function unshift($value, $key = null)
    {
        $array = $this->getArrayCopy();
        if (is_string($key) && !empty($key)) {
            $array = array_merge(array($key => $value), $array);
        } else {
            array_unshift($array, $value);
        }
        $this->exchangeArray($array);
        return $this;
    }

    /**
     * Remove the first element in the nodelist, or a named element, returning 
     * the value
     * 
     * @param  null| $key 
     * @return mixed
     * @throws \InvalidArgumentException for invalid keys
     */
    public function shift($key = null)
    {
        if (null !== $key) {
            if (!$this->offsetExists($key)) {
                throw new \InvalidArgumentException;
            }
            $r = $this[$key];
            unset($this[$key]);
            return $r;
        }
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
            call_user_func($closure, $value, $key, $this);
        }
        return $this;
    }

    /**
     * Method overloading
     *
     * If method name matches a registered method, iterate over each item in 
     * the nodelist and pass it to the closure. Any arguments passed will be 
     * passed to the closure, as well as the arguments $value, $key, and the 
     * nodelist instance.
     * <code>
     * $nl->someMethod('foo');
     * // calls someMethod('foo', $value, $key, $list) for each item
     * </code>
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
     * Any method registered will receive arguments passed to the method, as 
     * well as the arguments $value, $key, and $list, allowing you to 
     * manipulate the Nodelist in place. Such methods can then be called 
     * directly on the Nodelist.
     * <code>
     * $nl->addMethod('appendString', function($string, $value, $key, $list) {
     *     $list[$key] = $value . $string;
     * });
     * $nl->appendString(': Foo!');
     * </code>
     * 
     * @param  string $name 
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

    /**
     * Get a list of all attached method closures
     * 
     * @return array
     */
    public function getMethodClosures()
    {
        return $this->_methods;
    }
}
