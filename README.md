# PDOBase

### Wrapper for PDO
---
## Description

### What it is?

**PDOBase** is a lightweight and easy-to-config wrapper for PDO. It hides most of routine under 
boilerplate, thus a developer can focus on *what* should be done instead of *how* it should be done.

### What it is not?
**PDOBase** is not a query builder and doesn't include one. 

Two reasons for this:
1. Supporting every SQL feature will add complexity to existing class.
2. Query builder, separated from specific database adaptor, becomes reusabe with another adaptors, that use SQL.
---
### Basic usage
##### Instantiating
```
    require '../vendor/autoload.php';

    $requisites = array(
        'host' => 'localhost',
        'user' => 'root',
        'char' => 'utf8',
        'dbname' => 'test_base',
        'password' => ''
    );

    $db = new gooddaykya\components\PDOBase($requisites);
```
##### Getting data from database
```
    $result = $db->execQuery('SELECT * FROM const_table')('fetchAll');
```
##### Using prepared statements
```
    $request = 'SELECT val, textval FROM const_base WHERE id = :id';
    $bindParams = array(
        ':id' => 1
    );
    
    $result = $db->execQuery($request, $bindParams)('fetch');
```
##### ACID example
```
    $primeRequest = 'INSERT INTO main_table (val) VALUES (:val)';
    $dependentReq = 'INSERT INTO dep_table (id, val) VALUES (:id, :val)';

    try {
        $db->beginTransaction();
        $insertedId = $db->execQuery($primeRequest,
            array(':val' => 'Independent value')
        )('lastInsertId');
        
        $result = $db->execQuery(
            $dependentReq,
            array(
                ':id' => $insertedId,
                ':val' => 'Dependent value'
            )
        )('rowCount');
        
        $db->commit();
    } catch (\PDOException $e) {
        $db->rollback();
    }
```