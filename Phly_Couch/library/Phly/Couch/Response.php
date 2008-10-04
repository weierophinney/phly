<?php

/**
 * @see Zend_Json
 */
require_once 'Zend/Json.php';

class Phly_Couch_Response implements Iterator, Countable
{
    /**
     * The raw response message
     *
     * @var string
     */
    protected $_rawResponse;

    /**
     * Response status
     *
     * @var int
     */
    protected $_status;

    /**
     * Response headers
     *
     * @var string
     */
    protected $_headers;

    /**
     * Response body
     *
     * @var string
     */
    protected $_body;

    /**
     * Constructor
     *
     * @param string $rawResponse
     */
    public function __construct($rawResponse)
    {
        $this->_rawResponse = $rawResponse;

        list($rawHeaders, $this->_body) = explode("\r\n\r\n", $rawResponse);
        $this->_body = Zend_Json::decode($this->_body);

        $rawHeaders = explode("\r\n", $rawHeaders);

        $this->_status = (int) substr(array_shift($rawHeaders), 9, 3);

        $headers = array();

        foreach ($rawHeaders as $header) {
            list($key, $value) = explode(': ', $header);
            $headers[$key] = $value;
        }

        $this->_headers = $headers;
    }

    /**
     * Get the raw response
     *
     * @return string
     */
    public function getRawResponse()
    {
        return $this->_rawResponse;
    }

    /**
     * Get the response as an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getBody();
    }

    /**
     * Get status code
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Check whether the response in successful
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        $restype = floor($this->getStatus() / 100);
        if ($restype == 2 || $restype == 3) {
            return true;
        }

        return false;
    }

    /**
     * Get the response headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Get a specific header.
     * Returns null when the header doesn't exist
     *
     * @param  string $header
     * @return string|null
     */
    public function getHeader($header)
    {
        if (isset($this->_headers[$header])) {
            return $this->_headers[$header];
        }

        return null;
    }

    /**
     * Get body element of the response by name.
     *
     * Since body is a JSON object in the CouchDB context, this accesses the deserialized
     * JSON array.
     *
     * @param  string $name
     * @return null|string|array|integer
     */
    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->_body[$name];
        }
        return null;
    }

    /**
     * Check if a given key is element of the response
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_body);
    }

    /**
     * Get the response body
     *
     * @return array
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * Return the Raw Response String
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getRawResponse();
    }

    /**
     * Count number of elements in the JSON response of CouchDB.
     *
     * @return int
     */
    public function count()
    {
        return count($this->_body);
    }

    /**
     * Return current element of JSON response.
     *
     * @return string|array|integer|boolean
     */
    public function current()
    {
        return current($this->_body);
    }

    /**
     * Return key of current JSON response.
     *
     * @return string|integer
     */
    public function key()
    {
        return key($this->_body);
    }

    /**
     * Return next element of JSON response.
     *
     * @return string|array|integer|boolean
     */
    public function next()
    {
        return next($this->_body);
    }

    /**
     * Reset array pointer of current JSON response.
     *
     * @return
     */
    public function rewind()
    {
        return reset($this->_body);
    }

    /**
     * Check if array pointer is valid.
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->current() !== false;
    }
}