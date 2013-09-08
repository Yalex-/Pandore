<?php

namespace Project\DataSources\Mercure\QueryClause;

/**
 * @brief The from clause of the query.
 */
trait FromClause
{
    use Clause;
    
    /**
     * @brief The classes in from clause of the query.
     * @var Array.
     */
    protected $from = array();

    /**
     * @brief Adds classes in from clause of the query.
     * @param Mixed $classesName The classes names.
	 * @return Mixed The query.
     *
     * @details
     * Use :
     * - from('Class').
     * - from('Class1', 'Class2').
     * - from(array('Class1', 'Class2')).
     */
    public function from($classesName)
    {
        if(!is_array($classesName))
        {
            $classesName = func_get_args();
        }
        foreach($classesName as $className)
        {
            $this->from[] = $className;
        }
        return $this;
    }
    
    /**
     * @brief Generates the query from part.
     * @return String The query from part.
     */
    protected function generateFrom()
    {
        $from = '';
        foreach($this->from as $className)
        {
            $from .= $this->addClass($className).', ';
        }
        if($from == '')
        {
            return '';
        }
        return ' FROM '.substr($from, 0, -2);
    }
}

?>