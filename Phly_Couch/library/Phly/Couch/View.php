<?php

class Phly_Couch_View extends Phly_Couch_Element implements Iterator, Countable
{
    /**
     * Uri of the view including the _design part and design document name
     *
     * @var string
     */
    protected $_viewUri;

    /**
     * Design document that this view belongs to.
     *
     * @var Phly_Couch_DesignDocument
     */
    protected $_designDocument;

    /**
     * Name of the design document this view belongs to.
     *
     * @var string
     */
    protected $_designDocumentName;

    /**
     * Results of the View are lazy loaded. This is the boolean indicator.
     *
     * @var boolean
     */
    protected $_fetchedView = false;

    /**
     * View result rows
     *
     * @var array
     */
    protected $_rows = array();

    /**
     * Total Rows indicator of a view result.
     *
     * @var integer
     */
    protected $_count = null;

    /**
     * Offset indicator of a view result.
     *
     * @var unknown_type
     */
    protected $_offset = 0;

    /**
     * Create a new view object that acts as an iterator over all documents.
     *
     * To fetch the rows that should be iterated over {@see query()} is used.
     * If no query was fired, all documents of the view are iterated over.
     *
     * @param unknown_type $viewUri
     * @param Phly_Couch $database
     */
    public function __construct($viewUri, $database)
    {
        if($viewUri !== "_all_docs" && !strpos($viewUri, "_design/")) {
            $viewUri = "_design/".$viewUri;
        }
        $this->_viewUri = $viewUri;

        if($viewUri !== "_all_docs") {
            $parts = explode("/", $viewUri);
            $this->_designDocumentName = "_design/".$parts[1];
        }

        if($database instanceof Phly_Couch) {
            $this->setDatabase($database);
        }
    }

    /**
     * Return name of the corresponding designDocument of this view.
     *
     * @return string
     */
    public function getDesignDocumentName()
    {
        return $this->_designDocumentName;
    }

    /**
     * Return design document that defined this view.
     *
     * @throws Phly_Couch_Exception - When view has no design document, for example _all_docs
     * @return Phly_Couch_DesignDocument
     */
    public function fetchDesignDocument()
    {
        if($this->_designDocument === null) {
            if($this->getDesignDocumentName() === null) {
                throw new Phly_Couch_Exception(sprintf("View '%s' has no design document.", $this->_viewUri));
            }
            $this->_designDocument = $this->_database->docOpen($this->getDesignDocumentName());
        }
        return $this->_designDocument;
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
        $body = $response->getBody();

        // only common thing between views and temp views is the rows key.
        if(isset($body['rows'])) {
            $this->_fetchedView = true;
            $this->_rows = $body['rows'];
            if(isset($body['offset'])) {
                $this->_offset = $body['offset'];
            }
            if(isset($body['total_rows'])) {
                $this->_count = $body['total_rows'];
            }
        } else {
            throw new Phly_Couch_Exception(sprintf("CouchDB Response '%s' is not a valid view result.", $body));
        }
        return $this;
    }

    /**
     * Return the complete View Json Response as an array
     *
     * @return array
     */
    public function toArray()
    {
        if($this->_fetchedView === false) {
            $this->query();
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
            $this->query();
        }

        return count($this->_rows);
    }

    /**
     * Return the total count of documents this view contains.
     *
     * @return int
     */
    public function getTotalDocumentCount()
    {
        return $this->_count;
    }

    /**
     * Return current element of view
     *
     * @return Phly_Couch_ViewRow|boolean
     */
    public function current()
    {
        if($this->_fetchedView === false) {
            $this->query();
        }

        $doc = current($this->_rows);
        if($doc == false) {
            return false;
        } else {
            // TODO: Maybe check for viewrow instance and replace instead of creating new all over?
            return new Phly_Couch_ViewRow($doc, $this->_database);
        }
    }

    /**
     * Return key of the current element
     *
     * @return integer
     */
    public function key()
    {
        if($this->_fetchedView === false) {
            $this->query();
        }

        return key($this->_rows);
    }

    /**
     * Advance to next element of view
     *
     * @return mixed
     */
    public function next()
    {
        if($this->_fetchedView === false) {
            $this->query();
        }

        return next($this->_rows);
    }

    /**
     * Reset view results
     *
     * @return mixed
     */
    public function rewind()
    {
        if($this->_fetchedView === false) {
            $this->query();
        }

        return reset($this->_rows);
    }

    /**
     * Check if there is still a valid result in the view
     *
     * @return boolean
     */
    public function valid()
    {
        if($this->_fetchedView === false) {
            $this->query();
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