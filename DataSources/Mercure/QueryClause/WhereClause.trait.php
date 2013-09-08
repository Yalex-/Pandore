<?php

namespace Project\DataSources\Mercure\QueryClause;

/**
 * @brief The where clause of the query.
 */
trait WhereClause
{
	use Clause;
	
    /**
     * @brief The where string.
     * @var String.
     */
	protected $where = '';
	
    /**
     * @brief Adds where clause to the query.
     * @param String $condition The where condition.
     * @param Mixed $queryData The associated query data.
     * @return Mixed The query.
     *
     * @details
     * $queryData is only $value or $array.
     */
    public function where($condition, $queryData = null)
    {
        $this->where .= $condition;
		if($queryData !== null)
        {
			$this->addArgument($queryData);
        }
        return $this;
    }

    /**
     * @brief Adds 'and' condition to the query.
     * @param String $condition The where condition.
     * @param Mixed $queryData The associated query data.
     * @return Mixed The query.
     *
     * @details
     * $queryData is only $value or $array.
     */
    public function andWhere($condition, $queryData = null)
    {
        return $this->where(' AND '.$condition, $queryData);
    }

    /**
     * @brief Adds 'or' condition to the query.
     * @param String $condition The where condition.
     * @param Mixed $queryData The associated query data.
     * @return Mixed The query.
     *
     * @details
     * $queryData is only $value or $array.
     */
    public function orWhere($condition, $queryData = null)
    {
        return $this->where(' OR '.$condition, $queryData);
    }
	
	/**
     * @brief Generates the where query part.
     * @return String The where query part.
     */
    protected function generateWhere()
    {
		if($this->where == '')
		{
			return '';
		}
		if(strpos($this->where, ' AND ') === 0)
		{
			$this->where = substr($this->where, 5);
		}
		elseif(strpos($this->where, ' OR ') === 0)
		{
			$this->where = substr($this->where, 4);
		}
		return ' WHERE '.$this->getFieldName($this->where);
	}
}

?>