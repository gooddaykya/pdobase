<?php
    namespace gooddaykya\components;

    class PDOBase
    {
        private $db;

        public function __construct(array $credentials)
        {
            $rules = array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
            );
            $dsn = 
                'mysql:host=' . $credentials['host'] . ';' .
                'dbname='     . $credentials['dbname'] . ';' .
                'char='       . $credentials['char'];
            try {
                $this->db = new \PDO(
                    $dsn,
                    $credentials['user'],
                    $credentials['password'],
                    $rules
                );
            } catch (\PDOException $e) {
                throw $e;
            }
        }

        public function execQuery($request)
        {
            $stmt = $this->db->prepare($request);
            $stmt->execute();
            $lastInsertId = $this->db->lastInsertId();

            return $lastInsertId;
        }
    }