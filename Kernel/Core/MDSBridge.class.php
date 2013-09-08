<?php

namespace Kernel\Core;

/**
 * @brief This class allows to link model objects and data sources.
 *
 * @details
 * This link revolves around the CRUD methods, where :
 * - C means Create which is associated with the insertOne method of data source and the insert method of model object.
 * - R means Read which is associated with the selectOne method of data source and the load method of model object.
 * - U means Update which is associated with the updateOne method of data source and the update method of model object.
 * - D means Delete which is associated with the deleteOne method of data source and the remove method of model object.
 *
 * @see Kernel::Core::DataSourceProxy.
 * @see Kernel::Core::Model.
 */
class MDSBridge
{
    /**
     * @brief Inserts model object in the data source.
     * @param Kernel::Core::Model $model The model object.
     * @param String $sourceName The data source name.
     * @return Bool Whether the operation is successful.
     *
     * @details
     * Insertion can fill model object, typically the id attribute.
     */
    public static function insert(Model &$model, $sourceName = '')
    {
        return DataSourceProxy::get($sourceName)->insertOne($model);
    }
    
    /**
     * @brief Loads model object from data source.
     * @param Kernel::Core::Model $model The model object.
     * @param String $sourceName The data source name.
     */
    public static function load(Model &$model, $sourceName = '')
    {
        DataSourceProxy::get($sourceName)->selectOne($model);
    }
    
    /**
     * @brief Removes data in data source associated with the model object.
     * @param Kernel::Core::Model $model The model object.
     * @param String $sourceName The data source name.
     * @return Bool Whether the removing operation is successful.
     */
    public static function remove(Model $model, $sourceName = '')
    {
        return DataSourceProxy::get($sourceName)->deleteOne($model);
    }

    /**
     * @brief Updates data source from model object.
     * @param Kernel::Core::Model $model The model object.
     * @param String $sourceName The data source name.
     * @return Bool Whether the updating operation is successful.
     */
    public static function update(Model $model, $sourceName = '')
    {
        return DataSourceProxy::get($sourceName)->updateOne($model);
    }
}

?>