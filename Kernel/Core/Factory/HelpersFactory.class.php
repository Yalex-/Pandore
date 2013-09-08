<?php

namespace Kernel\Core\Factory;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class implements factory pattern for helpers building.
 *
 * @see Kernel::Core::Helper.
 */
class HelpersFactory
{
    /**
     * @brief The helper class name.
     * @var String.
     */
    private static $helperClassName = 'Kernel\Core\Helper';
    
    /**
     * @brief Builds helper from class name.
     * @param String $className The helper class name.
     * @return Kernel::Core::Helper The helper.
     *
     * @exception Kernel::Exceptions::HelpersFactoryException When the desired class doesn't exist.
     * @exception Kernel::Exceptions::HelpersFactoryException When the desired class doesn't extend the helper abstract class.
     * @exception Kernel::Exceptions::HelpersFactoryException When the helper isn't instantiable.
     */

    public static function get($className)
    {        
        try {
            $reflectionClass = new \ReflectionClass($className);
        } catch(\Exception $e) {
            throw new Exceptions\HelpersFactoryException($className.' doesn\'t exist.');
        }

        if(!$reflectionClass->isSubclassOf(self::$helperClassName))
        {
            throw new Exceptions\HelpersFactoryException($className.' doesn\'t extends the helper abstract class.');
        }
        
        if(!$reflectionClass->isInstantiable())
        {
            throw new Exceptions\HelpersFactoryException($className.' isn\'t instantiable.');
        }
        
        return $reflectionClass->newInstance();
    }
}

?>