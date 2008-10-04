<?php
// TODO: Could implement a max documents integer that when being hit updates all the docs and removes them from the set
class Phly_Couch_DocumentSet extends Phly_Couch_Element implements Iterator,Countable
{
    /**
     * Array of Couch documents
     *
     * @var array
     */
    protected $_documents = array();

    /**
     * Construct Document Set
     *
     * @param array|string $options
     * @param Phly_Couch   $database
     */
    public function __construct($options = null, $database=null)
    {
        if (is_string($options)) {
            $this->fromJson($options);
        } elseif (is_array($options)) {
            $this->fromArray($options);
        }

        if($database instanceof Phly_Couch) {
            $this->setDatabase($database);
        }
    }

    /**
     * Add document to the set
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

    /**
     * Remove document by given Id
     *
     * @param  string|integer $id
     * @return Phly_Couch_DocumentSet
     */
    public function remove($id)
    {
        if (!array_key_exists($id, $this->_documents)) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Cannot remove document; id "%s" does not exist', $id));
        }
        unset($this->_documents[$id]);
        return $this;
    }

    /**
     * Return a document in the set by given Id
     *
     * @param  string|integer $id
     * @return Phly_Couch_Document|null
     */
    public function fetch($id)
    {
        if (!array_key_exists($id, $this->_documents)) {
            return null;
        }
        return $this->_documents[$id];
    }

    /**
     * Reset document set to zero elements
     *
     * @return Phly_Couch_DocumentSet
     */
    public function clearDocuments()
    {
        $this->_documents = array();
        return $this;
    }

    /**
     * Use existing database connection to save all documents in bulk.
     *
     * @return Phly_Couch_Response
     */
    public function bulkSave()
    {
        return $this->getDatabase()->docBulkSave($this);
    }

    /**
     * Return array with all documents also in array format.
     *
     * @return array
     */
    public function toArray()
    {
        $documents = array();
        foreach ($this as $document) {
            $documents[] = $document->toArray();
        }
        $array = array('docs' => $documents);
        return $array;
    }

    /**
     * Populate document set from an array
     *
     * @param array $array
     * @return Phly_Couch_DocumentSet
     */
    public function fromArray(array $array)
    {
        if (array_key_exists('rows', $array)) {
            $array = $array['rows'];
        }
        foreach ($array as $document) {
            $this->add($document);
        }
        return $this;
    }

    /**
     * Return JSON representation of the complete document set
     *
     * @return string
     */
    public function toJson()
    {
        require_once 'Zend/Json.php';
        return Zend_Json::encode($this->toArray());
    }

    /**
     * Populate document set from JSON string
     *
     * @param  string $json
     * @return Phly_Couch_DocumentSet
     */
    public function fromJson($json)
    {
        require_once 'Zend/Json.php';
        return $this->fromArray(Zend_Json::decode($json));
    }

    /**
     * Return number of documents in this set.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->_documents);
    }

    /**
     * Return current element of document set.
     *
     * @return Phly_Couch_Document|boolean
     */
    public function current()
    {
        return current($this->_documents);
    }

    /**
     * Return key of current document set element.
     *
     * @return string|int
     */
    public function key()
    {
        return key($this->_documents);
    }

    /**
     * Return next element of document set.
     *
     * @return Phly_Couch_Document
     */
    public function next()
    {
        return next($this->_documents);
    }

    /**
     * Reset document set list.
     *
     * @return
     */
    public function rewind()
    {
        return reset($this->_documents);
    }

    /**
     * Return if document set is on a valid pointer.
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->current() !== false;
    }
}
