<?php
class Phly_Couch_Document
{
    protected $_data = array();

    public function __construct($options = null)
    {
        if (is_string($options)) {
            if ('{' == substr($options, 0, 1)) {
                $this->loadJson($options);
            } else {
                $this->setId($options);
            }
        } elseif (is_array($options)) {
            $this->loadArray($options);
        } else {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception('Invalid options provided to ' . __CLASS__ . 'constructor');
        }
    }

    public function setId($id)
    {
        if ((null === $id) && !array_key_exists('_id', $this->_data)) {
            return $this;
        }
        if ((null === $id) && array_key_exists('_id', $this->_data)) {
            unset($this->_data['_id']);
            return $this;
        }
        $this->_data['_id'] = (string) $id;
        return $this;
    }

    public function getId()
    {
        if (array_key_exists('_id', $this->_data)) {
            return $this->_data['_id'];
        }
        return null;
    }

    public function setRevision($revision)
    {
        if ((null === $revision) && !array_key_exists('_rev', $this->_data)) {
            return $this;
        }
        if ((null === $revision) && array_key_exists('_rev', $this->_data)) {
            unset($this->_data['_rev']);
            return $this;
        }
        $this->_data['_rev'] = (string) $revision;
        return $this;
    }

    public function getRevision()
    {
        if (array_key_exists('_rev', $this->_data)) {
            return $this->_data['_rev'];
        }
        return null;
    }

    public function getRevisions()
    {
        if (array_key_exists('_revs_info', $this->_data)) {
            return $this->_data['_revs_info'];
        }
        return null;
    }

    public function toArray()
    {
        return $this->_data;
    }

    public function loadArray(array $array)
    {
        $this->_data = $array;
        return $this;
    }

    public function toJson()
    {
        require_once 'Zend/Json.php';
        return Zend_Json::encode($this->_data);
    }

    public function loadJson($json)
    {
        return $this->loadArray(Zend_Json::decode($json));
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->_data[$name];
        }
        return null;
    }

    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->_data);
    }

    public function __unset($name)
    {
        if (isset($this->$name)) {
            unset($this->_data[$name]);
        }
    }
}
