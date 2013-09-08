<?php

namespace Project\DataSources\Mercure\QueryClause;

/**
 * @brief The 'interface' of the query clause.
 */
trait Clause
{
    /**
     * @brief Adds attribute to the query.
     * @param String $attribute The attribute name.
     * @return String The field name.
     */
    protected abstract function addAttribute($attribute);

    /**
     * @brief Adds arguments to the query.
     * @param Mixed $arg The query data.
     */
    protected abstract function addArgument($arg);

    /**
     * @brief Adds class to the query.
     * @param String $class The class name.
     * @return String The table name.
     */
    protected abstract function addClass($class);

    /**
     * @brief Makes field name from attribute.
     * @param String $attribute The attribute name.
     * @param Mixed $className The class name.
     * @return String The formated field name.
     */
    protected abstract function getFieldName($attribute, $className = null);
}

?>