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
            $request = 'SELECT * FROM const_table';

            $lazy = self::$db->execQuery($request);
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

        public function testCorrectTransaction()
        {
            $request1 = 'INSERT INTO main_table (val) VALUES (15)';
            $request2 = 'INSERT INTO dep_table (id, val) VALUES (LAST_INSERT_ID(), LAST_INSERT_ID())';

            self::$db->beginTransaction();

            $lazy1 = self::$db->execQuery($request1);
            $mainId = $lazy1('lastInsertId');

            $lazy2 = self::$db->execQuery($request2);
            $depRowsAffected = $lazy2('rowCount');

            self::$db->commit();


            $check = 'SELECT val FROM dep_table WHERE id = :id';
            $params = array(
                'id' => $mainId
            );

            $lazyCheck = self::$db->execQuery($check, $params);
            $checkInsertedValue = $lazyCheck('fetch')->val;

            $this->assertEquals(1, $depRowsAffected);
            $this->assertEquals($mainId, $checkInsertedValue);
        }

        public function testFailedTransactionRollback()
        {
            $wrongRequest = 'INSERT INTO main_table (va_) VALUES (15)';
            $dependentRequest = 'INSERT INTO dep_table (id, val) VALUES (LAST_INSERT_ID(), 1)';
            $result = null;

            try {
                
                self::$db->beginTransaction();
                
                $lazyWrong = self::$db->execQuery($wrongRequest);
                $resultWrong = $lazyWrong('lastInsertId');

                $lazy = self::$db->execQuery($dependentRequest);
                $result = $lazy('rowCount');
                
                self::$db->commit();
            } catch (\PDOException $e) {
                self::$db->rollback();
            } finally {
            
                $this->assertNull($result);
            }
        }
    }