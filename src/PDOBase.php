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

        public function execQuery($request, array $bindParams = array())
        {
            $stmt = $this->db->prepare($request);
            array_walk($bindParams, $this->bindParam($stmt));
            $stmt->execute();

            $lastInsertId = $this->db->lastInsertId();

            return function($fetchMethod) use($stmt, $lastInsertId)
            {
                return $fetchMethod === 'lastInsertId' ? 
                    $lastInsertId : $stmt->fetchMethod();
            };
        }

        private function bindParam($stmt)
        {
            return function($value, $placeholder) use($stmt)
            {
                $pdoType = is_int($value) ? \PDO::PARAM_INT : 
                    (is_bool($value) ? \PDO::PARAM_BOOL : \PDO::PARAM_STR);
                $stmt->bindValue($placeholder, $value, $pdoType);
            };
        }
    }