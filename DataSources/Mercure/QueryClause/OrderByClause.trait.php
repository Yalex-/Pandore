<?php

namespace Project\DataSources\Mercure\QueryClause;

/**
 * @brief The order by clause of the query.
 */
trait OrderByClause
{
    use Clause;
	
    /**
     * @brief The order by string.
     * @var String.
     */
	protected $orderBy = '';
    
	/** 
     * @brief Adds 'order by asc' clause to the query.
     * @param $attribute The associated attribute.
	 * @return Mixed The query.
     */
    public function orderByAsc($attribute)
    {
		$this->orderBy .= $attribute.' ASC, ';
        return $this;
    }
	
	/** 
     * @brief Adds 'order by desc' clause to the query.
     * @param $attribute The associated attribute.
     * @return Mixed The query.
     */
	public function orderByDesc($attribute)
    {
		$this->orderBy .= $attribute.' DESC, ';
        return $this;
    }
	
	/**
     * @brief Generates the order by query part.
     * @return String The order by query part.
     */
    protected function generateOrderBy()
    {
        if($this->orderBy == '')
        {
            return '';
        }
		return ' ORDER BY '.substr($this->getFieldName($this->orderBy), 0, -2);
	}
}

?>