<?php
    namespace gooddaykya\tests;

    class TestSuitePDOBase extends \PHPUnit\Framework\TestCase
    {
        public function setUp()
        {
            $creds = include('credentials.php');
            $this->db = new \gooddaykya\components\PDOBase($creds);
        }

        public function tearDown()
        {
            $this->db->execQuery('TRUNCATE TABLE test_table')('lastInsertId');
        }

        public function testCreatePDO()
        {
            $this->assertObjectHasAttribute('db', $this->db);
        }

        public function testCreateTable()
        {
            $cleanup = 'DROP TABLE IF EXISTS test_table';
            $create  = 
                'CREATE TABLE test_table ' .
                '(' .
                    'id INT UNSIGNED NOT NULL AUTO_INCREMENT, ' .
                    'val VARCHAR(20) NOT NULL, ' .
                    'PRIMARY KEY (id)' .
                ') ENGINE = InnoDB CHARSET=utf8 COLLATE utf8_bin';

            $this->db->execQuery($cleanup);
            $this->db->execQuery($create);
        }

        public function insertQueryProvider()
        {
            return array(
                array('INSERT INTO test_table (val) VALUES ("test value 1")'),
                array('INSERT INTO test_table (val) VALUES ("Test value 2")')            
            );
        }

        public function insertBindQueryProvider()
        {
            return array(
                array(
                    'INSERT INTO test_table (val) VALUES (:val)',
                    array(':val' => 'Test value 1')
                ),
                array(
                    'INSERT INTO test_table (val) VALUES (:val)',
                    array(':val' => 42)      
                )
            );
        }

        public function selectQueryProvider()
        {
            return array(
                array(
                    'SELECT COUNT(id) AS res FROM const_table',
                    array(),
                    'fetch',
                    6
                ),
                array(
                    'SELECT val AS res FROM const_table WHERE id = :id',
                    array(':id' => 3),
                    'fetch',
                    1
                ),
                array(
                    'SELECT textval AS res FROM const_table WHERE val = :val',
                    array(':val' => 42),
                    'fetch',
                    'Universal answer'
                )
            );
        }

        public function transactionProvider()
        {
            return array(
                array(
                    'insert into main_table (val) values (:val)',
                    'lastInsertId',
                    'insert into dep_table (id, val) values (:id, :val)',
                    'rowCount'
                )
            );
        }

        public function lazyEvaluationRequestProvider()
        {
            return array(
                array(
                    'SELECT val AS res FROM const_table WHERE id = 2',
                    'SELECT textval AS res FROM const_table WHERE id = 2',
                    1,
                    'one'                    
                )
                );
        }

        /**
        * @dataProvider insertQueryProvider
        */
        public function testDynamicInsertValue($request)
        {
            $result = $this->db->execQuery($request)('lastInsertId');
            $this->assertEquals(1, $result);
        }

        /**
         * @dataProvider insertBindQueryProvider
         */
        public function testDynamicInsertBindedeValue($request, $bindParams)
        {
            $result = $this->db->execQuery($request, $bindParams)('lastInsertId');
            $this->assertEquals(1, $result);
        }

        /**
         * @dataProvider selectQueryProvider
         */
        public function testDynamicSelectValue($request, $bindParams, $fetchType, $expected)
        {
            $result = $this->db->execQuery($request, $bindParams)($fetchType)->res;
            $this->assertEquals($expected, $result);
        }

        public function testManualFetchColumn()
        {
            $result = $this->db->execQuery(
                'SELECT textval FROM const_table WHERE val = :val',
                array(':val' => 2)
            )('fetchColumn');
            $this->assertEquals('Two', $result);
        }

        /**
         * @dataProvider transactionProvider
         */
        public function testManualTransactionSuccess($request1, $fetch1, $request2, $fetch2)
        {
            $this->db->beginTransaction();
        
            $primaryId = $this->db->execQuery($request1, [':val' => 15])($fetch1);
            $result = $this->db->execQuery($request2, [':id' => $primaryId, ':val' => 1])($fetch2);
            
            $this->db->commit();

            $this->assertEquals(1, $result);
        }

        /**
         * @dataProvider transactionProvider
         */
        public function testManualTransactionFail($request1, $fetch1, $request2, $fetch2)
        {
            $this->expectException(\PDOException::class);

            $this->db->beginTransaction();

            $primaryId = $this->db->execQuery($request1, [':val' => 15])($fetch1);
            $result = $this->db->execQuery($request2, [':id' => $primaryId + 1, ':val' => 1])($fetch2);

            $this->db->commit();
        }

        public function testIncorrectSQLSyntax()
        {
            $this->expectException(\PDOException::class);
            $request = 'SLECT * FROM const_base';
            $result = $this->db->execQuery($request)('fetchAll');
        }

        /**
         * @dataProvider lazyEvaluationRequestProvider
         */
        public function testLazyEvaluationInterferenceRegularOrder($req_1, $req_2, $expect_1, $expect_2)
        {
            $res_1 = $this->db->execQuery($req_1);
            $res_2 = $this->db->execQuery($req_2);
            $this->assertEquals($expect_1, $res_1('fetch')->res);
            $this->assertEquals($expect_2, $res_2('fetch')->res);
        }

        /**
         * @dataProvider lazyEvaluationRequestProvider
         */
        public function testLazyEvaluationInterferenceReverseOrder($req_1, $req_2, $expect_1, $expect_2)
        {
            $res_1 = $this->db->execQuery($req_1);
            $res_2 = $this->db->execQuery($req_2);
            $this->assertEquals($expect_2, $res_2('fetch')->res);
            $this->assertEquals($expect_1, $res_1('fetch')->res);
        }
    }