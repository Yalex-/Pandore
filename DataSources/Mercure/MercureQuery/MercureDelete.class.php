<?php

namespace Project\DataSources\Mercure\MercureQuery;

use Kernel\Services\PDO as PDO;
use Project\DataSources\Mercure\ObjectParser\IObjectParser as IObjectParser;
use Project\DataSources\Mercure\QueryClause as QueryClause;

/**
 * @brief This class encapsulates a delete query.
 *
 * @see Kernel::Services::PDO.
 * @see Mercure::ObjectParser::IObjectParser.
 * @see Mercure::QueryClause::FromClause.
 * @see Mercure::QueryClause::LimitClause.
 * @see Mercure::QueryClause::OrderByClause.
 * @see Mercure::QueryClause::WhereClause.
 */
class MercureDelete extends AbstractQuery
{
    use QueryClause\FromClause;
    use QueryClause\WhereClause;
    use QueryClause\OrderByClause;
    use QueryClause\LimitClause;
    
    /**
     * @brief Constructor.
     * @param Kernel::Services::PDO $sql The sql object.
     * @param Mercure::ObjectParser::IObjectParser $parser The parser object.
     * @param Mixed $classes The classes name.
     */
    public function __construct(PDO $sql, IObjectParser $parser, $classes)
    {
        parent::__construct($sql, $parser);
        $this->from($classes);
    }
	
    /**
     * @brief Executes the query.
     * @return Int The rows number affected by the delete query.
     */
    public function exec()
    {
		return $this->sql->delete($this->generateQuery(), $this->args);
    }
	
    /**
     * @brief Generates the query.
     * @return String The query string.
     */
	protected function generateQuery()
	{
		$query  = 'DELETE';
        $query .= $this->generateFrom();
        $query .= $this->generateWhere();
        $query .= $this->generateOrderBy();
        $query .= $this->generateLimit();
        return $query;
	}
}
?>