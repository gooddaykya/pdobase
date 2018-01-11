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
            $this->db = null;
        }

        public function testCreatePDO()
        {
            $this->assertObjectHasAttribute('db', $this->db);
        }

        public function testCreateTable()
        {
            $cleanup = 'drop table if exists test_table';
            $create  = 'create table test_table (
                id int unsigned not null auto_increment,
                val varchar(10) not null,
                primary key(id)
            ) engine=InnoDB default charset utf8-bin';

            $this->db->execQuery($cleanup);
            $this->db->execQuery($create);
        }
    }