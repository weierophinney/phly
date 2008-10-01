<?php

class Phly_Couch_Connection
{
    /**
     * @var Phly_Couch_Connection
     */
    protected static $_defaultConnection;

    /**
     * @var Zend_Http_Client HTTP client used for accessing server
     */
    protected $_client;

    /**
     * @var Zend_Http_Client Default HTTP client to use for CouchDB access
     */
    protected static $_defaultClient;

    /**
     * @var string Database host; defaults to 127.0.0.1
     */
    protected $_host = '127.0.0.1';

    /**
     * @var int Database host port; defaults to 5984
     */
    protected $_port = 5984;

    public function __construct($options)
    {
        if($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if(isset($options['host'])) {
            $this->setHost($options['host']);
        }

        if(isset($options['port'])) {
            $this->setPort($options['port']);
        }

        if(isset($options['http_client'])) {
            $this->setHttpClient($options['http_client']);
        }
    }

    /**
     * Set database host
     *
     * @param  string $host
     * @return Phly_Couch
     */
    public function setHost($host)
    {
        $this->_host = $host;
        return $this;
    }

    /**
     * Retrieve database host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * Set database host port
     *
     * @param  int $port
     * @return Phly_Couch
     */
    public function setPort($port)
    {
        $this->_port = (int) $port;
        return $this;
    }

    /**
     * Retrieve database host port
     *
     * @return int
     */
    public function getPort()
    {
        return $this->_port;
    }

    // HTTP client

    /**
     * Set HTTP client
     *
     * @param  Zend_Http_Client $client
     * @return Phly_Couch
     */
    public function setHttpClient(Zend_Http_Client $client)
    {
        $this->_client = $client;
        return $this;
    }

    /**
     * Set default HTTP client
     *
     * @param  Zend_Http_Client $client
     * @return void
     */
    public static function setDefaultHttpClient(Zend_Http_Client $client)
    {
        self::$_defaultClient = $client;
    }

    /**
     * Get current HTTP client
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        if (null === $this->_client) {
            $client = self::getDefaultHttpClient();
            if (null === $client) {
                require_once 'Zend/Http/Client.php';
                $client = new Zend_Http_Client;
            }
            $this->setHttpClient($client);
        }
        return $this->_client;
    }

    /**
     * Retrieve default HTTP client
     *
     * @return null|Zend_Http_Client
     */
    public static function getDefaultHttpClient()
    {
        return self::$_defaultClient;
    }

    /**
     * Return default connection
     *
     * @throws Phly_Couch_Exception
     * @return Phly_Couch_Connection
     */
    public static function getDefaultConnection()
    {
        if(!(self::$_defaultConnection instanceof Phly_Couch_Connection)) {
            throw new Phly_Couch_Exception("No default connection given!");
        }

        return self::$_defaultConnection;
    }

    /**
     * Set default connection
     *
     * @param Phly_Couch_Connection $conn
     */
    public static function setDefaultConnection(Phly_Couch_Connection $conn)
    {
        self::$_defaultConnection = $conn;
    }

    // Server API methods

    /**
     * Get server information
     *
     * @return Phly_Couch_Result
     */
    public function serverInfo()
    {
        $this->_prepareUri('');
        $response = $this->_prepareAndSend('', 'GET');
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed retrieving server information; received response code "%s"', (string) $response->getStatus()));
        }

        require_once 'Phly/Couch/Result.php';
        return new Phly_Couch_Result($response);
    }

    /**
     * Get list of all databases
     *
     * @return Phly_Couch_Result
     */
    public function allDbs()
    {
        $response = $this->_prepareAndSend('_all_dbs', 'GET');
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed retrieving database list; received response code "%s"', (string) $response->getStatus()));
        }
        require_once 'Phly/Couch/Result.php';
        return new Phly_Couch_Result($response);
    }

    /**
     * Create database
     *
     * @param  string $db
     * @return Phly_Couch_Result
     * @throws Phly_Couch_Exception when fails or invalid database name
     */
    public function dbCreate($db = null)
    {
        $db = $this->_verifyDb($db);
        $response = $this->_prepareAndSend($db, 'PUT');
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed creating database "%s"; received response code "%s"', $db, (string) $response->getStatus()));
        }
        require_once 'Phly/Couch/Result.php';
        return new Phly_Couch_Result($response);
    }

    /**
     * Drop database
     *
     * @param  string $db
     * @return Phly_Couch_Result
     * @throws Phly_Couch_Exception when fails
     */
    public function dbDrop($db = null)
    {
        $db = $this->_verifyDb($db);
        $response = $this->_prepareAndSend($db, 'DELETE');
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed dropping database "%s"; received response code "%s"', $db, (string) $response->getStatus()));
        }
        require_once 'Phly/Couch/Result.php';
        return new Phly_Couch_Result($response);
    }

    /**
     * Verify database parameter
     *
     * @param  mixed $db
     * @return string
     * @throws Phly_Couch_Exception for invalid database
     */
    protected function _verifyDb($db)
    {
        if (!preg_match('/^[a-z][a-z0-9_$()+-\/]+$/', $db)) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Invalid database specified: "%s"', htmlentities($db)));
        }

        return $db;
    }

    public function send($path, $method, $queryParams)
    {
        return $this->_prepareAndSend($path, $method, $queryParams);
    }

    /**
     * Prepare the URI and send the request
     *
     * @param  string $path
     * @param  string $method
     * @param  null|array $queryParams
     * @return Zend_Http_Response
     */
    protected function _prepareAndSend($path, $method, array $queryParams = null)
    {
        $client = $this->getHttpClient();
        $this->_prepareUri($path, $queryParams);
        $response = $client->request($method);
        $client->resetParameters();
        return $response;
    }

    /**
     * Prepare the URI
     *
     * @param  string $path
     * @param  null|array $queryParams
     * @return void
     */
    protected function _prepareUri($path, array $queryParams = null)
    {
        $client = $this->getHttpClient();
        $uri    = 'http://' . $this->getHost() . ':' . $this->getPort() . '/' . $path;

        $client->setUri($uri);
        if (null !== $queryParams) {
            foreach ($queryParams as $key => $value) {
                if (is_bool($value)) {
                    $queryParams[$key] = ($value) ? 'true' : 'false';
                }
            }
            $client->setParameterGet($queryParams);
        }
    }
}