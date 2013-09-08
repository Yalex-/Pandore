<?php

namespace Project\DataSources\Mercure\ObjectParser;

use Kernel\Services as Services;
use Project\DataSources\Mercure as Mercure;
use Project\DataSources\Mercure\Exceptions as Exceptions;

/**
 * @brief This class allows to link model syntax (camel case) with sql syntax (snake case) using schema information.
 *
 * @see Kernel::Services::IniParser.
 * @see Mercure::Mercure.
 */
class SchemaObjectParser implements IObjectParser
{
    /**
     * @brief The field name indexed by class and attribute name.
     * @var Array.
     */
    private $attrToField;
    /**
     * @brief The Mercure configuration.
     * @var Kernel::Services::IniParser.
     */
    private $config;
    /**
     * @brief The attributes name indexed by table and field name.
     * @var Array.
     */
    private $fieldToAttr;
    /**
     * @brief The attributes linked with the associated primary keys.
     * @var Array.
     */
    private $primaryAttr;
    /**
     * @brief The attributes which are linked with the associated auto incremented fields.
     * @var Array.
     */
    private $autoIncrementAttr;
    
    /**
     * @brief Constructor.
     * @param Mercure::Mercure $mercure The Mercure object.
     * @param Kernel::Services::IniParser $config The Mercure configuration.
     */
    public function __construct(Mercure\Mercure $mercure, Services\IniParser $config)
    {
        $this->config = $config;

        // table <=> class.
        // attibute <=> column.
        $this->fieldToAttr = array();
        $this->attrToField = array();
        $this->primaryAttr = array();
        $this->autoIncrementAttr = array();
    }

    /**
     * @brief Get all table fields.
     * @param String $className The classe name.
     * @return Array The fields name array.
     */
    public function getAllFields($className)
    {
        return $this->attrToField[$className]['field'];
    }
    
    /**
     * @brief Get attribute from the associated field and table.
     * @param String $fieldName The field name.
     * @param String $tableName The table name.
     * @return String The attribute name.
     *
     * @exception Mercure::Exceptions::ObjectParserException When the attribute associated with the field and the table doesn't exist.
     */
    public function getAttribute($fieldName, $tableName)
    {
        $attribute = $this->fieldToAttr[$tableName]['attribute'][$fieldName];
        if($attribute == null || $attribute == '')
        {
            new Exceptions\ObjectParserException('The attribute associated with the field "'.$fieldName.'" of the table "'.$tableName.'" doesn\'t exist.');
        }
        return $attribute;
    }

    /**
     * @brief Get value associated with the given attribute of the given object.
     * @param Mixed $object The object.
     * @param String $attribute The attribute name.
     * @return Mixed The value.
     */
    public function getAttributeValue($object, $attribute)
    {
        $get = 'get'.ucfirst($attribute);
        return $object->$get();
    }

    /**
     * @brief Get attributes which are linked with the associated auto incremented fields.
     * @param String $className The class name.
     * @return Array The attributes name array.
     */
    public function getAutoIncrement($className)
    {
        return $this->autoIncrementAttr[$className];
    }

    /**
     * @brief Get class from the associated table.
     * @param String $tableName The table name.
     * @return String The class name.
     *
     * @exception Mercure::Exceptions::ObjectParserException When the class associated with the table doesn't exist.
     */
    public function getClass($tableName)
    {
        $class = $this->fieldToAttr[$tableName]['class'];
        if($class == null || $class == '')
        {
            throw new Exceptions\ObjectParserException('The class associated with the table "'.$tableName.'" doesn\'t exist.');
        }
        return $class;
    }
	
    /**
     * @brief Get field from the associated attribute and class.
     * @param String $attributeName The attribute name.
     * @param String $className The class name.
     * @return String The field name.
     *
     * @exception Mercure::Exceptions::ObjectParserException When the field associated with the attribute and the class doesn't exist.
     */
    public function getField($attributeName, $className)
    {
        $class = strtok($attributeName, '.');
        $attributeName = strtok($attributeName);
        if($attributeName == '')
        {
            $attributeName = $class;
        }
        else
        {
            $className = $class;
        }
        $field = $this->attrToField[$className]['field'][$attributeName];
        if($field == null || $field == '')
        {
            throw new Exceptions\ObjectParserException('The field associated with the attribute "'.$attributeName.'" of the class "'.$className.'" doesn\'t exist.');
        }
        return $field;
    }

    /**
     * @brief Get attributes which are linked with the associated primary keys.
     * @param String $className The class name.
     * @return Array The attributes name array.
     */
    public function getPrimaryKeys($className)
    {
        return $this->primaryAttr[$className];
    }
	
    /**
     * @brief Get table from the associated class.
     * @param String $className The class name.
     * @return String The table name.
     *
     * @exception Mercure::Exceptions::ObjectParserException When the table associated with the class doesn't exist.
     */
    public function getTable($className)
    {
		$table = $this->attrToField[$className]['table'];
		if($table == null || $table == '')
        {
            try {
                $this->parseErosSchema($className);
        		$table = $this->attrToField[$className]['table'];
            } catch(\Exception $e) {
                throw new Exceptions\ObjectParserException('The table associated with the class "'.$className.'" doesn\'t exist.');
            }
        }
        return $table;
    }
    
    /**
     * @brief Set value of the given attribute of the given object.
     * @param Mixed $object The object.
     * @param String $attribute The attribute name.
     * @param Mixed $value The value.
     */
    public function setAttributeValue(&$object, $attribute, $value)
    {
        $set = 'set'.ucfirst($attribute);
        $object->$set($value);
    }
    	
    /**
     * @brief Get object class name without namespace.
     * @param Mixed $object The object.
     * @return String The class name without namespace.
     */
    public function getRelativeClassName($object)
    {
        return substr(strrchr(get_class($object), '\\'), 1);
    }
    
    /** 
     * @brief Makes table name from the class name.
     * @param String $className The class name.
     * @return String The table name.
     */
    private function makeTableName($className)
    {
        // ClassName => Class Name
        $class = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $className);
        // Class Name => class_name = table.
        return str_replace(' ', '', strtolower($class));
    }

    /**
     * @brief Parses eros class schema.
     * @param String $className The class name.
     */
    private function parseErosSchema($className)
    {
        $schemaClassName = str_replace(DIRECTORY_SEPARATOR, '\\', $this->config->getValue('schemaClassPath'));
        $reflectionClass = new \ReflectionClass($schemaClassName);
        $parser = $reflectionClass->newInstance($this->config->getValue('schemaPath').$className);

        $elements = $parser->getModel()->getElements();
        $tableName = $this->parseClassName($className);
        $this->primaryAttr[$className] = array();
        $this->autoIncrementAttr[$className] = array();
        $this->attrToField[$className]['table'] = $tableName;
        $this->fieldToAttr[$tableName]['class'] = $className;

        foreach($elements as $attributeName => $element)
        {
            $columnName = $element['sqlRelation'];
            $this->fieldToAttr[$tableName]['attribute'][$columnName] = $attribute;
            $this->attrToField[$className]['field'][$attribute] = $columnName;
            if($element['primary'] == 'true')
            {
                $this->primaryAttr[$className][] = $attributeName;
            }
            if($element['autoincrement'] == 'true')
            {
                $this->autoIncrementAttr[$className][$columnName] = $attributeName;
            }
        }
    }
}

?>