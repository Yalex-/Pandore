<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;
use Kernel\Services as Services;

/**
 * @brief This abstract class defines the minimal requirements for plugins.
 *
 * @details
 * Plugins follow the observer pattern using their update method.
 * About it, plugins are essential elements of the global dispatch through the definition of the pre and post dispatch method, which are automatically called via the update method.
 *
 * Smart variables access :
 * - $this->config : get the configuration,
 * - $this->pluginsManager : get the plugins manager,
 * - $this->request : get the request,
 * - $this->response : get the response.
 *
 * @see Kernel::Core::PluginsManager.
 * @see Kernel::Core::Request.
 * @see Kernel::Core::Response.
 * @see Kernel::Services::IniParser.
 * @see Kernel::Services::Tools.
 */
abstract class Plugin
{
    use Services\Tools;

    /**
     * @brief The inaccessible properties.
     * @var ArrayObject.
     */
    private $data;
    
    /**
     * @brief Constructor.
     * @param Kernel::Core::Request $request The request.
     * @param Kernel::Core::Response $response The response.
     * @param Kernel::Services::IniParser $config The configuration.
     * @param Kernel::Core::PluginsManager $pluginsManager The plugins manager.
     */
    public function __construct(Request $request, Response $response, Services\IniParser $config, PluginsManager $pluginsManager)
    {
        $this->data = new \ArrayObject();
        $this->data->offsetSet('config', $config);
        $this->data->offsetSet('pluginsManager', $pluginsManager);
        $this->data->offsetSet('request', $request);
        $this->data->offsetSet('response', $response);
        
        $this->init();
    }

    /**
     * @brief Reads data from inaccessible properties.
     * @param String $key The key.
     * @return Mixed The value.
     *
     * @exception Kernel::Exceptions::BadKeyException When the key doesn't exist.
     */
    public function __get($key)
    {
        if(!array_key_exists($key, $this->data))
        {
            throw new Exceptions\BadKeyException('The key "'.$key.'" doesn\'t exist.');
        }
        return $this->data[$key];
    }
    
    /**
     * @brief Executes step if make sense. 
     * @param String $step The step or the plugin method name.
     */
    public function update($step)
    {
        if($this->checkAction($step))
        {
            $this->$step();
        }
    }

    /**
     * @brief Initializes plugin.
     */
    protected function init() {}

    /**
     * @brief Notifies plugins.
     * @param String $step The plugins step.
     * @param Array $plugins The affected plugins.
     */
    protected function notify($step, $plugins = array())
    {
        $this->pluginsManager->notify($step, $plugins);
    }
    
    /**
     * @brief Checks whether method exists.
     * @param String $methodName The method name.
     * @return Bool Whether the method exists.
     */
    private function checkAction($methodName)
    {
        $reflexion = new \ReflectionClass($this);
        return $reflexion->hasMethod($methodName);
    }
}

?>