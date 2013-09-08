<?php

namespace Kernel\Core;

use Kernel\Services as Services;

/**
 * @brief This abstract class defines the minimal requirements for models to ensure synchronization with data sources.
 *
 * @details
 * This synchronization revolves around the CRUD methods, where :
 * - C means Create which is associated with the insert method.
 * - R means Read which is associated with the load method.
 * - U means Update which is associated with the update method.
 * - D means Delete which is associated with the remove method.
 *
 * @see Kernel::Core::MDSBridge.
 * @see Kernel::Core::Tools.
 */
abstract class Model
{
    use Services\Tools;

    /**
     * @brief Inserts model object in the data source.
     * @param String $sourceName The data source name.
     * @return Bool Whether the inserting operation is successful.
     */
    public function insert($sourceName = '')
    {
        return MDSBridge::insert($this, $sourceName);
    }
    
    /**
     * @brief Loads model object from data source.
     * @param String $sourceName The data source name.
     */
    public function load($sourceName = '')
    {
        MDSBridge::load($this, $sourceName);
    }
    
    /**
     * @brief Removes data in data source associated with the model object.
     * @param String $sourceName The data source name.
     * @return Bool Whether the removing operation is successful.
     */
    public function remove($sourceName = '')
    {
        return MDSBridge::remove($this, $sourceName);
    }
    
    /**
     * @brief Updates data source from model object.
     * @param String $sourceName The data source name.
     * @return Bool Whether the updating operation is successful.
     */
    public function update($sourceName = '')
    {
        return MDSBridge::update($this, $sourceName);
    }
}

?>