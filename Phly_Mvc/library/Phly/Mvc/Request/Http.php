<?php

/**
 * HTTP request environment
 *
 * Portions verbatim or based on Zend_Controller_Request_Http, 
 * copyright (c) 2005-2008  Zend Technologies USA Inc. (http://www.zend.com).
 * 
 * @uses       Phly_Mvc_Request_Request
 * @package    Phly_Mvc
 * @subpackage Request
 * @copyright  Matthew Weier O'Phinney <mweierophinney@gmail.com> 
 * @author     Matthew Weier O'Phinney <mweierophinney@gmail.com> 
 * @license    New BSD {@link http://framework.zend.com/license/new-bsd}
 * @version    $Id: $
 */
class Phly_Mvc_Request_Http extends Phly_Mvc_Request_Request
{
    /**
     * Scheme for http
     */
    const SCHEME_HTTP  = 'http';

    /**
     * Scheme for https
     */
    const SCHEME_HTTPS = 'https';

    protected $_baseUrl;
    protected $_cookie;
    protected $_get;
    protected $_pathInfo;
    protected $_post;
    protected $_requestUri;

    public function setCookie($data)
    {
        return $this->_setSource('cookie', $data);
    }

    public function getCookie($name = null, $default = null)
    {
        return $this->_getSource('cookie', $name, $default);
    }

    public function setPost($data)
    {
        return $this->_setSource('post', $data);
    }

    public function getPost($name = null, $default = null)
    {
        return $this->_getSource('post', $name, $default);
    }

    public function setQuery($data)
    {
        return $this->_setSource('get', $data);
    }

    public function getQuery($name = null, $default = null)
    {
        return $this->_getSource('get', $name, $default);
    }

    public function setRequestUri($uri)
    {
        $this->_requestUri = (string) $uri;
        return $this;
    }

    public function getRequestUri()
    {
        if (null === $this->_requestUri) {
            // logic for retrieving request URI
            if (!$requestUri = $this->getServer('HTTP_X_REWRITE_URL', false)) {
                if (!$requestUri = $this->getServer('REQUEST_URI', false)) {
                    if (!$requestUri = $this->getServer('ORIG_PATH_INFO', false)) {
                        // Cannot determine from environment
                        return null;
                    }
                    if ($query = $this->getServer('QUERY_STRING', false)) {
                        // Appending query string
                        $requestUri .= '?' . $query;
                    }
                } else {
                    // request_uri used
                    $pathInfo    = parse_url($requestUri, PHP_URL_PATH);
                    $queryString = parse_url($requestUri, PHP_URL_QUERY);
                    $requestUri  = $pathInfo
                                 . ((empty($queryString)) ? '' : '?' . $queryString);
                }
            }
            $this->setRequestUri($requestUri);
        }

        return $this->_requestUri;
    }

    public function setBaseUrl($baseUrl)
    {
        $this->_baseUrl = rtrim((string) $baseUrl, '/');
        return $this;
    }

    public function getBaseUrl()
    {
        if (null === $this->_baseUrl) {
            $filename = $this->getServer('SCRIPT_FILENAME', '');

            $scriptName = $this->getServer('SCRIPT_NAME', false);
            if ($scriptName && basename($scriptName) === $filename) {
                $baseUrl = $scriptName;
            } else {
                $phpSelf = $this->getServer('PHP_SELF', false);
                if ($phpSelf && basename($phpSelf) === $filename) {
                    $baseUrl = $phpSelf;
                } else {
                    $origScriptName = $this->getServer('ORIG_SCRIPT_NAME', false);
                    if ($origScriptName && basename($origScriptName) === $filename) {
                        $baseUrl = $origScriptName;
                    } else {
                        $path    = ($phpSelf) ? $phpSelf : '';
                        $file    = $filename;
                        $segs    = explode('/', trim($file, '/'));
                        $segs    = array_reverse($segs);
                        $index   = 0;
                        $last    = count($segs);
                        $baseUrl = '';
                        do {
                            $seg     = $segs[$index];
                            $baseUrl = '/' . $seg . $baseUrl;
                            ++$index;
                        } while (($last > $index) && (false !== ($pos = strpos($path, $baseUrl))) && (0 != $pos));
                    }
                }
            }

            // Does the baseUrl have anything in common with the request_uri?
            $requestUri = $this->getRequestUri();

            if (empty($requestUri)) {
                // do nothing if request URI is empty...
            } elseif (0 === strpos($requestUri, $baseUrl)) {
                // full $baseUrl matches
                $this->setBaseUrl($baseUrl);
            } elseif (0 === strpos($requestUri, dirname($baseUrl))) {
                // directory portion of $baseUrl matches
                $this->setBaseUrl(dirname($baseUrl));
            } elseif (!strpos($requestUri, basename($baseUrl))) {
                // no match whatsoever; set it blank
                $this->setBaseUrl('');
            } elseif ((strlen($requestUri) >= strlen($baseUrl))
                && ((false !== ($pos = strpos($requestUri, $baseUrl))) 
                    && ($pos !== 0))
            ) {
                // If using mod_rewrite or ISAPI_Rewrite strip the script filename
                // out of baseUrl. $pos !== 0 makes sure it is not matching a value
                // from PATH_INFO or QUERY_STRING
                $this->setBaseUrl(substr($requestUri, 0, $pos + strlen($baseUrl)));
            }
        }

        return $this->_baseUrl;
    }

    public function setPathInfo($path)
    {
        $this->_pathInfo = (string) $path;
        return $this;
    }

    public function getPathInfo()
    {
        if (null === $this->_pathInfo) {
            if (null === ($requestUri = $this->getRequestUri())) {
                return null;
            }

            $pathInfo = parse_url($requestUri, PHP_PATH_INFO);
            $baseUrl  = $this->getBaseUrl();

            if (null !== $baseUrl) {
                if (false !== ($matches = substr($requestUri, strlen($baseUrl)))) {
                    $pathInfo = $matches;
                }
            }

            $this->setPathInfo($pathInfo);
        }

        return $this->_pathInfo;
    }

    /**
     * Return the method by which the request was made
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getServer('REQUEST_METHOD');
    }

    /**
     * Was the request made by POST?
     *
     * @return boolean
     */
    public function isPost()
    {
        if ('POST' == $this->getMethod()) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by GET?
     *
     * @return boolean
     */
    public function isGet()
    {
        if ('GET' == $this->getMethod()) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by PUT?
     *
     * @return boolean
     */
    public function isPut()
    {
        if ('PUT' == $this->getMethod()) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by DELETE?
     *
     * @return boolean
     */
    public function isDelete()
    {
        if ('DELETE' == $this->getMethod()) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by HEAD?
     *
     * @return boolean
     */
    public function isHead()
    {
        if ('HEAD' == $this->getMethod()) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by OPTIONS?
     *
     * @return boolean
     */
    public function isOptions()
    {
        if ('OPTIONS' == $this->getMethod()) {
            return true;
        }

        return false;
    }

    /**
     * Is the request a Javascript XMLHttpRequest?
     *
     * Should work with Prototype/Script.aculo.us, possibly others.
     *
     * @return boolean
     */
    public function isXmlHttpRequest()
    {
        return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
    }

    /**
     * Is this a Flash request?
     *
     * @return bool
     */
    public function isFlashRequest()
    {
        $header = strtolower($this->getHeader('USER_AGENT'));
        return (strstr($header, ' flash')) ? true : false;
    }

    /**
     * Is https secure request
     *
     * @return boolean
     */
    public function isSecure()
    {
        return ($this->getScheme() === self::SCHEME_HTTPS);
    }

    /**
     * Return the raw body of the request, if present
     *
     * @return string|false Raw body, or false if not present
     */
    public function getRawBody()
    {
        $body = file_get_contents('php://input');

        if (strlen(trim($body)) > 0) {
            return $body;
        }

        return false;
    }

    /**
     * Return the value of the given HTTP header. Pass the header name as the
     * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
     * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
     *
     * @param string $header HTTP header name
     * @return string|false HTTP header value, or false if not found
     * @throws Zend_Controller_Request_Exception
     */
    public function getHeader($header)
    {
        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (false !== ($value = $this->getServer($temp, false))) {
            return $value;
        }

        // This seems to be the only way to get the Authorization header on
        // Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$header])) {
                return $headers[$header];
            }
        }

        return false;
    }

    /**
     * Get the request URI scheme
     *
     * @return string
     */
    public function getScheme()
    {
        return ($this->getServer('HTTPS') == 'on') ? self::SCHEME_HTTPS : self::SCHEME_HTTP;
    }

    /**
     * Get the HTTP host.
     *
     * "Host" ":" host [ ":" port ] ; Section 3.2.2
     * Note the HTTP Host header is not the same as the URI host.
     * It includes the port while the URI host doesn't.
     *
     * @return string
     */
    public function getHttpHost()
    {
        $host = $this->getServer('HTTP_HOST');
        if (!empty($host)) {
            return $host;
        }

        $scheme = $this->getScheme();
        $name   = $this->getServer('SERVER_NAME');
        $port   = $this->getServer('SERVER_PORT');

        if (($scheme == self::SCHEME_HTTP && $port == 80) || ($scheme == self::SCHEME_HTTPS && $port == 443)) {
            return $name;
        } else {
            return $name . ':' . $port;
        }
    }
}
