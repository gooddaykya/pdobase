<?php
    namespace gooddaykya\tests\PDOBaseTestSuite;


    use gooddaykya\components\PDOBase;

    class PDOBaseQueryTest extends \PHPUnit\Framework\TestCase
    {
        private static $db;

        public static function setUpBeforeClass()
        {
            $credentials = require __DIR__ . './../../credentials.php';
            self::$db = new PDOBase($credentials);
        }

        public function testSelectRowByIdFromTable()
        {
            $request = 'SELECT val FROM const_table WHERE id = :id';
            $params = array(
                ':id' => 5
            );

            $lazy = self::$db->execQuery($request, $params);
            $result = $lazy('fetch')->val;

            $this->assertEquals(13, $result);
            $this->assertFalse($result != 13);
        }

        public function testUndefinedPdoStatement()
        {
            $request = 'SELECT val FROM const_table WHERE id = :id';
            $params = array(
                ':id' => 5
            );

            $lazy = self::$db->execQuery($request, $params);
            $this->expectException(\PDOException::class);
            $lazy('undefined_method');
        }

        public function testIncorrectSqlSyntax()
        {
            $request = 'S_LECT * FROM const_table';
            
            $this->expectException(\PDOException::class);
            $lazy = self::$db->execQuery($request);
            $result = $lazy('fetchAll');
        }
    }