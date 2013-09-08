<?php

namespace Kernel\Core;

/**
 * @brief This interface defines the minimal requirements for data sources to ensure the link with model objects.
 *
 * @details
 * This link revolves around the CRUD methods, where :
 * - C means Create which is associated with the insertOne method.
 * - R means Read which is associated with the selectOne method.
 * - U means Update which is associated with the updateOne method.
 * - D means Delete which is associated with the deleteOne method.
 *
 * @see Kernel::Core::Model.
 */
interface IDataSource
{
    /**
     * @brief Constructor.
     * @param String $DSN The DSN.
     * 
     * @see http://php.net/manual/en/ref.pdo-mysql.connection.php.
     */
    public function __construct($DSN);

    /**
     * @brief Deletes data from model object.
     * @param Kernel::Core::Model $model The model object.
     * @return Bool Whether the operation is successful.
     */
    public function deleteOne($model);

    /**
     * @brief Creates data from model object.
     * @param Kernel::Core::Model $model The model object.
     * @return Bool Whether the operation is successful.
     *
     * @details Insertion can fill model object like an id setting for instance.
     */
    public function insertOne(&$model);
    
    /**
     * @brief Reads data and fills model object.
     * @param Kernel::Core::Model $model The model object.
     */
    public function selectOne(&$model);
    
    /**
     * @brief Updates data from model object.
     * @param Kernel::Core::Model $model The model object.
     * @return Bool Whether the operation is successful.
     */
    public function updateOne($model);
}

?>