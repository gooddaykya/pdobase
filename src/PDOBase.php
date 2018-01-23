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

            return function($fetchMethod) use($stmt) {
                $stmt->execute();
                if ($fetchMethod === 'lastInsertId')
                    return $this->db->lastInsertId();

                if (method_exists($stmt, $fetchMethod))
                    return $stmt->$fetchMethod();
                
                throw new \PDOException('Call undefined PDOStatement method');
            };
        }

        public function beginTransaction()
        {
            $this->db->beginTransaction();
        }

        public function commit()
        {
            $this->db->commit();
        }

        public function rollback()
        {
            $this->db->rollback();
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
