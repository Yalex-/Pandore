<?php

namespace Kernel\Core\Factory;

use Kernel\Core as Core;
use Kernel\Exceptions as Exceptions;
use Kernel\Services as Services;

/**
 * @brief This class implements factory pattern for modules building.
 *
 * @see Kernel::Core::Module.
 * @see Kernel::Core::PluginsManager.
 * @see Kernel::Core::Request.
 * @see Kernel::Core::Response.
 * @see Kernel::Services::IniParser.
 */
class ModulesFactory
{
    /**
     * @brief The module class name.
     * @var String.
     */
    private static $moduleClassName = 'Kernel\Core\Module';
    
    /**
     * @brief Builds module from class name.
     * @param String $className The module class name.
     * @param Kernel::Core::Request $request The request.
     * @param Kernel::Core::Response $response The response.
     * @param Kernel::Services::IniParser $config The configuration.
     * @param Kernel::Core::PluginsManager $pluginsManager The pluginsManager.
     * @return Kernel::Core::Module The module.
     *
     * @exception Kernel::Exceptions::ModulesFactoryException When the desired class doesn't exist.
     * @exception Kernel::Exceptions::ModulesFactoryException When the desired class doesn't extend the module class.
     * @exception Kernel::Exceptions::ModulesFactoryException When the module isn't instantiable.
     */

    public static function get($className, Core\Request $request, Core\Response $response, Services\IniParser $config, Core\PluginsManager $pluginsManager)
    {        
        try {
            $reflectionClass = new \ReflectionClass($className);
        } catch(\Exception $e) {
            throw new Exceptions\ModulesFactoryException($className.' doesn\'t exist.');
        }
        
        if(!$reflectionClass->isSubclassOf(self::$moduleClassName))
        {
            throw new Exceptions\ModulesFactoryException($className.' doesn\'t extend the module class.');
        }
        
        if(!$reflectionClass->isInstantiable())
        {
            throw new Exceptions\ModulesFactoryException($className.' isn\'t instantiable.');
        }
        
        return $reflectionClass->newInstance($request, $response, $config, $pluginsManager);
    }
}

?>