<?php

class Phly_Couch_View implements Iterator, Countable
{
    protected $_viewUri;

    protected $_designDocument = null;

    protected $_fetchedView = false;

    protected $_rows = array();

    protected $_count = null;

    protected $_database = null;

    /**
     * Create a new view object that acts as an iterator over all documents.
     *
     * To fetch the rows that should be iterated over {@see query()} is used.
     * If no query was fired, all documents of the view are iterated over.
     *
     * @param unknown_type $viewUri
     * @param Phly_Couch $database
     */
    public function __construct($viewUri, Phly_Couch $database)
    {
        if($viewUri !== "_all_docs" && !strpos($viewUri, "_design/")) {
            $viewUri = "_design/".$viewUri;
        }
        $this->_viewUri = $viewUri;

        if($viewUri !== "_all_docs") {
            $parts = explode("/", $viewUri);
            $this->_designDocument = "_design/".$parts[1];
        }

        if($database !== null) {
            $this->setDatabase($database);
        }
    }

    public function setDatabase(Phly_Couch $database)
    {
        $this->_database = $database;
        return $this;
    }

    public function getDatabase()
    {
        if($this->_database === null) {
            throw new Phly_Couch_Exception("Database is not set in view.");
        }
        return $this->_database;
    }

    public function getDesignDocumentName()
    {
        return $this->_designDocument;
    }

    /**
     * Return design document that defined this view
     *
     * @return Phly_Couch_Document
     */
    public function fetchDesignDocument()
    {
        if($this->_designDocument === null) {
            throw new Phly_Couch_Exception(sprintf("View '%s' has no design document.", $this->_viewUri));
        }
        return $this->_database->docOpen($this->_designDocument);
    }

    /**
     * Query View for results. Use Params for sorting and other options.
     *
     * @param array $params
     * @throws Phly_Couch_Exception
     * @return Phly_Couch_View
     */
    public function query(array $queryParams=array())
    {
        $response = $this->_prepareAndSend($queryParams);
        $body = Zend_Json::decode($response->getBody());

        if(isset($body['total_rows']) && isset($body['rows']) && isset($body['offset'])) {
            $this->_count = $body['total_rows'];
            $this->_rows = $body['rows'];
            $this->_offset = $body['offset'];
            $this->_fetchedView = true;
        } else {
            throw new Phly_Couch_Exception("CouchDb Response is not a valid view result.");
        }
        return $this;
    }

    /**
     * Couch Views return a key field that is primarily designed for sorting by.
     *
     * @return boolean
     */
    public function sortByKey()
    {
        // TODO: Sort View Rows by Couch returned key names
    }

    /**
     * Return the complete View Json Response as an array
     *
     * @return array
     */
    public function toArray()
    {
        if($this->_fetchedView === false) {
            $this->query(array());
        }

        return array('total_rows' => $this->_count, 'offset' => $this->_offset, 'rows' => $this->_rows);
    }

    /**
     * Return number of view results
     *
     * @return int
     */
    public function count()
    {
        if($this->_fetchedView === false) {
            $this->query(array());
        }

        return count($this->_rows);
    }

    public function getTotalDocumentCount()
    {
        return $this->_count;
    }

    public function current()
    {
        if($this->_fetchedView === false) {
            $this->query(array());
        }

        $doc = current($this->_rows);
        if($doc == false) {
            return false;
        } else {
            // TODO: Maybe check for viewrow instance and replace instead of creating new all over?
            return new Phly_Couch_ViewRow($doc, $this->_database);
        }
    }

    public function key()
    {
        if($this->_fetchedView === false) {
            $this->query(array());
        }

        return key($this->_rows);
    }

    public function next()
    {
        if($this->_fetchedView === false) {
            $this->query(array());
        }

        return next($this->_rows);
    }

    public function rewind()
    {
        if($this->_fetchedView === false) {
            $this->query(array());
        }

        return reset($this->_rows);
    }

    public function valid()
    {
        if($this->_fetchedView === false) {
            $this->query(array());
        }

        return $this->current() !== false;
    }

    /**
     * Prepare the URI and send the request
     *
     * @param  string $path
     * @param  string $method
     * @param  null|array $queryParams
     * @return Zend_Http_Response
     */
    protected function _prepareAndSend($queryParams = null)
    {
        $path = $this->_database->getDb() . '/' . $this->_viewUri;
        return $this->_database->getConnection()->send($path, 'GET', $queryParams);
    }
}