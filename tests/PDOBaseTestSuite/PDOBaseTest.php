<?php
    namespace gooddaykya\tests\PDOBaseTestSuite;


    use gooddaykya\components\PDOBase;

    class PDOBaseTest extends \PHPUnit\Framework\TestCase
    {
        private static $credentials = null;
        private $db;

        public static function setUpBeforeClass()
        {
            self::$credentials = require __DIR__ . './../../credentials.php';
        }

        public function setUp()
        {
            $this->db = new PDOBase(self::$credentials);
        }

        public function testObjIsInstanceOfPDOBase()
        {
            $this->assertInstanceOf(PDOBase::class, $this->db);
        }

        public function testWrongCredentials()
        {
            $this->expectException(\PDOException::class);
            $falseCreds = array_merge(array(), self::$credentials);
            $falseCreds['user'] .= 'wrong';

            $this->expectException(\PDOException::class);
            $db = new PDOBase($falseCreds);
        }
    }
