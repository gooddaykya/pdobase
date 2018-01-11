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

        /**
        * @dataProvider insertQueryProvider
        */
        public function testDynamicInsertValue($request)
        {
            $result = $this->db->execQuery($request);
            $this->assertEquals(1, $result);
        }
    }