<?php
/**
 * Phly - PHp LibrarY
 * 
 * @category   Phly
 * @package    Phly
 * @subpackage Test
 * @copyright  Copyright (C) 2008 - Present, Matthew Weier O'Phinney
 * @author     Matthew Weier O'Phinney <mweierophinney@gmail.com> 
 * @license    New BSD {@link http://www.opensource.org/licenses/bsd-license.php}
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Phly_AllTests::main');
}

/**
 * Test helper
 */
require_once dirname(__FILE__) . '/../TestHelper.php';

require_once 'Phly/PubSubTest.php';
require_once 'Phly/PubSub/HandleTest.php';
require_once 'Phly/PubSub/ProviderTest.php';

/**
 * @category   Phly
 * @package    Phly
 * @subpackage Test
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    New BSD {@link http://www.opensource.org/licenses/bsd-license.php}
 */
class Phly_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Phly - All Tests');

        $suite->addTestSuite('Phly_PubSubTest');
        $suite->addTestSuite('Phly_PubSub_ProviderTest');
        $suite->addTestSuite('Phly_PubSub_HandleTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Phly_AllTests::main') {
    Phly_AllTests::main();
}
