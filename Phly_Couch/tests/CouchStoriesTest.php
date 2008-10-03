<?php

require_once "PHPUnit/Framework.php";

$libraryPath = dirname(__FILE__)."/../library/";
set_include_path($libraryPath.":".get_include_path());

require "Zend/Loader.php";
Zend_Loader::registerAutoload();

class CouchStoriesTest extends PHPUnit_Framework_TestCase
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

    public function tearDown()
    {
        $this->_connection->dbDrop("couchunittest");
    }

    /**
     * @see http://www.cmlenz.net/archives/2007/10/couchdb-joins
     */
    public function testBlogWorkflow()
    {
        $database = $this->_database;

        // Step1: Insert two new blog posts into database
        $blogPost1 = $database->docNew(array(
            "_id" => "johnslug",
            "author" => "john",
            "subject" => "Hello World",
            "content" => "My blog post with lot of content...",
            "comments" => array(
                array("author" => "jack", "comment" => "thats great!"),
                array("author" => "eve", "comment" => "thats wrong!"),
            )
        ));
        $database->docSave($blogPost1);

        $blogPost2 = $database->docNew(array(
            "_id" => "jackslug",
            "author" => "jack",
            "subject" => "Greetings",
            "content" => "My blog post with lot of content...",
            "comments" => array(
                array("author" => "john", "comment" => "thats great!"),
                array("author" => "eve", "comment" => "thats wrong!"),
                array("author" => "john", "comment" => "stop complaining!"),
                array("author" => "jack", "comment" => "really!"),
            )
        ));
        $database->docSave($blogPost2);

        // Step2: Iterate over all results of the allDocs view
        $view = $database->fetchAllDocuments();
        $this->assertEquals(2, count($view));
        foreach($view AS $viewRow) {
            $this->assertContains($viewRow->getId(), array('jackslug', 'johnslug'));
        }

        // Step3: Create some results in temorary views: Request all comments of "john"
        $map = "function(doc) { log(doc);
  for (var i in doc.comments) {
    emit(doc.comments[i].author, doc.comments[i].content);
  }
}";
        $tempView = $database->fetchTemporaryView($map, null, array('key' => 'john'));
        $tempView->query();

        $this->assertEquals(3, count($tempView));
    }
}