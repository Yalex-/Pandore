<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;
use Kernel\Services as Services;

/**
 * @brief This class builds route from $_GET array which are set by the .htaccess file.
 *
 * @details
 * $_GET array is filled with only one value in order to have a complete, clean and simple url rewriting. Valid Pandore url is defined as follows :
 * http://www.foo.com/moduleName/actionName/params/ where params are like key1/value1/key2/value2/.../keyN/valueN.
 *
 * @see Kernel::Services::IniParser.
 */
class Router
{
    /**
     * @brief The action name.
     * @var String.
     */
    private $actionName;
    /**
     * @brief The module name.
     * @var String.
     */
    private $moduleName;
    /**
     * @brief The parameters array.
     * @var ArrayObject.
     */
    private $parameters;

    /**
     * @brief Constructor.
     * @param Array $get The $_GET array.
     * @param Kernel::Services::IniParser $config The configuration.
     */
    public function __construct($get, Services\IniParser $config)
    {
        try {
            $this->moduleName = $config->getValue('module');
        } catch(\Exception $exception) {
            $this->moduleName = '';
        }
        $this->actionName = '';
        $this->parameters = new \ArrayObject();
        
        if(is_array($get) && isset($get['q']) && !empty($get['q']))
        {
            $request = $get['q'];
            $request .= ($request[strlen($request) - 1] == '/') ? '' : '/';
            $explode = explode('/', $request);
            $this->moduleName = !empty($explode[0]) ? $explode[0] : $this->moduleName;
            $this->actionName = !empty($explode[1]) ? $explode[1] : $this->actionName;
            for($index = 2; $index < count($explode); $index = $index + 2)
            {
                if(isset($explode[$index + 1]))
                {
                    $this->parameters[$explode[$index]] = $explode[$index + 1];
                }
            }
        }
    }
    
    /**
     * @brief Get action name.
     * @return String The action name.
     */
    public function getActionName()
    {
        return $this->actionName;
    }
    
    /**
     * @brief Get module name.
     * @return String The module name.
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }
    
    /**
     * @brief Get parameters array.
     * @return ArrayObject The parameters array.
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}

?>