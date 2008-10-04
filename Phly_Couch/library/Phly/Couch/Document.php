<?php
class Phly_Couch_Document extends Phly_Couch_Element
{
    protected $_data = array();

    protected $_database;

    public function __construct($data = null, $database=null)
    {
        if (is_string($data)) {
            if ('{' == substr($data, 0, 1)) {
                $this->fromJson($data);
            } else {
                $this->setId($data);
            }
        } elseif (is_array($data)) {
            $this->fromArray($data);
        } else {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception('Invalid data provided to ' . __CLASS__ . 'constructor');
        }

        if($database instanceof Phly_Couch) {
            $this->setDatabase($database);
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

    public function fetchRevision($revision)
    {
        // TODO: Needs implementation
    }

    public function fetchAllRevisions()
    {
        // TODO: Needs implementation
    }

    public function toArray()
    {
        return $this->_data;
    }

    public function fromArray(array $array)
    {
        $this->_data = $array;
        return $this;
    }

    public function toJson()
    {
        require_once 'Zend/Json.php';
        return Zend_Json::encode($this->_data);
    }

    public function fromJson($json)
    {
        return $this->fromArray(Zend_Json::decode($json));
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

    public function save()
    {
        return $this->getDatabase()->docSave($this, $this->getId());
    }

    public function remove()
    {
        return $this->getDatabase()->docRemove($this);
    }
}
