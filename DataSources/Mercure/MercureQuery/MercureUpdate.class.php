<?php

namespace Project\DataSources\Mercure\MercureQuery;

use Kernel\Services\PDO as PDO;
use Project\DataSources\Mercure\QueryClause as QueryClause;
use Project\DataSources\Mercure\ObjectParser\IObjectParser as IObjectParser;

/**
 * @brief This class encapsulates a update query.
 *
 * @see Kernel::Services::PDO.
 * @see Mercure::ObjectParser::IObjectParser.
 * @see Mercure::QueryClause::LimitClause.
 * @see Mercure::QueryClause::OrderByClause.
 * @see Mercure::QueryClause::SetClause.
 * @see Mercure::QueryClause::WhereClause.
 */
class MercureUpdate extends AbstractQuery
{
    use QueryClause\SetClause;
    use QueryClause\WhereClause;
    use QueryClause\OrderByClause;
    use QueryClause\LimitClause;
    
    /**
     * @brief The updated classes.
     * @var Array.
     */
    private $updateClasses = array();
    
    /**
     * @brief Constructor.
     * @param Kernel::Services::PDO $sql The sql object.
     * @param Mercure::ObjectParser::IObjectParser $parser The parser object.
     * @param Array $classes The classes name.
     */
    public function __construct(PDO $sql, IObjectParser $parser, $classes)
    {
        parent::__construct($sql, $parser);
        $this->updateClasses = $classes;
    }
	
    /**
     * @brief Executes the query.
     * @return The query result.
     */
    public function exec()
    {
		return $this->sql->update($this->generateQuery(), $this->args);
    }
    
    /**
     * @brief Generates the query.
     * @return String The query string.
     */
	protected function generateQuery()
	{
		$query  = 'UPDATE ';
        foreach($this->updateClasses as $className)
        {
            $query .= $this->addClass($className).', ';
        }
        $query = substr($query, 0, -2);
        $query .= $this->generateSet();
        $query .= $this->generateWhere();
        $query .= $this->generateOrderBy();
        $query .= $this->generateLimit();
        return $query;
	}
}
?>