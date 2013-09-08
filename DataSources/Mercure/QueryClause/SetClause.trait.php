<?php

namespace Project\DataSources\Mercure\QueryClause;

/**
 * @brief The set clause of the query.
 */
trait SetClause
{
	use Clause;
	
    /**
     * @brief The set string.
     * @var String.
     */
    protected $set = '';
    
    /**
     * @brief Adds set clause to the query.
     * @param String $attribute The attribute name.
     * @param Mixed $queryData The associated query data.
     * @param String $rule The set rule with pdo format.
	 * @return Mixed The query.
     *
     * @details
     * $queryData is only $value or $array.
     *
     * @details
     * Use :
     * - set('name', 'newName').
     * - set('value', 2, 'value * ?').
     */
    public function set($attribute, $queryData, $rule = '?')
    {
		$this->set .= $attribute .' = '.$rule.', ';
		$this->addArgument($queryData);
        return $this;
	}
	
	/**
     * @brief Generates the query set part.
     * @return String The query set part.
     */
    protected function generateSet()
    {
        if($this->set == '')
        {
            return '';
        }
		return ' SET '.$this->getFieldName(substr($this->set, 0, -2));
	}
}

?>