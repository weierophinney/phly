<?php
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__)),
    realpath(dirname(__FILE__) . '/../library'),
    realpath(dirname(__FILE__) . '/../../Phly_PubSub/library'),
    '/home/matthew/git/zf-standard/incubator/library',
    '/home/matthew/git/zf-standard/trunk/library',
    '/usr/local/zend/share/pear',
)));
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
spl_autoload_unregister(array($loader, 'autoload'));
unset($loader);
Zend_Loader_Autoloader::resetInstance();
Zend_Loader_Autoloader::getInstance()->registerNamespace('PHPUnit_')
                                     ->registerNamespace('Horde_')
                                     ->registerNamespace('Phly_');
