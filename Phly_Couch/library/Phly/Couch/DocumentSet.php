<?php
class Phly_Couch_DocumentSet implements Iterator,Countable
{
    protected $_documents = array();

    public function __construct($options = null)
    {
        if (is_string($options)) {
            $this->loadJson($options);
        } elseif (is_array($options)) {
            $this->loadArray($options);
        } else {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception('Invalid options provided to ' . __CLASS__ . 'constructor');
        }
    }

    /**
     * add 
     * 
     * @param  array|Phly_Couch_Document $document 
     * @return Phly_Couch_DocumentSet
     */
    public function add($document)
    {
        if (is_array($document)) {
            require_once 'Phly/Couch/Document.php';
            $document = new Phly_Couch_Document($document);
        } elseif (!$document instanceof Phly_Couch_Document) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception('Invalid document provided');
        }
        $id = $document->getId();
        if (null === $id) {
            $this->_documents[] = $document;
        } else {
            $this->_documents[$id] = $document;
        }
        return $this;
    }

    public function remove($id)
    {
        if (!array_key_exists($id, $this->_documents)) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Cannot remove document; id "%s" does not exist', $id));
        }
        unset($this->_documents[$id]);
        return $this;
    }

    public function fetch($id)
    {
        if (!array_key_exists($id, $this->_documents)) {
            return null;
        }
        return $this->_documents[$id];
    }

    public function clearDocuments()
    {
        $this->_documents = array();
        return $this;
    }

    public function toArray()
    {
        $documents = array();
        foreach ($this as $document) {
            $documents[] = $document->toArray();
        }
        $array = array('docs' => $documents);
        return $array;
    }

    public function loadArray(array $array)
    {
        if (array_key_exists('rows', $array)) {
            $array = $array['rows'];
        }
        foreach ($array as $document) {
            $this->add($document);
        }
        return $this;
    }

    public function toJson()
    {
        require_once 'Zend/Json.php';
        return Zend_Json::encode($this->toArray());
    }

    public function loadJson($json)
    {
        require_once 'Zend/Json.php';
        return $this->loadArray(Zend_Json::decode($json));
    }

    public function count()
    {
        return count($this->_documents);
    }

    public function current()
    {
        return current($this->_documents);
    }

    public function key()
    {
        return key($this->_documents);
    }

    public function next()
    {
        return next($this->_documents);
    }

    public function rewind()
    {
        return reset($this->_documents);
    }

    public function valid()
    {
        return $this->current() !== false;
    }
}
