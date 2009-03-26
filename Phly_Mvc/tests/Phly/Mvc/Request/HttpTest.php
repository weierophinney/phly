<?php

class Phly_Mvc_Request_HttpTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->request = new Phly_Mvc_Request_Http();
    }

    // $_GET

    public function testUsesQuerySuperGlobalByDefault()
    {
        $expected = $_GET;
        $this->assertSame($expected, $this->request->getQuery());
    }

    public function testQuerySuperGlobalIsOverwritten()
    {
        $this->request->getQuery();
        $this->assertTrue(empty($_GET));
    }

    public function testCanSpecifyDataForQuery()
    {
        $data = array('foo' => 'bar');
        $this->request->setQuery($data);
        $this->assertSame($data, $this->request->getQuery());
    }

    public function testCanRetrieveIndividualItemsFromQuery()
    {
        $data = array('foo' => 'bar');
        $this->request->setQuery($data);
        $this->assertEquals('bar', $this->request->getQuery('foo'));
    }

    public function testRetrievingIndividualItemFromQueryUsesPassedDefaultIfItemDoesNotExist()
    {
        $this->assertFalse($this->request->getQuery('foo', false));
    }

    // $_POST

    public function testUsesPostSuperGlobalByDefault()
    {
        $expected = $_POST;
        $this->assertSame($expected, $this->request->getPost());
    }

    public function testPostSuperGlobalIsOverwritten()
    {
        $this->request->getPost();
        $this->assertTrue(empty($_POST));
    }

    public function testCanSpecifyDataForPost()
    {
        $data = array('foo' => 'bar');
        $this->request->setPost($data);
        $this->assertSame($data, $this->request->getPost());
    }

    public function testCanRetrieveIndividualItemsFromPost()
    {
        $data = array('foo' => 'bar');
        $this->request->setPost($data);
        $this->assertEquals('bar', $this->request->getPost('foo'));
    }

    public function testRetrievingIndividualItemFromPostUsesPassedDefaultIfItemDoesNotExist()
    {
        $this->assertFalse($this->request->getPost('foo', false));
    }

    // $_COOKIE

    public function testUsesCookieSuperGlobalByDefault()
    {
        $expected = $_COOKIE;
        $this->assertSame($expected, $this->request->getCookie());
    }

    public function testCookieSuperGlobalIsOverwritten()
    {
        $this->request->getCookie();
        $this->assertTrue(empty($_COOKIE));
    }

    public function testCanSpecifyDataForCookie()
    {
        $data = array('foo' => 'bar');
        $this->request->setCookie($data);
        $this->assertSame($data, $this->request->getCookie());
    }

    public function testCanRetrieveIndividualItemsFromCookie()
    {
        $data = array('foo' => 'bar');
        $this->request->setCookie($data);
        $this->assertEquals('bar', $this->request->getCookie('foo'));
    }

    public function testRetrievingIndividualItemFromCookieUsesPassedDefaultIfItemDoesNotExist()
    {
        $this->assertFalse($this->request->getCookie('foo', false));
    }

    // REQUEST_URI

    public function testUsesXRewriteUrlForRequestUriWhenPresent()
    {
        $this->request->setServer(array('HTTP_X_REWRITE_URL' => '/foo/bar'));
        $this->assertEquals('/foo/bar', $this->request->getRequestUri());
    }

    public function testUsesOrigPathInfoForRequestUriWhenPresent()
    {
        $this->request->setServer(array('ORIG_PATH_INFO' => '/foo/bar'));
        $this->assertEquals('/foo/bar', $this->request->getRequestUri());
    }

    public function testAppendsQueryStringToOrigPathInfoWhenDeterminingRequestUri()
    {
        $this->request->setServer(array(
            'ORIG_PATH_INFO' => '/foo/bar',
            'QUERY_STRING'   => 'foo=bar',
        ));
        $this->assertEquals('/foo/bar?foo=bar', $this->request->getRequestUri());
    }

    public function testUsersRequestUriFromServerForRequestUriWhenPresent()
    {
        $this->request->setServer(array('REQUEST_URI' => '/foo/bar'));
        $this->assertEquals('/foo/bar', $this->request->getRequestUri());
    }

    public function testRequestUriRemainsNullIfNoMatchingHeadersAreFound()
    {
        $this->request->setServer(array());
        $this->assertNull($this->request->getRequestUri());
    }

    // Base URL
    public function testBaseUrlShouldBeNullWhenServerArrayIsEmpty()
    {
        $this->request->setServer(array());
        $this->assertNull($this->request->getBaseUrl());
    }

    public function testBaseUrlShouldBeNullIfRequestUriIsEmpty()
    {
        $data = array(
            'SCRIPT_FILENAME' => 'index.php',
            'SCRIPT_NAME'     => '/foo/bar/index.php',
        );
        $this->request->setServer($data);
        $this->assertNull($this->request->getBaseUrl());
    }

    public function testBaseUrlShouldEqualScriptNameWhenScriptNameAndFileNameAreSameAndMatchStartOfRequestUri()
    {
        $data = array(
            'REQUEST_URI'     => '/foo/bar/index.php/baz/bat',
            'SCRIPT_FILENAME' => 'index.php',
            'SCRIPT_NAME'     => '/foo/bar/index.php',
        );
        $this->request->setServer($data);
        $this->assertEquals($data['SCRIPT_NAME'], $this->request->getBaseUrl());
    }

    public function testBaseUrlShouldEqualScriptNameDirWhenScriptNameAndFileNameAreSameAndMatchDirectoryAtStartOfRequestUri()
    {
        $data = array(
            'REQUEST_URI'     => '/foo/bar/baz/bat',
            'SCRIPT_FILENAME' => 'index.php',
            'SCRIPT_NAME'     => '/foo/bar/index.php',
        );
        $this->request->setServer($data);
        $this->assertEquals(dirname($data['SCRIPT_NAME']), $this->request->getBaseUrl());
    }

    public function testBaseUrlShouldBeEmptyWhenScriptNameAndFileNameAreSameAndDoNotMatchRequestUriAtAll()
    {
        $data = array(
            'REQUEST_URI'     => '/baz/bat',
            'SCRIPT_FILENAME' => 'index.php',
            'SCRIPT_NAME'     => '/foo/bar/index.php',
        );
        $this->request->setServer($data);
        $baseUrl = $this->request->getBaseUrl();
        $this->assertTrue(empty($baseUrl));
    }

    public function testBaseUrlShouldNeverMatchAgainstQueryString()
    {
        $data = array(
            'REQUEST_URI'     => '/baz/bat?rel=/foo/bar/index.php/baz',
            'SCRIPT_FILENAME' => 'index.php',
            'SCRIPT_NAME'     => '/foo/bar/index.php',
        );
        $this->request->setServer($data);
        $baseUrl = $this->request->getBaseUrl();
        $this->assertTrue(empty($baseUrl), $baseUrl);
    }
}
