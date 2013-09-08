<?php

namespace Project\DataSources\Mercure\ObjectParser;

use Kernel\Services as Services;
use Project\DataSources\Mercure as Mercure;
use Project\DataSources\Mercure\Exceptions as Exceptions;

/**
 * @brief This class implements factory pattern for object parsers building.
 *
 * @see Kernel::Services::IniParser.
 * @see Mercure;:Mercure.
 * @see Mercure::ObjectParser::IObjectParser.
 */
class ObjectParserFactory
{
    /**
     * @brief The object parser interface name.
     * @var String.
     */
    private static $dataSourceInterfaceName = 'Project\DataSources\Mercure\ObjectParser\IObjectParser';

    /**
     * @brief Builds object parser from class name.
     * @param String $className The object parser class name.
     * @param Mercure::Mercure $mercure The Mercure object.
     * @param Kernel::Services::IniParser $config The Mercure configuration.
     * @return Mercure::ObjectParser::IObjectParser The object parser.
     *
     * @exception Mercure::Exceptions::ObjectParserFactoryException When the desired class doesn't exist.
     * @exception Mercure::Exceptions::ObjectParserFactoryException When the desired class doesn't implement the object parser interface.
     * @exception Mercure::Exceptions::ObjectParserFactoryException When the object parser isn't instantiable.
     */
    public static function get($className, Mercure\Mercure $mercure, Services\IniParser $config)
    {
        try {
            $reflectionClass = new \ReflectionClass($className);
        } catch(\Exception $e) {
            throw new Exceptions\ObjectParserFactoryException($objectParserName.' doesn\'t exist.');
        }
        
        if(!$reflectionClass->isSubclassOf(self::$dataSourceInterfaceName))
        {
            throw new Exceptions\ObjectParserFactoryException($objectParserName.' doesn\'t implement the object parser interface.');
        }
        
        if(!$reflectionClass->isInstantiable())
        {
            throw new Exceptions\ObjectParserFactoryException($objectParserName.' isn\'t instantiable.');
        }
        
        return $reflectionClass->newInstance($mercure, $config);
    }
}

?>