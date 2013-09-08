<?php

namespace Project\DataSources\Mercure\ObjectParser;

use Kernel\Services as Services;
use Project\DataSources\Mercure as Mercure;
use Project\DataSources\Mercure\Exceptions as Exceptions;

/**
 * @brief This class allows to link model syntax (camel case) with sql syntax (snake case) using database information.
 *
 * @see Kernel::Services::IniParser.
 * @see Mercure::Mercure.
 */
class DatabaseObjectParser implements IObjectParser
{
    /**
     * @brief The field name indexed by class and attribute name.
     * @var Array.
     */
    private $attrToField;
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
        $requete = 'SELECT column_name, table_name, column_key, extra FROM INFORMATION_SCHEMA.columns WHERE table_schema = ?';
        $infos = $mercure->getSql()->select($requete, array($mercure->getSql()->getDatabase()), \PDO::FETCH_OBJ);
        
        // table <=> class.
        // attibute <=> field.
        $this->fieldToAttr = array();
        $this->attrToField = array();
        $this->primaryAttr = array();
        $this->autoIncrementAttr = array();

        foreach($infos as $row)
        {
            $attribute = lcfirst($this->makeAttributeName($row->column_name, $row->table_name));
            
            $class = $this->makeClassName($row->table_name);
            $this->fieldToAttr[$row->table_name]['class'] = $class;
            $this->fieldToAttr[$row->table_name]['attribute'][strtolower($row->column_name)] = $attribute;
            $this->attrToField[$class]['table'] = $row->table_name;
            $this->attrToField[$class]['field'][$attribute] = $row->column_name;
			
			if(isset($this->primaryAttr[$class]) && !is_array($this->primaryAttr[$class]))
			{
				$this->primaryAttr[$class] = array();
			}
			
            if($row->column_key == 'PRI')
            {
                $this->primaryAttr[$class][] = $attribute;
            }
            
			if(isset($this->autoIncrementAttr[$class]) && !is_array($this->autoIncrementAttr[$class]))
			{
				$this->autoIncrementAttr[$class] = array();
			}

            if($row->extra === 'auto_increment')
            {
                $this->autoIncrementAttr[$class][$row->column_name] = $attribute;
            }
        }	
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
     * @brief Get object class name without namespace.
     * @param Mixed $object The object.
     * @return String The class name without namespace.
     */
    public function getRelativeClassName($object)
    {
        return substr(strrchr(get_class($object), '\\'), 1);
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
			throw new Exceptions\ObjectParserException('The table associated with the class "'.$className.'" doesn\'t exist.');
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
     * @brief Makes the attribute name from the field and the table name.
     * @param String $fieldName The field name.
     * @param String $tableName The table name.
     * @return String The attribute name.
     */
    private function makeAttributeName($fieldName, $tableName)
    {
        // table_name_field_name => field_name.
        $attribute = substr($fieldName, strlen($tableName) + 1);
        // field_name => Field Name.
        $attribute = ucwords(str_replace('_', ' ', $attribute));
        // Field Name => FieldName = Attribute.
        return str_replace(' ', '', $attribute);
    }
    
	/** 
     * @brief Makes class name from the table name.
     * @param String $tableName The table name.
     * @return String The classe name.
     */
    private function makeClassName($tableName)
    {
        // table_name => Table Name.
        $class = ucwords(str_replace('_', ' ', $tableName));
        // Table Name => TableName = Class.
        return str_replace(' ', '', $class);
    }
}

?>