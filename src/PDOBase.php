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
                'charset='    . $credentials['charset'];
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

        public function changeFetchMode($mode)
        {
            $this->db->setFetchMode($mode);
        }

        public function execQuery($request, array $bindParams = array())
        {
            list ($request, $bindParams) = $this->augmentQuery($request, $bindParams);

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

        private function augmentQuery($request, array $bindings)
        {
            $toAugment = array_filter($bindings, function($value) {
                return is_array($value);
            });
            $toBind = array_diff($bindings, $toAugment);

            foreach ($toAugment as $placeholder => $values) {
                $str = array_reduce($values, function($query, $value) use(&$toBind) {
                    $query .= ':' . (string) $value . ', ';
                    $toBind[":$value"] = $value;
                    return $query;
                }, '');
                $res = str_replace($placeholder, substr($str, 0, -2), $request);
            }
            return [$res, $toBind];
        }
    }
