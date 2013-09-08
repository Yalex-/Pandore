<?php

namespace Project\DataSources\Mercure\QueryClause;

/**
 * @brief The limit clause of the query.
 */
trait LimitClause
{
	use Clause;

    /**
     * @brief The limit string.
     * @var String.
     */
    protected $limit = '';
    
    /**
     * @brief Adds limit clause to the query.
     * @param Int $nb The number of result needed.
     * @param Int $start The start of the limit.
     * @return Mixed The query.
     */
    public function limit($nb, $start = 0)
    {
        if($start == 0)
		{
            $this->limit .= intval($nb);
		}
        else
        {
            $this->limit .= intval($start).', '.intval($nb);
        }
		return $this;
    }
	
	/**
     * @brief Generates the query limit part.
     * @return String The query limit part.
     */
    protected function generateLimit()
    {
        if($this->limit == '')
        {
            return '';
        }
		return ' LIMIT '.$this->limit;
	}
}

?>