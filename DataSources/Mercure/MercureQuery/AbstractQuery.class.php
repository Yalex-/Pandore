<?php

namespace Project\DataSources\Mercure\MercureQuery;

use Kernel\Services\PDO as PDO;
use Project\DataSources\Mercure\ObjectParser\IObjectParser as IObjectParser;

/**
 * @brief This class encapsulates an abstract query.
 *
 * @see Kernel::Services::PDO.
 * @see Mercure::ObjectParser::IObjectParser.
 */
abstract class AbstractQuery
{
    /**
     * @brief The query arguments.
     * @var Array.
     */
    protected $args;
    /**
     * @brief The query classes.
     * @var Array.
     */
    protected $classes;
    /**
     * @brief The fields name of query classes.
     * @var Array.
     */
    protected $fieldsName;
    /**
     * @brief The parser object.
     * @var Mercure::ObjectParser::IObjectParser.
     */
    protected $parser;
    /**
     * @brief The sql object.
     * @var Kernel::Services::PDO.
     */
    protected $sql;
 
    /**
     * @brief Constructor.
     * @param Kernel::Services::PDO $sql The sql object.
     * @param Mercure::ObjectParser::IObjectParser $parser The parser object.
     */
	public function __construct(PDO $sql, IObjectParser $parser)
	{
        $this->parser = $parser;
        $this->sql = $sql;
        
		$this->args = array();
		$this->classes = array();
        $this->fieldsName = array();
	}

    /**
     * @brief Allows to decide how this class will react when it is treated like a string.
     * @return String The query string.
     */
    public function __toString()
    {
        $query = $this->generateQuery();
        return $query.' [ '.implode(', ', $this->args).' ]';
    }

    /**
     * @brief Executes the query.
     * @return Mixed The query results.
     */
    public abstract function exec();

    /**
     * @brief Adds attribute to the query.
     * @param String $attribute The attribute name.
     * @return String The field name.
     */
    protected function addAttribute($attribute)
    {
        // Computes attribute and alias names.
        $explode = explode(' AS ', $attribute);
        $attributeName = $explode[0];
        if(count($explode) == 2)
        {
            $alias = $explode[1];
        }

        // Computes field name.
        $fieldName = $this->getFieldName($attributeName);

        // Complete field name.
        if(isset($alias))
        {
            $fieldName .= ' AS "'.$alias.'"';
        }
        elseif(array_key_exists($attributeName, $this->fieldsName))
        {
            $fieldName .= ' AS "'.$attributeName.'"';
        }
        elseif(stripos($attributeName, '*') !== false)
        {
            $fieldName = '';
            $classesNb = count($this->classes);
            foreach($this->classes as $class => $fields)
            {
                foreach($fields as $attr => $field)
                {
                    $pos = stripos($attr, $class.'.');
                    if(($pos === false && $classesNb == 1) || ($pos !== false && $classesNb > 1))
                    {
                        $fieldName .= $field.' AS "'.$attr.'", ';
                    }
                }
            }
            $fieldName = str_replace('*', substr($fieldName, 0, -2), $attributeName);
        }

        return $fieldName;
    }
	
	/**
     * @brief Adds arguments to the query.
     * @param Mixed $arg The query data.
     *
     * @details
     * Use :
     * - addArgument($arg).
     * - addArgument(array($arg1, $arg2)).
	 */
    protected function addArgument($arg)
    {
		if(is_array($arg))
		{
			foreach($arg as $argument)
            {
                $this->args[] = $argument;
            }
		}
		else
        {
			$this->args[] = $arg;
        }
    }

    /**
     * @brief Adds class to the query.
     * @param String $class The class name.
     * @return String The table name.
     */
    protected function addClass($class)
    {
        // Computes class and alias name.
        $explode = explode(' AS ', $class);
        $className = $explode[0];
        if(count($explode) == 2)
        {
            $alias = $explode[1];
        }

        // Compute table name.
        $table = $this->parser->getTable($className);

        // Complete table name.
        if(isset($alias))
        {
            $table .= ' AS '.$alias;
        }
        //
        else
        {
            $alias = $className;
        }

        // Computes fields <-> attributes arrays.
        $aliasTable = $this->parser->getTable($className).'.';
        $aliasClass = $alias.'.';
        $fields = $this->parser->getAllFields($className);
        foreach($fields as $attribute => $field)
        {
            $aliasAttribute = $aliasClass.$attribute;
            $aliasField = $aliasTable.$field;
            $this->fieldsName[$attribute] = $field;
            $this->fieldsName[$aliasAttribute] = $aliasField;
        }
        $this->classes[$className] = $this->fieldsName;

        // Return table name.
        return $table;
    }

    /**
     * @brief Get field name linked with the given attribute and class name.
     * @param String $attributeName The attribute name.
     * @param Mixed $className The class name.
     * @return String The formated field name.
     */
    protected function getFieldName($attributeName, $className = null)
    {
        if($className != null)
        {
            $fieldName = strtr($attributeName, $this->classes[$className]);
        }
        else
        {
            $fieldName = strtr($attributeName, $this->fieldsName);
        }
        return $fieldName;
    }

    /**
     * @brief Generates the query.
     * @return String The query.
     */
    protected abstract function generateQuery();
}

?>