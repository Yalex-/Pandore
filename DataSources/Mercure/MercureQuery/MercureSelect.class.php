<?php

namespace Project\DataSources\Mercure\MercureQuery;

use Kernel\Services\PDO as PDO;
use Project\DataSources\Mercure\Exceptions as Exceptions;
use Project\DataSources\Mercure\ObjectParser\IObjectParser as IObjectParser;
use Project\DataSources\Mercure\QueryClause as QueryClause;

/**
 * @brief This class encapsulates a select query.
 *
 * @see Kernel::Services::PDO.
 * @see Mercure::ObjectParser::IObjectParser.
 * @see Mercure::QueryClause::FromClause.
 * @see Mercure::QueryClause::JoinClause.
 * @see Mercure::QueryClause::LimitClause.
 * @see Mercure::QueryClause::OrderByClause.
 * @see Mercure::QueryClause::WhereClause.
 */
class MercureSelect extends AbstractQuery
{
    use QueryClause\FromClause;
    use QueryClause\WhereClause;
    use QueryClause\OrderByClause;
    use QueryClause\LimitClause;
	use QueryClause\JoinClause;
    
    /**
     * @brief The selected attributes.
     * @var Array.
     */
	private $select;
	
    /**
     * @brief Constructor.
     * @param Kernel::Services::PDO $sql The sql object.
     * @param Mercure::ObjectParser::IObjectParser $parser The parser object.
     * @param Array $attributes The attributes.
     */
    public function __construct($sql, $parser, $attributes)
    {
        parent::__construct($sql, $parser);
		
		$this->select = array();
		foreach($attributes as $attr)
		{
			$this->select[] = $attr;
		}
    }

    /**
     * @brief Executes the query.
     * @param Int $fetchStyle Controls how the next row will be returned.
     * @return Mixed The query result.
     */
    public function exec($fetchStyle = \PDO::FETCH_ASSOC)
    {
        return $this->sql->select($this->generateQuery(), $this->args, $fetchStyle);
    }

	/**
	 * @brief Executes query and return results.
     * @param Int $fetchStyle Controls how the next row will be returned.
     * @return Mixed The query result.
	 */
	public function getResults($fetchStyle = \PDO::FETCH_ASSOC)
	{
		return $this->exec($fetchStyle);
	}
	
	/**
	 * @brief Executes query and return ONE result.
     * @param Int $fetchStyle Controls how the next row will be returned.
	 * @return Mixed The query result.
     *
     * @exception Mercure::Exceptions::BadCountException When the query has returned an invalid result quantity.
	 */
	public function getOneResult($fetchStyle = \PDO::FETCH_ASSOC)
	{
		$result = $this->exec($fetchStyle);
		if(count($result) != 1)
		{
            throw new Exceptions\BadCountException('The query has returned an invalid result quantity ('.count($result).')');
		}
        return $result[0];
	}
	
	/**
	 * @brief Executes query and return an array of object.
	 * @param String $objectType The complete object name (with namespace).
	 * @param String $index The attibute name used as array index.
	 * @return Array The object array.
	 */
    public function getObjects($objectType, $index = NULL)
    {
		$results = $this->exec();
        $return = array();
		if(strripos($objectType, '\\') === false)
		{
			$tableName = $this->parser->getTable($objectType);
		}
		else
		{
			$tableName = $this->parser->getTable(substr(strrchr($objectType, '\\'), 1));
		}
        foreach($results as $result)
        {
            $obj = new $objectType();
            foreach($result as $attribute => $value)
            {
				$this->parser->setAttributeValue($obj, $attribute, $value);
            }
            if(!is_null($index))
			{
                $return[$this->parser->getAttributeValue($obj, $index)] = $obj;
			}
			else
			{
                $return[] = $obj;
			}
        }
        return $return;
    }
	
	/**
	 * @brief Executes query and return ONE object.
	 * @param string $objectType The complete object name (with namespace).
	 * @return Mixed The object.
     *
     * @exception Mercure::Exceptions::BadCountException When the query has returned an invalid object quantity.
	 */
    public function getOneObject($objectType)
    {
        $objects = $this->getObjects($objectType);
        if (count($objects) != 1)
		{
            throw new Exceptions\BadCountException('The query has returned an invalid object quantity ('.count($result).')');
		}
		return $objects[0];
    }
	
	/**
     * @brief Generates the query.
     * @return String The query string.
     */
	protected function generateQuery()
	{
        $from  = $this->generateFrom();
		$from .= $this->generateJoin();
		$query  = 'SELECT ';

		if(empty($this->select))
		{
			$this->select[] = '*';
		}

		foreach($this->select as $attribute)
		{	
			$query .= $this->addAttribute($attribute).', ';
		}
		$query = substr($query, 0, -2);

		$query .= $from;
        $query .= $this->generateWhere();
        $query .= $this->generateOrderBy();
        $query .= $this->generateLimit();
        return $query;
	}
}
?>