# Mercure

Mercure is an ORM distributed with Pandore to abstract database queries.

## How to install

To install Mercure, put it in `Pandore/Project/DataSources/` and add the following instructions in the configuration file in `Project/Config/` under `[Datasource]`.

    [Datasource]
    source[__SourceName__] = Mercure
    dsn[__SourceName__] = dbms:__DBMS__+host:__HOST__+dbname:__DB_NAME__+username:__USERNAME__+password:__PASSWORD__

## How to use

To use Mercure, you don't need to manipulate sql syntax, only use Pandore Models notation.

Each Model is parsed to match an organization's database : the class name `MyClassName` is the table `my_class_name`. This transformation is the job of `DatabaseObjectParser`. You can implement your own database notation by adding your own parser.

### Select

#### Examples

Select all objects from `class_name` table and filled `ClassName` objects array.

    $objects = DataSourceProxy::get()->select('*')
                                     ->from('ClassName')
                                     ->getObjects('Namespace\\ClassName');

Select one object with `Attribute` and `AnotherAttribute` attributes filled from database where `class_name_id` is equal to `$id`.

    $oneObject = DataSourceProxy::get()->select('Attribute', 'AnotherAttribute')
                                       ->from('ClassName')
                                       ->where('id = ?', $id)
                                       ->getOneObject('Namespace\\Classname');

Select `Attribute` and `AnotherAttribute` from `class_name` table and filled `ClassName` objects array with selected values.

    $objects = DataSourceProxy::get()->select(array('Attribute', 'AnotherAttribute'))
                                     ->from('ClassName')
                                     ->getObjects('Namespace\\ClassName');

Counts the number of rows that matches specified criteria. `AS` clause must capitalize.

    $res = DataSourceProxy::get()->select('COUNT(*) AS count')
                                 ->from('TableTest')
                                 ->getOneResult(\PDO::FETCH_OBJ);

Use `count` to get the number of rows.

    echo 'Number of rows : '.$res->count;
    =>
    Number of rows : 2

#### Results presentation

To execute queries and retrives values, Mercure provides several methods with different results presentation.

##### The getResults method

###### Prototype

     /**
      * @brief Executes query and return results.
      * @param Int $fetchStyle Controls how the next row will be returned.
      * @return Mixed The query result.
      */
    public function getResults($fetchStyle = \PDO::FETCH_ASSOC)

###### Example

    $results = DataSourceProxy::get()->select('*')
                                     ->from('ClassName')
                                     ->getResults();

###### Result

    Array
    (
        [0] => Array
            (
                [id] => 1
                [attribute] => 47654.3
                [anotherAttribute] => first example
            )

        [1] => Array
            (
                [id] => 2
                [attribute] => 13.23
                [anotherAttribute] => second example
            )

    )

##### The getOneResult method

###### Prototype

     /**
      * @brief Executes query and return ONE result.
      * @param Int $fetchStyle Controls how the next row will be returned.
      * @return Mixed The query result.
      *
      * @exception Kernel::Exceptions::BadCountException When the query has returned an invalid result quantity.
      */
    public function getOneResult($fetchStyle = \PDO::FETCH_ASSOC)

###### Example

    $result = DataSourceProxy::get()->select('*')
                                    ->from('ClassName')
                                    ->where('id = ?', $id)
                                    ->getOneResult();

###### Result

    Array
    (
        [id] => 1
        [attribute] => 47654.3
        [anotherAttribute] => first example
    )

##### The getObjects method

###### Prototype

     /**
      * @brief Executes query and return an array of object.
      * @param String $objectType The complete object name (with namespace).
      * @param String $index The attibute name used as array index.
      * @return Array The object array.
      */
     public function getObjects($objectType, $index = NULL)

###### Example

     $objects = DataSourceProxy::get()->select('*')
                                      ->from('ClassName')
                                      ->getObjects('Namespace\\ClassName', 'id');

###### Result

    Array
    (
        [0] => Project\Models\TableTest Object
            (
                [id:Project\Models\TableTest:private] => 1
                [attribute:Project\Models\TableTest:private] => 47654.3
                [anotherAttribute:Project\Models\TableTest:private] => first example
            )

        [1] => Project\Models\TableTest Object
            (
                [id:Project\Models\TableTest:private] => 2
                [attribute:Project\Models\TableTest:private] => 13.23
                [anotherAttribute:Project\Models\TableTest:private] => second example
            )
    )

##### The getOneObject method

###### Prototype

     /**
      * @brief Executes query and return ONE object.
      * @param string $objectType The complete object name (with namespace).
      * @return Mixed The object.
      *
      * @exception Kernel::Exceptions::BadCountException When the query has returned an invalid object quantity.
      */
    public function getOneObject($objectType)

###### Example

    $object = DataSourceProxy::get()->select('attribute', 'anotherAttribute')
                                    ->from('ClassName')
                                    ->where('id = ?', $id)
                                    ->getOneObject('Namespace\\ClassName');

###### Result

    Project\Models\TableTest Object
    (
        [id:Project\Models\TableTest:private] => 
        [attribute:Project\Models\TableTest:private] => 47654.3
        [anotherAttribute:Project\Models\TableTest:private] => first example
    )

#### SelectOne

Mercure perfectly integrate into Pandore with Models notation. Indeed, you can select an object from database matching `$id` and filled attributes.

    $object = new Models\Object($id);
    DataSourceProxy::get()->selectOne($object);

### Update

`Update` method return `true` on success or `false` if an error occured.

#### Examples    

Update `class_name`, set `class_name_attribute` to `newValue` where `class_name_id` equals to `$id`.

    DataSourceProxy::get()->update('ClassName')
                          ->set('attribute', $newValue)
                          ->where('id = ?', $id)
                          ->exec();

Mercure accepts rules in `set` clause. In this example, 3 is added to `class_name_attribute` with a join to `another_class_name`.

    DataSourceProxy::get()->update('ClassName', 'AnotherClassName')
                          ->set('ClassName.attribute', 3, 'ClassName.attribute + ?')
                          ->where('ClassName.id = AnotherClassName.id')
                          ->andWhere('AnotherClassName.id = ?', array($id))
                          ->exec();

#### UpdateOne

    $object = new Models\Object($id);
    $object->setAttribute($value);
    $isSuccessfull = DataSourceProxy::get()->updateOne($object);

### Insert

`InsertInto` method return `true` on success or `false` if an error occured.

#### Examples

    DataSourceProxy::get()->insertInto('ClassName', array($value, $anotherValue))
                          ->exec();

Insert into `class_name` with specific columns.

    DataSourceProxy::get()->insertInto('ClassName', 'Attribute', 'AnotherAttribute')
                          ->values($value, $anotherValue)
                          ->exec();

Insert into `class_name` by applying SQL function in `$value.

    DataSourceProxy::get()->insertInto('ClassName')
                          ->values(array('ROUND(?)', $value), $anotherValue)
                          ->exec();

Insert into `class_name` with an array of values.

    DataSourceProxy::get()->insertInto('ClassName')
                          ->values(array($value, $anotherValue))
                          ->exec();

#### InsertOne

    $object = new Models\Object();
    $object->setAttribute($value);
    $object->setAnotherAttribute($anotherValue);
    $isSuccessfull = DataSourceProxy::get()->insertOne($object);

### Delete

`DeleteFrom` method return `true` on success or `false` if an error occured.

#### Example

    DataSourceProxy::get()->deleteFrom('ClassName')
                          ->where('id = ?', $id)
                          ->exec();

#### DeleteOne

    $object = new Models\Object($id);
    $isSuccessfull = DataSourceProxy::get()->deleteOne($object);

### Others instructions

#### Limit

##### Prototype

    /**
     * @brief Adds limit clause to the query.
     * @param Int $nb The number of result needed.
     * @param Int $start The start of the limit.
     * @return Mixed The query.
     */
    public function limit($nb, $start = 0)

##### Example

    $res = DataSourceProxy::get()->select('*')
                                 ->from('ClassName')
                                 ->limit(5)
                                 ->getResults();

#### Join

Mercure allows several type of joins :

  - crossJoin
  - innerJoin
  - join
  - leftJoin
  - leftOuterJoin
  - naturalLeftJoin
  - naturalLeftOuterJoin
  - naturalRightJoin
  - naturalRightOuterJoin
  - rightJoin
  - rightOuterJoin
  - straightJoin

And the predicates for the join is specified by the following methods.

##### on
        
    /**
     * @brief Adds 'on' condition to the query.
     * @param String $condition The join condition.
     * @param Mixed $queryData The associated query data.
     * @return Mixed The query.
     *
     * @details
     * $queryData is only $value or $array.
     */
    public function on($condition, $queryData = null)

##### orOn

    /**
     * @brief Adds 'or on' condition to the query.
     * @param String $condition The join condition.
     * @param Mixed $queryData The associated query data.
     * @return Mixed The query.
     *
     * @details
     * $queryData is only $value or $array.
     */
    public function orOn($condition, $queryData = null)

##### andOn

    /**
     * @brief Adds 'and on' condition to the query.
     * @param String $condition The join condition.
     * @param Mixed $queryData The associated query data.
     * @return Mixed The query.
     *
     * @details
     * $queryData is only $value or $array.
     */
    public function andOn($condition, $queryData = null)

##### using

    /**
     * @brief Adds using to the join.
     * @param Mixed The restricted attributes of the natural join.
     * @return Mixed The query.
     *
     * @details
     * Use :
     * - using('attr1', 'attr2').
     * - using(array('attr1', 'attr2')).
     */
    public function using($attributes)

##### Example

    $res = DataSourceProxy::get()->select('*')
                                 ->from('ClassName')
                                 ->join('AnotherClassName')
                                 ->on('ClassName.id = AnotherClassName.id')
                                 ->getResults();

#### OrderBy

##### Prototype

###### order by asc

    /** 
     * @brief Adds 'order by asc' clause to the query.
     * @param $attribute The associated attribute.
     * @return Mixed The query.
     */
    public function orderByAsc($attribute)

###### order by desc

    /** 
     * @brief Adds 'order by desc' clause to the query.
     * @param $attribute The associated attribute.
     * @return Mixed The query.
     */
    public function orderByDesc($attribute)

##### Example

    $res = DataSourceProxy::get()->select('attribute', 'anotherAttribute')
                                 ->from('ClassName')
                                 ->orderByDesc('attribute')
                                 ->getResults();

#### Where

##### Examples

    $res = DataSourceProxy::get()->select('*')
                                 ->from('ClassName')
                                 ->where('id = ?', $id)
                                 ->andWhere('attribute < ?', 10)
                                 ->orWhere('anotherAttribute > ?', 5)
                                 ->getResults();
                                 
If you want to manage priority with parenthesis, you must write directly like this example.

    $res = DataSourceProxy::get()->select('*')
                                 ->from('ClassName')
                                 ->where('id = ? OR (attribute < ? AND attribute > ?)', array($id, 5, 10))
                                 ->getResults(); 
    