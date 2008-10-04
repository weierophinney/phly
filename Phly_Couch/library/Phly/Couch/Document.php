<?php
class Phly_Couch_Document extends Phly_Couch_Element
{
    /**
     * Document data
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Revision information of document
     *
     * @var array|nuull
     */
    protected $_revs_info = null;

    /**
     * Attachments of this document
     *
     * @var array
     */
    protected $_attachments;

    /**
     * Construct new document
     *
     * @param  string|array $data
     * @param  Phly_Couch   $database optional
     * @throws Phly_Couch_Exception
     */
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

    /**
     * Set or overwrite old document id
     *
     * @param  mixed $id
     * @return Phly_Couch_Document
     */
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

    /**
     * Get ID of this document
     *
     * @return string|integer
     */
    public function getId()
    {
        if (array_key_exists('_id', $this->_data)) {
            return $this->_data['_id'];
        }
        return null;
    }

    /**
     * Set the revision of a document.
     *
     * Be careful when updating loaded documents. A changed revision id can lead to a
     * conflict while saving and the process aborts.
     *
     * @param  string|integer $revision
     * @return Phly_Couch_Document
     */
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

    /**
     * Get current revision of this document
     *
     * @return integer|string|null
     */
    public function getRevision()
    {
        if (array_key_exists('_rev', $this->_data)) {
            return $this->_data['_rev'];
        }
        return null;
    }

    /**
     * Just returns an instance of the given revision of the document, does not revert the current one.
     *
     * @param  string|integer $revision
     * @return Phly_Couch_Document
     */
    public function fetchRevision($revision)
    {
        return $this->getDatabase()->docOpen($this->getId(), array('rev' => $revision));
    }

    /**
     * Revert document instance to a specific given revision.
     *
     * @param string|integer
     * @return Phly_Couch_Document
     */
    public function revertToRevision($revision)
    {
        $doc = $this->fetchRevision($revision);
        $this->fromArray($doc->toArray());
        unset($doc);
        return $this;
    }

    /**
     * Returns an array of information on document revisions.
     *
     * Depending on how the document has been loaded in the first place (&revs=true or
     * &revs_info=true) this function does NOT need to make a trip to the database again.
     * Also this has to be called only once per document instance.
     *
     * @return array
     */
    public function fetchAllRevisions()
    {
        if(!is_array($this->_revs_info)) {
            $doc = $this->getDatabase()->docOpen($this->getId(), array('revs_info' => true));
            $this->_revs_info = $doc->getAllRevisions();
            unset($doc);
        }

        return $this->_revs_info;
    }

    /**
     * Return the document revisions list
     *
     * @throws Phly_Couch_Exception
     * @return array
     */
    public function getAllRevisions()
    {
        if($this->_revs_info === null) {
            throw new Phly_Couch_Exception(sprintf("Revisions of the document '%s' have not been fetched.", $this->getId()));
        }
        return $this->_revs_info;
    }

    /**
     * Return array of document data
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_data;
    }

    /**
     * Populate document data from an array.
     *
     * If data has already been set an array_merge strategy will be used.
     * To delete specific fields of an document use unset($document->fieldName);
     * and save then.
     *
     * @param array $array
     * @return Phly_Couch_Document
     */
    public function fromArray(array $array)
    {
        // Take care of the revisions history
        if(isset($array["revs"])) {
            foreach((array)$array["revs"] AS $revision) {
                $this->_revs_info[] = array('rev' => $revision, 'status' => 'unknown');
            }
            unset($array["revs"]);
        }
        if(isset($array["revs_info"])) {
            $this->_revs_info = $array["revs_info"];
            unset($array["revs_info"]);
        }

        $this->_data = array_merge($this->_data, $array);
        return $this;
    }

    /**
     * Return JSON representation of the document
     *
     * @return string
     */
    public function toJson()
    {
        require_once 'Zend/Json.php';
        return Zend_Json::encode($this->toArray());
    }

    /**
     * Populate document data from a JSON string.
     *
     * @see   toArray()
     * @param string $json
     * @return Phly_Couch_Document
     */
    public function fromJson($json)
    {
        return $this->fromArray(Zend_Json::decode($json));
    }

    /**
     * Get document data field
     *
     * @param string $name
     * @return string|array|integer
     */
    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->_data[$name];
        }
        return null;
    }

    /**
     * Set a document data field
     *
     * @param string               $name
     * @param string|array|integer $value
     */
    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    /**
     * Check if a specific field is set in this document.
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_data);
    }

    /**
     * Unset a document field
     *
     * @param string $name
     */
    public function __unset($name)
    {
        if (isset($this->$name)) {
            unset($this->_data[$name]);
        }
    }

    /**
     * Save this document into persistence of CouchDB.
     *
     * This only works if the document has been given a database connection.
     *
     * @throws Phly_Couch_Exception
     * @see    setDatabase()
     * @return Phly_Couch_Response
     */
    public function save()
    {
        return $this->getDatabase()->docSave($this, $this->getId());
    }

    /**
     * Remove this document from CouchDB.
     *
     * @return Phly_Couch_Response
     */
    public function remove()
    {
        return $this->getDatabase()->docRemove($this);
    }
}
