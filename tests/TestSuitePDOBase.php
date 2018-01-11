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
            $this->db->execQuery('TRUNCATE TABLE test_table');
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
    }