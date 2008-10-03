<?php

class Phly_Couch_Connection
{
    const CRLF = "\r\n";

    /**
     * @var Phly_Couch_Connection
     */
    protected static $_defaultConnection;

    protected $_lastRequest;

    protected $_lastResponse;

    /**
     * @var string Database host; defaults to 127.0.0.1
     */
    protected $_host = '127.0.0.1';

    /**
     * @var int Database host port; defaults to 5984
     */
    protected $_port = 5984;

    public function __construct($options, $isDefault = true)
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

        if($isDefault === true) {
            self::setDefaultConnection($this);
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
     * @return Phly_Couch_Response
     */
    public function serverInfo()
    {
        $this->_prepareUri('');
        $response = $this->_prepareAndSend('', 'GET');
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed retrieving server information; received response code "%s"', (string) $response->getStatus()));
        }

        return $response;
    }

    /**
     * Get list of all databases
     *
     * @return Phly_Couch_Response
     */
    public function fetchAllDatabases()
    {
        $response = $this->_prepareAndSend('_all_dbs', 'GET');
        if (!$response->isSuccessful()) {
            require_once 'Phly/Couch/Exception.php';
            throw new Phly_Couch_Exception(sprintf('Failed retrieving database list; received response code "%s"', (string) $response->getStatus()));
        }
        return $response;
    }

    /**
     * Create database
     *
     * @param  string $db
     * @return Phly_Couch_Response
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
        return $response;
    }

    /**
     * Drop database
     *
     * @param  string $db
     * @return Phly_Couch_Response
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
        return $response;
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

    /**
     * Send Request to CouchDB
     *
     * @param string      $path
     * @param string      $method
     * @param null|array  $queryParams
     * @param null|string $rawData
     * @return Phly_Couch_Response
     */
    public function send($path, $method, $queryParams=null, $rawData=null)
    {
        return $this->_prepareAndSend($path, $method, $queryParams, $rawData);
    }

    /**
     * From Url, method and data build the raw request.
     *
     * @param  string      $url
     * @param  string      $method
     * @param  null|string $data
     * @return string      $request
     */
    protected function _buildRawRequest($url, $method, $data=null)
    {
        $method = strtoupper($method);

        $request = $method . ' ' . $url . ' HTTP/1.0' . self::CRLF;

        $date = new DateTime();
        $request .= 'Date: ' . $date->format('r') . self::CRLF;

        if($data !== null) {
            $request .= 'Content-Length: ' . strlen($data) . self::CRLF;
            $request .= 'Content-Type: application/json' . self::CRLF . self::CRLF;
            $request .= $data;
        }

        $request .= self::CRLF;

        return $request;
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
    protected function _prepareAndSend($path, $method, array $queryParams = null, $rawData = null)
    {
        // Build Request
        if(strpos($path, '/') !== 0) {
            $path = '/' . $path;
        }

        if(count($queryParams) > 0) {
            $queryString = array();
            foreach($queryParams AS $k => $v) {
                if(is_bool($v)) {
                    $v = ($v)?"true":"false";
                } else {
                    $v = Zend_Json::encode($v);
                }
                $queryString[] = $k . '=' .$v;
            }
            $path = $path . '?' . implode("&", $queryString);
        }

        $request = $this->_buildRawRequest($path, $method, $rawData);

        $errorString = '';
        $errorNumber = '';
        $response    = '';

        $socket = fsockopen($this->_host, $this->_port, $errorNumber, $errorString);

        if (!$socket) {
            require_once 'Phly/Couch/Exception.php';

            throw new Phly_Couch_Exception('Failed to open connection to ' . $this->_host . ':' .
                                           $this->_port . ' (Error number ' . $errorNumber . ': ' .
                                           $errorString . ')');
        }

        fwrite($socket, $request);

        while (!feof($socket)) {
            $response .= fgets($socket);
        }

        fclose($socket);

        $socket = null;

        $response = new Phly_Couch_Response($response);
        $this->_lastRequest = $request;
        $this->_lastResponse = $response;

        // Throw detailed exception on all unsuccessful queries.
        if(!$response->isSuccessful()) {
            $body = $response->getBody();
            $errorMessage = "";
            $reason = "";
            if(isset($body['error']) && isset($body['reason'])) {
                $errorMessage = $body['error'];
                $reason = $body['reason'];
            }

            require "Phly/Couch/Connection/Response/Exception.php";
            throw new Phly_Couch_Connection_Response_Exception(
                sprintf('Failed query "%s" (response: "%s") with message: %s - %s', $path, (string) $response->getStatus(), $errorMessage, $reason),
                $response,
                $request
            );
        }

        return $response;
    }

    /**
     * Return last request
     *
     * @return string
     */
    public function getLastRequest()
    {
        return $this->_lastRequest;
    }

    /**
     * Return last response
     *
     * @return Phly_Couch_Response
     */
    public function getLastResponse()
    {
        return $this->_lastResponse;
    }
}