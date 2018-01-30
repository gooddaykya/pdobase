# PDOBase
[![Latest Stable Version](https://poser.pugx.org/gooddaykya/pdobase/v/stable)](https://packagist.org/packages/gooddaykya/pdobase) [![Total Downloads](https://poser.pugx.org/gooddaykya/pdobase/downloads)](https://packagist.org/packages/gooddaykya/pdobase) [![License](https://poser.pugx.org/gooddaykya/pdobase/license)](https://packagist.org/packages/gooddaykya/pdobase)

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
### Using
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

    $db = new \gooddaykya\components\PDOBase($requisites);
```
Or, by passing array from external file
```
    require '../vendor/autoload.php';

    $db = new \gooddaykya\components\PDOBase(require '../requisites.php');
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
---
### Testing
##### Testing tables

|const_table|||
| --- | --- | --- |
|**id**: unsigned int, not null, primary, AI|**val**: unsigned int, not null|**textval**: varchar(20)|
|1|0| |
|2|1|one|
|3|1|One|
|4|2|Two|
|5|13|Trirteen|
|6|42|Universal answer|


|main_table||
| --- | --- |
|**id**: unsigned int, not null, primary, AI|**val**: unsigned int, not null|

|dep_table|foreign key(main_table.id)|
| --- | --- |
|**id**: unsigned int, not null, primary|**val**: unsigned int, not null|

---
### ToDo List

[ ] separate current test cases into transaction and non transaction suites.

[ ] retrieve expected results directly from database.

---
### EOF
