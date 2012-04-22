<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Services_LibravatarAllTests::main');
}

require_once 'PHPUnit/Autoload.php';

class Services_LibravatarAllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Services_Libravatar tests');
        $suite->addTestFiles(
            array(__DIR__ . '/Services/LibravatarTest.php')
        );

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Services_LibravatarAllTests::main') {
    Services_LibravatarAllTests::main();
}
?>