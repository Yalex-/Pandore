<?php

namespace Project\DataSources\Mercure\QueryClause\ValuesClause;

/**
 * @brief The values clause of the query.
 */
//trait ValuesClause
//{
    use Clause;
	
    /**
     * @brief The values string.
     * @var String.
     */
	private $values = '';
	
    /**
     * @brief Adds values clause to the query.
     * @param Mixed $values The associated values.
     * @return Mixed The query.
     *
     * @details
     * Use :
     * - values($value).
	 * - values($value1, $value2).
	 * - values($value1, array(function(?), $value2), ...).
	 * - values(array($value1, $value2)).
	 * - values(array($value1, array(function(?), $value2), ...)).
	 */
    public function values($values)
    {
		if(!is_array($values))
		{
			$values = func_get_args();
		}
		$this->values .= '( ';
		foreach($values as $value)
		{
			if(is_array($value))
			{
				$this->values .= $value[1].', ';
			}
			else
			{
				$this->values .= '?, ';
			}
			$this->addArgument($value);	
		}
		$this->values = substr($this->values, 0, -2).'), ';
        return $this;
    }
	
	/**
     * @brief Generates the query values part.
     * @return String The query values part.
     */
    protected function generateValues()
    {
		return ' VALUES '.substr($this->values, 0, -2);
	}
//}

?>