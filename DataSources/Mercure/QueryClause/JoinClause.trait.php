<?php

namespace Project\DataSources\Mercure\QueryClause;

/**
 * @brief The join clause of the query.
 */
trait JoinClause
{
    use Clause;
    
    /**
     * @brief The join array.
     * @var Array.
     */
    protected $join = array();

    /**
     * @brief Adds cross join clause to the query.
     * @param String $className The class name.
     * @return Mixed The query.
     */
    public function crossJoin($className)
    {
        return $this->addJoin('CROSS JOIN', $className);
    }
    
    /**
     * @brief Adds inner join clause to the query.
     * @param String $className The class name.
     * @return Mixed The query.
     */
    public function innerJoin($className)
    {
        return $this->addJoin('INNER JOIN', $className);
    }

    /**
     * @brief Adds join clause to the query.
     * @param String $className The class name.
     * @return Mixed The query.
     */
    public function join($className)
    {
        return $this->addJoin('JOIN', $className);
    }

    /**
     * @brief Adds left join clause to the query.
     * @param String $className The class name.
     * @return Mixed The query.
     */
    public function leftJoin($className)
    {
        return $this->addJoin('LEFT JOIN', $className);
    }
    
    /**
     * @brief Adds left outer join clause to the query.
     * @param String $className The class name.
     * @return Mixed The query.
     */
    public function leftOuterJoin($className)
    {
        return $this->addJoin('LEFT OUTER JOIN', $className);
    }

    /**
     * @brief Adds natural left join clause to the query.
     * @param String $className The class name.
     * @return Mixed The query.
     */
    public function naturalLeftJoin($className)
    {
        return $this->addJoin('NATURAL LEFT JOIN', $className);
    }
    
    /**
     * @brief Adds natural left outer join clause to the query.
     * @param String $className The class name.
     * @return Mixed The query.
     */
    public function naturalLeftOuterJoin($className)
    {
        return $this->addJoin('NATURAL LEFT OUTER JOIN', $className);
    }
    
    /**
     * @brief Adds natural right join clause to the query.
     * @param String $className The class name.
     * @return Mixed The query.
     */
    public function naturalRightJoin($className)
    {
        return $this->addJoin('NATURAL RIGHT JOIN', $className);
    }
    
    /**
     * @brief Adds natural right outer join clause to the query.
     * @param String $className The class name.
     * @return Mixed The query.
     */
    public function naturalRightOuterJoin($className)
    {
        return $this->addJoin('NATURAL RIGHT OUTER JOIN', $className);
    }

    /**
     * @brief Adds right join clause to the query.
     * @param String $className The class name.
     * @return Mixed The query.
     */
    public function rightJoin($className)
    {
        return $this->addJoin('RIGHT JOIN', $className);
    }
    
    /**
     * @brief Adds right outer join clause to the query.
     * @param String $className The class name.
     * @return Mixed The query.
     */
    public function rightOuterJoin($className)
    {
        return $this->addJoin('RIGHT OUTER JOIN', $className);
    }
    
    /**
     * @brief Adds straight join clause to the query.
     * @param String $className The class name.
     * @return Mixed The query.
     */
    public function straightJoin($className)
    {
        return $this->addJoin('STRAIGHT_JOIN', $className);
    }
        
    /**
     * @brief Adds 'on' condition to the query.
     * @param String $condition The join condition.
     * @param Mixed $queryData The associated query data.
	 * @return Mixed The query.
     *
     * @details
     * $queryData is only $value or $array.
     */
    public function on($condition, $queryData = null)
    {
		return $this->addOn('ON', $condition, $queryData);
    }
    
    /**
     * @brief Adds 'or on' condition to the query.
     * @param String $condition The join condition.
     * @param Mixed $queryData The associated query data.
     * @return Mixed The query.
     *
     * @details
     * $queryData is only $value or $array.
     */
    public function orOn($condition, $queryData = null)
    {
        return $this->addOn('OR', $condition, $queryData);
    }
    
    /**
     * @brief Adds 'and on' condition to the query.
     * @param String $condition The join condition.
     * @param Mixed $queryData The associated query data.
     * @return Mixed The query.
     *
     * @details
     * $queryData is only $value or $array.
     */
    public function andOn($condition, $queryData = null)
    {
		return $this->addOn('AND', $condition, $queryData);
    }
    
    /**
     * @brief Adds using to the join.
     * @param Mixed $attributes The restricted attributes of the natural join.
     * @return Mixed The query.
     *
     * @details
     * Use :
     * - using('attr1', 'attr2').
     * - using(array('attr1', 'attr2')).
     */
    public function using($attributes)
    {
        if(!is_array($attributes))
        {
            $attributes = func_get_args();
        }
        $this->join[] = array('type' => 'USING', 'value' => '('.implode(', ', $attributes).')');
        return $this;
    }
    
    /**
     * @brief Generates the query join part.
     * @return String The query join part.
     */
    protected function generateJoin()
    {
        $join = '';
        foreach($this->join as $part)
        {
            if(stripos($part['type'], 'JOIN') !== false)
            {
                $join .= ' '.$part['type'].' '.$this->addClass($part['value']);
            }
            else
            {
                $join .= ' '.$part['type'].' '.$this->getFieldName($part['value']);
            }
        }
        return $join;
    }
    
    /**
     * @brief Adds join clause to the query.
     * @param String $type The join type.
     * @param String $className The associated class name.
     * @return Mixed The query.
     */
    private function addJoin($type, $className)
    {
        $this->join[] = array('type' => $type, 'value' =>$className);
        return $this;
    }

    /**
     * @brief Adds join condition to the query.
     * @param String $type The condition type.
     * @param String $condition The join condition.
     * @param Mixed $queryData The associated query data.
     * @return Mixed The query.
     */
    private function addOn($type, $condition, $queryData = null)
    {
        $this->join[] = array('type' => $type, 'value' =>$condition);
        if($queryData !== null)
        {
            $this->addArgument($queryData);
        }
        return $this;
    }
}

?>