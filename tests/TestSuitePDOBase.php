<?php
    namespace gooddaykya\tests;

    class TestSuitePDOBase extends \PHPUnit\Framework\TestCase
    {
        public function setUp()
        {
            $this->db = new \gooddaykya\components\PDOBase();
        }

        public function tearDown()
        {
            $this->db = null;
        }

        public function testCreatePDO()
        {
            $this->assertObjectHasAttribute('db', $this->db);
        }
    }