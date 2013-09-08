<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;
use Kernel\Services as Services;

/**
 * @brief This class manages plugins.
 *
 * @details
 * The plugin manager loads plugins from config file and updates proper plugins when notifications is received.
 * For convenience, the pre and post dispath notifications are already defined.
 *
 * @see Kernel::Core::Request.
 * @see Kernel::Core::Response.
 * @see Kernel::Core::Plugin.
 * @see Kernel::Services::IniParser.
 */
class PluginsManager
{
    /**
     * @brief The kernel plugins namespace.
     * @var String.
     */
    private static $kernelNamespace = 'Kernel\Plugins';
    /**
     * @brief The project plugins namespace.
     * @var String.
     */
    private static $projectNamespace = 'Project\Plugins';

    /**
     * @brief The configuration.
     * @var Kernel::Services::IniParser.
     */
    private $config;
    /**
     * @brief The plugins array.
     * @var ArrayObject.
     */
    private $plugins;
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
     */
    public function __construct(Request $request, Response $response, Services\IniParser $config)
    {
        $this->config = $config;
        $this->request = $request;
        $this->response = $response;
        
        $this->plugins = new \ArrayObject();
    }

    /**
     * @brief Whether plugin exists.
     * @param String $pluginName The plugin name.
     * @return Bool Whether plugin exists.
     */
    public function has($pluginName)
    {
        return $this->plugins->offsetExists($pluginName);
    }

    /**
     * @brief Loads plugins from the configuration file.
     */
    public function loadPlugins()
    {
        // Creates the system plugins starting with the exceptions hander plugin.
        try {
            $pluginsName = $this->config->getArray('systemPlugins');
        } catch(\Exception $exception) {
            $pluginsName = new \ArrayObject();
        }

        if(in_array('ExceptionsHandler', $pluginsName->getArrayCopy()))
        {
            $pluginClassName = self::$kernelNamespace.'\ExceptionsHandler\ExceptionsHandler';

            $plugin = Factory\PluginsFactory::get($pluginClassName, $this->request, $this->response, $this->config, $this);
            $this->plugins->offsetSet('ExceptionsHandler', $plugin);
        }

        $plugins = new \ArrayObject();

        foreach($pluginsName as $pluginName)
        {
            if($pluginName == 'ExceptionsHandler')
            {
                continue;
            }

            $pluginClassName = self::$kernelNamespace.'\\'.ucfirst($pluginName).'\\'.ucfirst($pluginName);

            $plugin = Factory\PluginsFactory::get($pluginClassName, $this->request, $this->response, $this->config, $this);
            $plugins->offsetSet($pluginName, $plugin);
        }

        if(in_array('ExceptionsHandler', $pluginsName->getArrayCopy()))
        {
            $plugins->offsetSet('ExceptionsHandler', $this->plugins->offsetGet('ExceptionsHandler'));
        }
        $this->plugins = $plugins;

        // Creates the project plugins.
        try {
            $pluginsName = $this->config->getArray('plugins');
        } catch(\Exception $exception) {
            $pluginsName = new \ArrayObject();
        }

        $plugins = new \ArrayObject();
        foreach($pluginsName as $pluginName)
        {
            $pluginClassName = self::$projectNamespace.'\\'.ucfirst($pluginName).'\\'.ucfirst($pluginName);

            $plugin = Factory\PluginsFactory::get($pluginClassName, $this->request, $this->response, $this->config, $this);
            $plugins->offsetSet($pluginName, $plugin);
        }

        foreach($this->plugins as $pluginName => $plugin)
        {
            if($plugins->offsetExists($pluginName))
            {
                throw new Exceptions\BadKeyException('The plugin "'.$pluginName.'" is already declared as system plugin.');
            }

            $plugins->offsetSet($pluginName, $plugin);
        }

        $this->plugins = $plugins;
    }
    
    /**
     * @brief Notifies proper plugins.
     * @param String $step The plugins step.
     * @param Array $plugins The proper plugins.
     */
    public function notify($step, $plugins = array())
    {
        if(empty($plugins))
        {
            $iterator = $this->plugins->getIterator();
            while($iterator->valid())
            {
                $iterator->current()->update($step);
                $iterator->next();
            }
        }
        elseif(is_array($plugins))
        {
            $iterator = $this->plugins->getIterator();
            while($iterator->valid())
            {
                if(in_array($iterator->key(), $plugins))
                {
                    $iterator->current()->update($step);
                }
                $iterator->next();
            }
        }
    }

    /**
     * @brief Executes plugins pre dispatch.
     */
    public function preDispatch()
    {
        $this->notify('preDispatch');
    }
    
    /**
     * @brief Executes plugins post dispatch.
     */
    public function postDispatch()
    {
        $this->notify('postDispatch');
    }
}

?>