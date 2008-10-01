<?php

require_once "PHPUnit/Framework.php";

$libraryPath = dirname(__FILE__)."/../library/";
set_include_path($libraryPath.":".get_include_path());

require "Zend/Loader.php";
Zend_Loader::registerAutoload();

class CouchTest extends PHPUnit_Framework_TestCase
{
    private $_connection;

    private $_database;

    public function setUp()
    {
        $this->_connection = new Phly_Couch_Connection(array('host' => 'localhost', 'port' => '5984'));
        try {
            $this->_connection->dbCreate("couchunittest");
        } catch(Phly_Couch_Exception $e) {
            $this->_connection->dbDrop("couchunittest");
            $this->_connection->dbCreate("couchunittest");
        }
        $this->_database = new Phly_Couch(array("db" => "couchunittest", 'connection' => $this->_connection));
    }

    public function testSetDbName()
    {
        $this->assertEquals("couchunittest", $this->_database->getDb());
    }

    public function testListDb()
    {
        $result = $this->_connection->allDbs();
        $this->assertTrue($result instanceof Phly_Couch_Result);
        $this->assertContains('couchunittest', $result->getInfo());
    }

    public function testAllDocs()
    {
        $document = new Phly_Couch_Document(array('foo' => 'bar', 'baz' => 'test'));
        $this->_database->docSave($document);

        $document = new Phly_Couch_Document(array('bar' => 'foo', 'test' => 'baz'));
        $this->_database->docSave($document);

        $result = $this->_database->allDocs();
        $this->assertTrue($result instanceof Phly_Couch_View);

        $result2 = $this->_database->getView('_all_docs');
        $this->assertTrue($result2 instanceof Phly_Couch_View);

        $this->assertEquals($result->toArray(), $result2->toArray());
        $this->assertEquals(2, count($result));
        $this->assertEquals(2, count($result2));

        foreach($result AS $viewRow) {
            $this->assertTrue($viewRow instanceof Phly_Couch_ViewRow);
            $doc = $viewRow->getDocument();
            $this->assertTrue($doc instanceof Phly_Couch_Document);
            $docJson = $doc->toJson();
            $this->assertContains("foo", $docJson);
            $this->assertContains("bar", $docJson);
            $this->assertContains("baz", $docJson);
            $this->assertContains("test", $docJson);
        }
    }

    public function testModifyDoc()
    {
        $document = new Phly_Couch_Document(array('_id' => 'testId', 'foo' => 'bar', 'baz' => 'test'), $this->_database);
        $document->save();

        $document = $this->_database->docOpen('testId');
        $data = $document->toArray();
        unset($data['_rev']);
        $this->assertEquals(array('_id' => 'testId', 'foo' => 'bar', 'baz' => 'test'), $data);

        $document->subject = "hello World!";
        $document->save();

        $document = $this->_database->docOpen('testId');
        $data = $document->toArray();
        unset($data['_rev']);
        $this->assertEquals(array('_id' => 'testId', 'foo' => 'bar', 'baz' => 'test', 'subject' => 'hello World!'), $data);
    }

    public function tearDown()
    {
        $this->_connection->dbDrop("couchunittest");
    }
}