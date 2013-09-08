<?php

namespace Project\DataSources\Mercure\ObjectParser;

use Kernel\Services as Services;
use Project\DataSources\Mercure\Mercure as Mercure;

/**
 * @brief The ObjectParser interface.
 * 
 * @details
 * This class allows to link the classes with the tables.
 *
 * @see Kernel::Services::IniParser.
 * @see Mercure::Mercure.
 */
interface IObjectParser
{
    /**
     * @brief Constructor.
     * @param Mercure::Mercure $mercure The Mercure object.
     * @param Kernel::Services::IniParser $config The Mercure configuration.
     */
    public function __construct(Mercure $mercure, Services\IniParser $config);
    
    /**
     * @brief Get all table fields.
     * @param String $className The classe name.
     * @return Array The fields name array.
     */
    public function getAllFields($className);

    /**
     * @brief Get attribute from the associated field and table.
     * @param String $fieldName The field name.
     * @param String $tableName The table name.
     * @return String The attribute name.
     */
    public function getAttribute($fieldName, $tableName);

    /**
     * @brief Get value associated with the given attribute of the given object.
     * @param Mixed $object The object.
     * @param String $attribute The attribute name.
     * @return Mixed The value.
     */
    public function getAttributeValue($object, $attribute);

    /**
     * @brief Get attributes which are linked with the associated auto incremented fields.
     * @param String $className The class name.
     * @return Array The attributes name array.
     */
    public function getAutoIncrement($className);

    /**
     * @brief Get class from the associated table.
     * @param String $tableName The table name.
     * @return String The class name.
     */
    public function getClass($tableName);
    
    /**
     * @brief Get field from the associated attribute and class.
     * @param String $attributeName The attribute name.
     * @param String $className The class name.
     * @return String The field name.
     */
    public function getField($attributeName, $className);

    /**
     * @brief Get attributes which are linked with the associated primary keys.
     * @param String $className The class name.
     * @return Array The attributes name array.
     */
    public function getPrimaryKeys($className);

    /**
     * @brief Get object relative class name.
     * @param Mixed $object The object.
     * @return String The relative class name.
     */
    public function getRelativeClassName($object);
    
    /**
     * @brief Get table from the associated class.
     * @param String $className The class name.
     * @return String The table name.
     */
    public function getTable($className);
        
    /**
     * @brief Set value of the given attribute of the given object.
     * @param Mixed $object The object.
     * @param String $attribute The attribute name.
     * @param Mixed $value The value.
     */
    public function setAttributeValue(&$object, $attribute, $value);
}

?>