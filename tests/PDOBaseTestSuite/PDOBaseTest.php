<?php
    namespace gooddaykya\tests\PDOBaseTestSuite;


    use gooddaykya\components\PDOBase;

    class PDOBaseTest extends \PHPUnit\Framework\TestCase
    {
        private static $db = null;

        public static function setUpBeforeClass()
        {
            $requisites = require_once __DIR__ . './../../credentials.php';
            self::$db = new PDOBase($requisites);
        }

        public function testObjIsInstanceOfPDOBase()
        {
            $this->assertInstanceOf(PDOBase::class, self::$db);
        }
    }