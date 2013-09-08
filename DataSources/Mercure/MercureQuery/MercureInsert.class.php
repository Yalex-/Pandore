<?php

namespace Project\DataSources\Mercure\MercureQuery;

use Kernel\Services\PDO as PDO;
use Project\DataSources\Mercure\ObjectParser\IObjectParser as IObjectParser;
use Project\DataSources\Mercure\QueryClause as QueryClause;

/**
 * @brief This class encapsulates an insert query.
 *
 * @see Kernel::Services::PDO.
 * @see Mercure::ObjectParser::IObjectParser.
 * @see Mercure::QueryClause::ValuesClause.
 */
class MercureInsert extends AbstractQuery
{
    use QueryClause\ValuesClause;

    /**
     * @brief The associated class.
     * @var String.
     */
    private $into;
    /**
     * @brief The inserted attributes.
     * @var Array.
     */
    private $attributes;
    
    /**
     * @brief Constructor.
     * @param Kernel::Services::PDO $sql The sql object.
     * @param Mercure::ObjectParser::IObjectParser $parser The parser object.
     * @param String $className The class name.
     * @param Array $attributes The attributes.
     */
    public function __construct(PDO $sql, IObjectParser $parser, $className, $attributes)
    {
        parent::__construct($sql, $parser);
        $this->addClass($className);
        $this->into = $className;
        $this->attributes = $attributes;
    }
	
    /**
     * @brief Executes the query.
     * @return Int The rows number affected by the insert query.
     */
    public function exec()
    {
		return $this->sql->insert($this->generateQuery(), $this->args);
    }
    
    /**
     * @brief Generates the query.
     * @return String The query string.
     */
    protected function generateQuery()
	{
        $table = $this->parser->getTable($this->into);
		$query = 'INSERT INTO '.$table.' (';

        if(empty($this->attributes))
        {
            $query .= implode(', ', array_diff($this->parser->getAllFields($this->into), array_keys($this->parser->getAutoIncrement($this->into))));
        }
        else
        {
            $query .= $this->getFieldName(implode(', ', $this->attributes), $this->into);
        }

        $query .= ')';
        $query .= $this->generateValues();
		return $query;
	}
}

?>