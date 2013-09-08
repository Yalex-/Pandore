<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class automates intelligently the file inclusion using namespaces (dependencies injection pattern).
 *
 * @details
 * All files aren't automatically included but loader can dynamically include :
 * - Class files named ClassName.class.php.
 * - Interface files named InterfaceName.class.php.
 * - Task files named TasksName.task.php.
 * - Trait files named TraitName.trait.php.
 * Loader can also include all php files with the load method. In addition to the previous naming convention, the libraries can be included easily using dir/dir/(..)/Library.lib.php.
 */
class Loader
{
    /**
     * @brief Includes file associated with the undefined class.
     * @param String $name The complete name of the undefined class (with namespace).
     */
    public static function autoload($name)
    {
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $name);
        self::load($relativePath);
    }

    /**
     * @brief Includes file from its name.
     * @param String $name The file name.
     *
     * @details
     * The name is the path relative to the root directory without extension like complete name of class (with namespace).
     *
     * @exception Kernel::Exceptions::LoaderException When it's impossible to load Pandore component.
     */
    public static function load($name)
    {
        $path = ROOT_PATH.$name;

        $pathClass = $path.'.class.php';
        $pathInterface = $path.'.interface.php';
        $pathLib = $path.'.lib.php';
        $pathTrait = $path.'.trait.php';
        $pathTask = $path.'.task.php';
        $pathClassic = $path.'.php';
        
        if(file_exists($pathClass))
        {
            require_once($pathClass);
        }
        elseif(file_exists($pathTrait))
        {
            require_once($pathTrait);
        }
        elseif(file_exists($pathInterface))
        {
            require_once($pathInterface);
        }
        elseif(file_exists($pathTask))
        {
            require_once($pathTask);
        }
        elseif(file_exists($pathLib))
        {
            require_once($pathLib);
        }
        elseif(file_exists($pathClassic))
        {
            require_once($pathClassic);
        }
        else
        {
            throw new Exceptions\LoaderException('It\'s impossible to load '.$path.'.');
        }
    }
}

?>