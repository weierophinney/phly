<?php
require_once 'Zend/Json.php';

class Phly_Couch
{
    /**
     * @var string Database on which operations are performed
     */
    protected $_db;

    /**
     * @var Phly_Couch_Connection
     */
    protected $_connection;

    /**
     * Constructor
     *
     * @param  null|string|array|Zend_Config $info Database name, or array/config of options
     * @return void
     */
    public function __construct($options = null)
    {
        if (null !== $options) {
            if (is_array($options)) {
                $this->setOptions($options);
            } elseif ($options instanceof Zend_Config) {
                $this->setConfig($options);
            } elseif (is_string($info)) {
                $this->setDb($options);
            }
        }
    }

    /**
     * Set connection options
     *
     * @param  array $options
     * @return Phly_Couch
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Set connection options from Zend_Config object
     *
     * @param  Zend_Config $config
     * @return Phly_Couch
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }

    /**
     * Set Database on which to perform operations
     *
     * @param  string $db
     * @return Phly_Couch
     * @throws Phly_Couch_Exception for invalid DB name
     */
    public function setDb($db)
    {
        if (!preg_match('/^[a-z][a-z0-9_$()+-\/]+$/', $db)) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Invalid database specified: "%s"', htmlentities($db)));
        }
        $this->_db = $db;
        return $this;
    }

    /**
     * Retrieve current database name
     *
     * @return string|null
     */
    public function getDb()
    {
        if($this->_db === null) {
            throw new Phly_Couch_Exception("No Database was given!");
        }

        return $this->_db;
    }

    /**
     * Set a connection for this database
     *
     * @param Phly_Couch_Connection|array $connection
     */
    public function setConnection($connection)
    {
        if($connection instanceof Phly_Couch_Connection) {
            $this->_connection = $connection;
        } else if(is_array($connection)) {
            $this->_connection = new Phly_Couch_Connection($connection);
        } else {
            throw new Phly_Couch_Exception("Invalid connection data given for database!");
        }
    }

    /**
     * Get connection of this database
     *
     * @throws Phly_Couch_Exception
     * @return Phly_Couch_Connection
     */
    public function getConnection()
    {
        if($this->_connection === null) {
            $this->_connection = Phly_Couch_Connection::getDefaultConnection();
        }
        return $this->_connection;
    }

    // API METHODS

    // Database API methods

    /**
     * Compact a database
     *
     * @param  null|string $db
     * @r-eturn Phly_Couch_Response
     * @throws Phly_Couch_Exception when fails or no database specified
     */
    public function compact()
    {
        $db = $this->getDb();
        $response = $this->_prepareAndSend($db . '/_compact', 'POST');
        if (202 !== $response->getStatus()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed compacting database "%s": received response code "%s"', $db, (string) $response->getStatus()));
        }
        return $response;
    }

    /**
     * Get database info
     *
     * @param  string $db
     * @return Phly_Couch_Response
     * @throws Phly_Couch_Exception when fails
     */
    public function info()
    {
        $db = $this->getDb();

        return $this->_prepareAndSend($db, 'GET');
    }

    // Document API methods

    /**
     * Retrieve all documents for a give database
     *
     * @param  null|array $options Query options
     * @return Phly_Couch_View
     * @throws Phly_Couch_Exception on failure or bad db
     */
    public function fetchAllDocuments(array $options = null)
    {
        return $this->fetchView('_all_docs');
    }

    /**
     * Get rows from a view
     *
     * @param  string $viewName including the design document name
     * @param  array  $queryParams
     * @return Phly_Couch_View
     */
    public function fetchView($viewName, array $queryParams=array())
    {
        $view = new Phly_Couch_View($viewName, $this);
        if(count($queryParams) > 0) {
            $view->query($queryParams);
        }

        return $view;
    }

    /**
     * Fetch rows from a temporary view given a map (and optionally reduce) strategy.
     *
     * @param string $map
     * @param string $reduce
     * @param array $queryParams
     * @return Phly_Couch_View
     */
    public function fetchTemporaryView($map, $reduce=null, array $queryParams=array())
    {
        $view = new Phly_Couch_TemporaryView($map, $reduce, $this);
        if(count($queryParams) > 0) {
            $view->query($queryParams);
        }

        return $view;
    }

    /**
     * Return a new document with the given data as initials.
     *
     * This document is not saved into the database until you explicitly
     * do that by $document->save(); or $database->docSave($document);
     *
     * @param array $data
     * @return unknown
     */
    public function docNew(array $data=array())
    {
        return new Phly_Couch_Document($data, $this);
    }

    /**
     * Open a document
     *
     * @param  string $id
     * @param  null|array $options
     * @return Phly_Couch_Document
     * @throws Phly_Couch_Exception on failure
     * @todo   handle unsuccessful call
     */
    public function docOpen($id, array $options = null)
    {
        $db = $this->getDb($db);

        $response = $this->_prepareAndSend($db . '/' . $id, 'GET', $options);

        // TODO: What about empty result?

        require_once 'Phly/Couch/Document.php';
        if(strpos($id, '_design/') === 0) {
            return new Phly_Couch_DesignDocument($response->getBody(), $this);
        } else {
            return new Phly_Couch_Document($response->getBody(), $this);
        }
    }

    /**
     * Save a document
     *
     * @param  string|array|Phly_Couch_Document $document
     * @param  null|string $id
     * @return Phly_Couch_Response
     * @throws Phly_Couch_Exception on failure
     */
    public function docSave($document)
    {
        $db     = $this->getDb();
        $path   = $db . '/';

        if (is_string($document)) {
            if ('{' != substr($document, 0, 1)) {
                require_once 'Phly/Couch/Exception.php';
                throw new Phly_Couch_Exception('Invalid document provided');
            }
            require_once 'Phly/Couch/Document.php';
            $document = new Phly_Couch_Document($document);
        } elseif (is_array($document)) {
            require_once 'Phly/Couch/Document.php';
            $document = new Phly_Couch_Document($document);
        } elseif (!$document instanceof Phly_Couch_Document) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception('Invalid document provided');
        }

        $method = 'POST';
        $id = $document->getId();
        if (null !== $id) {
            $method = 'PUT';
            $path  .= $id;
        }

        if (null !== $document->getRevision()) {
            if ((null === $id) && (null === ($id = $document->getId()))) {
                require_once 'Phly/Couch/Exception.php';
                throw new Phly_Couch_Exception('Document updates require a document id; none provided');
            }
            $method = 'PUT';
        }

        try {
            $response = $this->_prepareAndSend($path, $method, null, $document->toJson());
            $status   = $response->getStatus();
        } catch(Phly_Couch_Connection_Response_Exception $e) {
            $status = $e->getHttpResponse()->getStatus();
        }

        switch ($status) {
            case 412:
                require_once 'Phly/Couch/Exception.php';
                throw new Phly_Couch_Exception(sprintf('Document with the specified document id "%s" already exists', $id));
                break;
            case 409:
                require_once 'Phly/Couch/Exception.php';
                throw new Phly_Couch_Exception(sprintf('Document with document id "%s" does not contain the revision "%s"', $id, $data['_rev']));
                break;
            case 201:
            default:
                break;
        }

        if($response instanceof Phly_Couch_Response) {
            $responseData = $response->getBody();
            if(isset($responseData["ok"]) && $responseData["ok"] == "true") {
                $document->setId($responseData["id"]);
                $document->setRevision($responseData["rev"]);
            }
        }
        unset($document);

        return $response;
    }

    /**
     * Remove a document
     *
     * @param  string $id
     * @param  array $options
     * @return Phly_Couch_Response
     * @throws Phly_Couch_Exception on failed call
     */
    public function docRemove($document, array $options = null)
    {
        if(!($document instanceof Phly_Couch_Document)) {
            throw new Phly_Couch_Exception("Given parameter in docRemove() is not of the type document.");
        }

        $db = $this->getDb();

        $path = $db . '/' . $document->getId() . '?rev='.$document->getRevision();
        try {
            $response = $this->_prepareAndSend($path, 'DELETE', $options);
            // TODO: Somehow mark document instance as deleted, maybe reset id and revision or throw exceptions on further usage?
        } catch(Phly_Couch_Connection_Response_Exception $e) {
            throw new Phly_Couch_Exception(sprintf('Failed deleting document with id "%s" from database "%s"; received response code "%s"', $id, $db, (string) $e->getHttpResponse()->getStatus()));
        }

        return $response;
    }

    /**
     * Bulk save many documents at once
     *
     * @param  array|Phly_Couch_DocumentSet $documents
     * @param  array $options
     * @return Phly_Couch_Response
     * @throws Phly_Couch_Exception on failed save
     */
    public function docBulkSave($documents, array $options = null)
    {
        $db = $this->getDb($db);

        if (is_array($documents)) {
            require_once 'Phly/Couch/DocumentSet.php';
            $documents = new Phly_Couch_DocumentSet($documents);
        } elseif (!$documents instanceof Phly_Couch_DocumentSet) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception('Invalid document set provided to bulk save operation');
        }

        $response = $this->_prepareAndSend($db . '/_bulk_docs', 'POST', $options, $documents->toJson());
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed deleting document with id "%s" from database "%s"; received response code "%s"', $id, $db, (string) $response->getStatus()));
        }

        return $response;
    }

    /**
     * Prepare the URI and send the request
     *
     * @param  string $path
     * @param  string $method
     * @param  null|array $queryParams
     * @param  null|string $rawData
     * @return Zend_Http_Response
     */
    protected function _prepareAndSend($path, $method, array $queryParams = null, $rawData=null)
    {
        return $this->getConnection()->send($path, $method, $queryParams, $rawData);
    }
}
