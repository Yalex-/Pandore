<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;
use Kernel\Services as Services;

/**
 * @brief This controller dispatches completely the requested module action.
 *
 * @details
 * 404 http code is set to the response in case of invalid module.
 * Default module action is executed in case of invalid action.
 *
 * @see Kernel::Core::Module.
 * @see Kernel::Core::PluginsManager.
 * @see Kernel::Core::Request.
 * @see Kernel::Core::Response.
 * @see Kernel::Core::Factory::ModulesFactory.
 * @see Kernel::Services::IniParser.
 */
class Dispatcher
{
    /**
     * @brief The project modules namespace.
     * @var String.
     */
    private static $projectNamespace = 'Project\Modules';

    /**
     * @brief The configuration.
     * @var Kernel::Services::IniParser.
     */
    private $config;
    /**
     * @brief The plugins manager.
     * @var Kernel::Core::PluginsManager.
     */
    private $pluginsManager;
    /**
     * @brief The request.
     * @var Kernel::Core::Request.
     */
    private $request;
    /**
     * @brief The response.
     * @var Kernel::Core::Response.
     */
    private $response;
    
    /**
     * @brief Constructor.
     * @param Kernel::Core::Request $request The request.
     * @param Kernel::Core::Response $response The response.
     * @param Kernel::Services::IniParser $config The configuration.
     * @param Kernel::Core::PluginsManager $pluginsManager The plugins manager.
     */
    public function __construct(Request $request, Response $response, Services\IniParser $config, PluginsManager $pluginsManager)
    {
        $this->request = $request;
        $this->response = $response;
        $this->config = $config;
        $this->pluginsManager = $pluginsManager;
    }
    
    /**
     * @brief Dispatches completely the requested module action.
     */
    public function dispatch()
    {
        $moduleClassName = self::$projectNamespace.'\\'.ucfirst($this->request->getModuleName()).'\\'.ucfirst($this->request->getModuleName());

        try {
            $module = Factory\ModulesFactory::get($moduleClassName, $this->request, $this->response, $this->config, $this->pluginsManager);
        } catch(Exceptions\ModulesFactoryException $e) {
            $this->response->setHttpStatusCode(404);
        }
        
        $actionName = $this->checkAction($module, $this->request->getActionName()) ? $this->request->getActionName() : Module::DEFAULT_ACTION_NAME; 
        
        $module->preExecute();
        $module->execute($actionName);
        $module->postExecute();
        
        $module->finalize();
    }
    
    /**
     * @brief Checks validity of the given module action.
     * @param Kernel::Core::Module $module The module.
     * @param String $actionName The action name.
     * @return Bool Whether the action is valid.
     */
    private function checkAction(Module $module, $actionName)
    {
        $reflexion = new \ReflectionClass($module);
        $actionNameFormated = Module::formatActionName($actionName);
        return $reflexion->hasMethod($actionNameFormated);
    }
}

?>