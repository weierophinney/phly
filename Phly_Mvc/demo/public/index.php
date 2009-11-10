<?php
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application')); 
set_include_path(implode(PATH_SEPARATOR, array(
    '.',
    dirname(__FILE__) . '/../library',
    '/home/matthew/.local/lib/php/ZendFramework/library',
)));

$autoloader = function($classname) 
{
    $namespace = '';
    $filename  = '';
    if (strstr($classname, '\\')) {
        $namespace = substr($classname, 0, strrpos($classname, '\\'));
        $classname = substr($classname, strrpos($classname, '\\') + 1);
        $filename  = str_replace('\\', '/', $namespace) . '/';
    }
    $filename .= str_replace('_', '/', $classname) . '.php';
    return include_once($filename);
};
spl_autoload_register($autoloader);

$app = new Zend_Application('development', array(
    'bootstrap' => array(
        'path'  => APPLICATION_PATH . '/Bootstrap.php',
        'class' => 'application\Bootstrap',
    ),
));
$app->bootstrap()
    ->run();
