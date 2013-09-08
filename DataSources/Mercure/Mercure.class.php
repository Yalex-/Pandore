<?php

namespace Project\DataSources\Mercure;

use Kernel\Core as Core;
use Kernel\Services as Services;
use Kernel\Services\PDO as PDO;
use Project\DataSources\Mercure\MercureQuery as MercureQuery;
use Project\DataSources\Mercure\ObjectParser as ObjectParser;

/**
 * @brief The Mercure class.
 * 
 * @details
 * Mercure is an Object Relational Mapper (ORM) based on PDO. It allows to do operations on objects which are representation of data in the database.
 *
 * @see Kernel::Core::IDataSource.
 */
class Mercure implements Core\IDataSource
{
    /**
     * @brief The Mercure configuration path.
     */
    const CONFIG_PATH = '/Project/DataSources/Mercure/Config/config.ini';

    /**
     * @brief The object parser namespace.
     * @var String.
     */
    private static $objectParserNamespace = 'Project\DataSources\Mercure\ObjectParser';

    /**
     * @brief The parser object.
     * @var Mercure::ObjectParser::IObjectParser.
     */
    private $parser;
    /**
     * @brief The sql object.
     * @var Kernel::Services::PDO.
     */
    private $sql;
    
    /**
     * @brief Constructor.
     * @param String $DSN The DSN.
     * 
     * @see http://php.net/manual/en/ref.pdo-mysql.connection.php.
     */
    public function __construct($DSN)
    {
        $this->sql = new PDO($DSN);

        $config = new Services\IniParser(ROOT_PATH.self::CONFIG_PATH);

        $objectParserClassName = self::$objectParserNamespace.'\\'.ucfirst($config->getValue('objectParser'));

        $this->parser = ObjectParser\ObjectParserFactory::get($objectParserClassName, $this, $config);
    }
	
    /**
     * @brief Get the object parser.
     * @return Mercure::ObjectParser::IObjectParser The object parser.
     */
    public function getParser()
    {
        return $this->parser;
    }
	 
    /**
	 * @brief Get the sql object.
	 * @return Kernel::Services::PDO The sql object.
	 */
	public function getSql()
	{
		return $this->sql;
	}
	
    /**************************************\
     *          Query functions           *
    \**************************************/

    /**
     * @brief Creates delete query.
     * @param Array $classesName The associated classes name.
     * @return Mercure::MercureQuery::MercureDelete The delete query.
     *
     * @details
     * Use :
     * - deleteFrom('Class').
     * - deleteFrom('Class1', 'Class2').
     * - deleteFrom(array('Class1', 'Class2')).
     */
    public function deleteFrom($classesName)
    {
        return new MercureQuery\MercureDelete($this->sql, $this->parser, $classesName);
    }
    	
    /**
     * @brief Creates insert query.
     * @param String $className The associated class name.
     * @param Mixed $attributes The selected attributes. If empty, all attributes are selected.
     * @return Mercure::MercureQuery::MercureInsert The insert query.
     *
     * @details
     * Use :
     * - insertInto('Class').
     * - insertInto('Class', 'attr1', 'attr2').
     * - insertInto('Class', array('attr1', 'attr2')).
     */
    public function insertInto($className, $attributes = array())
    {
		if(is_array($attributes))
		{
            $array = $attributes;
		}
        else
        {
            $array = func_get_args();
            unset($array[0]);
        } 
        return new MercureQuery\MercureInsert($this->sql, $this->parser, $className, $array);
    }
    
    /**
     * @brief Creates select query.
     * @param Mixed $attributes The selected attributes. If empty, all attributes are selected.
     * @return Mercure::MercureQuery::MercureSelect The select query.
     *
     * @details
     * Use :
     * - select().
     * - select('*').
     * - select('attr1', 'attr2').
     * - select(array('attr1', attr2)).
     */
    public function select($attributes = array())
    {
        if(is_array($attributes))
        {
            $array = $attributes;
        }
        else
        {
            $array = func_get_args();
        }
        return new MercureQuery\MercureSelect($this->sql, $this->parser, $array);
    }
    
    /**
     * @brief Creates update query.
     * @param Mixed $classesName The associated classes name.
     * @return Mercure::MercureQuery::MercureUpdate The update query.
     *
     * @details
     * Use :
     * - update('Class').
     * - update('Class1', 'Class2').
     * - update(array('Class1', 'Class2')).
     */
    public function update($classesName)
    {
        if(is_array($classesName))
        {
            $array = $classesName;
        }
        else
        {
            $array = func_get_args();
        }
        return new MercureQuery\MercureUpdate($this->sql, $this->parser, $array);
    }
    
    /**************************************\
     *          Object functions          *
    \**************************************/

    /**
     * @brief Deletes the given object in the database.
     * @param Object $object The object.
     * @return Int The deleted line number.
     */
    public function deleteOne($object)
    { 
        $className = $this->parser->getRelativeClassName($object);
        $tableName = $this->parser->getTable($className);
        $primaryKeys = $this->parser->getPrimaryKeys($className);
		
        $query = $this->deleteFrom($className);
        $ids = array();
        foreach($primaryKeys as $attribute)
        {
            $query->andWhere($attribute.' = ?', $this->parser->getAttributeValue($object, $attribute));
        }        
        return $query->exec();
    }
	
    /**
     * @brief Inserts the given object in the database and set id attribute.
     * @param Object $object The object.
     * @return Int The inserted line number.
     */
    public function insertOne(&$object)
    {
        $className = $this->parser->getRelativeClassName($object);
        $fieldsName = $this->parser->getAllFields($className);
        $autoAttributs = $this->parser->getAutoIncrement($className);
		
        $attributes = array();
        $values = array();
        foreach($fieldsName as $attribute => $fieldName)
        {
            $value = $this->parser->getAttributeValue($object, $attribute);
            if(!in_array($attribute, $autoAttributs))
            {
                $attributes[] = $attribute;
                $values[] = $value;
            }
        }
		
        $sqlQuery = $this->insertInto($className, $attributes);
		$sqlQuery->values($values);
        $res = $sqlQuery->exec($this->sql);
		
        // Auto increment attribute update.
        foreach($autoAttributs as $fieldName => $attribute)
        {
            $this->parser->setAttributeValue($object, $attribute, $this->sql->getLastInsertId($fieldName));
        }
        return $res;
    }
	
    /**
     * @brief Selects an object from id attribute in the database.
     * @param Object $object The object.
     */
    public function selectOne(&$object)
    {
        $className = $this->parser->getRelativeClassName($object);
        $query = $this->select()->from($className);
        $primaryKeys = $this->parser->getPrimaryKeys($className);

        foreach($primaryKeys as $attribute)
        {
            $query->where(lcfirst($attribute).' = ?', $this->parser->getAttributeValue($object, $attribute));
        }
        $res = $query->getOneResult();
        $tableName = $this->parser->getTable($className);
        foreach($res as $attribute => $value)
        {
            $this->parser->setAttributeValue($object, $attribute, $value);
        }
    }
	
    /**
     * @brief Updates database from the given object.
     * @param Object $object The object.
     * @return Int The updated line number.
     */
    public function updateOne($object)
    {
    	$className = $this->parser->getRelativeClassName($object);
        $primaryKeys = $this->parser->getPrimaryKeys($className);
        $fieldsName = $this->parser->getAllFields($className);
	   
        $query = $this->update($className);

        foreach($fieldsName as $attribute => $fieldName)
        {
            if(!in_array($attribute, $primaryKeys))
            {
                $value = $this->parser->getAttributeValue($object, $attribute);
                $query->set($attribute, $value);
            }
        }

        foreach($fieldsName as $attribute => $fieldName)
        {
            if(in_array($attribute, $primaryKeys))
            {
                $value = $this->parser->getAttributeValue($object, $attribute);
                $query->where($attribute.' = ?', $value);
            }
        }

        return $query->exec();
    }
}

?>
