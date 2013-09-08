<?php

namespace Kernel\Core\Factory;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class implements factory pattern for data sources building.
 *
 * @see Kernel::Core::IDataSource.
 */
class DataSourcesFactory
{
    /**
     * @brief The data source interface name.
     * @var String.
     */
    private static $dataSourceInterfaceName = 'Kernel\Core\IDataSource';
    
    /**
     * @brief Builds data source from class name.
     * @param String $className The class name of data source.
     * @param String $DSN The DSN.
     * @return Kernel::Core::IDataSource The data source.
     *
     * @exception Kernel::Exceptions::DataSourcesFactoryException When the desired class doesn't exist.
     * @exception Kernel::Exceptions::DataSourcesFactoryException When the desired class doesn't implement the data source interface.
     * @exception Kernel::Exceptions::DataSourcesFactoryException When the data source isn't instantiable.
     */
    public static function get($className, $DSN)
    {        
        try {
            $reflectionClass = new \ReflectionClass($className);
        } catch(\Exception $e) {
            throw new Exceptions\DataSourcesFactoryException($className.' doesn\'t exist.');
        }
        
        if(!$reflectionClass->isSubclassOf(self::$dataSourceInterfaceName))
        {
            throw new Exceptions\DataSourcesFactoryException($className.' doesn\'t implement the data source interface.');
        }
        
        if(!$reflectionClass->isInstantiable())
        {
            throw new Exceptions\DataSourcesFactoryException($className.' isn\'t instantiable.');
        }
        
        return $reflectionClass->newInstance($DSN);
    }
}

?>