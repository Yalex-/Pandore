<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;
use Kernel\Plugins as Plugins;
use Kernel\Services as Services;

/**
 * @brief This controller provides a centralized entry point for handling requests.
 * 
 * @details
 * First, the front controller loads the project configuration and builds the main objects like the main request, the main response and the plugins manager.
 * Then, the front controller dispatches the request through the dispatcher between the plugins pre and post dispatch operations. If something bad occurs during this step, the exception handler is called which may modify the main response.
 * Finally, the front controller sends the response to the web client.
 *
 * @see Kernel::Core::DataSourceProxy.
 * @see Kernel::Core::Dispatcher.
 * @see Kernel::Core::PluginsManager.
 * @see Kernel::Core::Request.
 * @see Kernel::Core::Response.
 * @see Kernel::Core::Router.
 * @see Kernel::Plugins::ExceptionsHandler.
 * @see Kernel::Services::IniParser.
 */
class FrontController
{
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
     * @param String $configFilePath The configuration file path.
     */ 
    public function __construct($configFilePath)
    {
        $this->config = new Services\IniParser($configFilePath);
        
        $router = new Router($_GET, $this->config);
        
        $this->request = new Request($router, $_POST, $_COOKIE, $_FILES, $_SERVER);
        
        $this->response = new Response($this->config);

        $this->pluginsManager = new PluginsManager($this->request, $this->response, $this->config);
    }
    
    /**
     * @brief Dispatches the request.
     */
    public function dispatch()
    {
        try {
            $this->pluginsManager->loadPlugins();

            if($this->request->getModuleName() === '')
            {
                throw new Exceptions\BadKeyException('The key "module" doesn\'t exist in the configuration file or isn\'t valid.');
            }

            try {
                $sources = $this->config->getArray('sources');
            } catch(\Exception $exception) {
                $sources = new \ArrayObject();
            }

            try {
                $DSNs = $this->config->getArray('dsns');
            } catch(\Exception $exception) {
                $DSNs = new \ArrayObject();
            }

            DataSourceProxy::init($sources, $DSNs);

            $this->pluginsManager->preDispatch();
        
            $dispatcher = new Dispatcher($this->request, $this->response, $this->config, $this->pluginsManager);
            $dispatcher->dispatch();

            $this->pluginsManager->postDispatch();
        } catch(\Exception $exception) {
            if($this->pluginsManager->has('ExceptionsHandler'))
            {
                Plugins\ExceptionsHandler\ExceptionsHandler::$exception = $exception;
                $this->pluginsManager->notify('execute', array('ExceptionsHandler'));
            }
            else
            {
                throw $exception;
            }
        }
    }

    /**
     * @brief Sends the main response.
     */
    public function sendResponse()
    {
        $this->response->send();
    }
}

?>