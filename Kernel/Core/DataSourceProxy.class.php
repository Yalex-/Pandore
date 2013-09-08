<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class loads and allows to get the data sources.
 *
 * @details
 * Valid data sources are objects which implement IDataSource interface.
 *
 * @see Kernel::Core::IDataSource.
 */
class DataSourceProxy
{
    /**
     * @brief The name of the default data source.
     */
    const DEFAULT_DS_NAME = 'Default';

    /**
     * @brief The project data sources namespace.
     * @var String.
     */
    private static $projectNamespace = 'Project\DataSources';
    /**
     * @brief The data sources array.
     * @var ArrayObject.
     */
    private static $sources = null;
    
    /**
     * @brief Initializes data sources which are declared in the configuration file.
     * @param ArrayObject $sources The data sources array.
     * @param ArrayObject $DSNs The DSN array associated with the data sources array.
     *
     * @exception DataSourceProxyException When the number of data source and DSN doesn't match.
     */
    public static function init(\ArrayObject $sources, \ArrayObject $DSNs)
    {
        if($sources->count() == $DSNs->count())
        {
            foreach($sources as $alias => $sourceName)
            {
                $sourceClassName = self::$projectNamespace.'\\'.ucfirst($sourceName).'\\'.ucfirst($sourceName);

                $dataSource = Factory\DataSourcesFactory::get($sourceClassName, $DSNs[$alias]);
                self::add($alias, $dataSource);
            }
        }
        else
        {
            throw new Exceptions\DataSourceProxyException('The number of source "'.$sources->count().'" and the number of DSN "'.$DSNs->count().'" doesn\'t match.');
        }
    }
    
    /**
     * @brief Adds data source.
     * @param String $name The name of data source.
     * @param Kernel::Core::IDataSource $dataSource The data source.
     */
    public static function add($name, IDataSource $dataSource)
    {
        $sources = self::getSources();
        $sources->offsetSet($name, $dataSource);
        self::$sources = $sources;
    }
    
    /**
     * @brief Get data source from its name.
     * @param String $name The name of data source.
     * @return Kernel::Core::IDataSource The data source.
     *
     * @details
     * In case of empty name, the default source is returned.
     *
     * @exception Kernel::Exceptions::DataSourceProxyException When DataSourceProxy doesn't know the desired data source.
     */
    public static function get($name = '')
    {
        $name = !empty($name) ? $name : self::DEFAULT_DS_NAME;
        $sources = self::getSources();
        if(!self::getSources()->offsetExists($name))
        {
            throw new Exceptions\DataSourceProxyException('DataSourceProxy doesn\'t know the data source called '.$name.'.');
        }
        return self::getSources()->offsetGet($name);
    }
    
    /**
     * @brief Get data sources array.
     * @return ArrayObject The data sources array.
     */
    private static function getSources()
    {
        if(self::$sources === null)
        {
            self::$sources = new \ArrayObject();
        }
        return self::$sources;
    }
}

?>