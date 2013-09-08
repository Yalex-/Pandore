<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;
use Kernel\Plugins as Plugins;
use Kernel\Services as Services;

/**
 * @brief This controller defines the minimal requirement to define actions and execution process.
 *
 * @details
 * The execution process is composed by the following methods :
 * - init is associated with the constructor,
 * - preExecute is executed before the action,
 * - postExecute is executed after the action,
 * - finalize is associated with the end of the execution process.
 * Theses previous methods allows to redefine or adapt the execution process for each modules.
 * 
 * On the other hand, module has several protected methods. Most of them are helps in order to factorize some routines.
 *
 * Smart variables access :
 * - $this->config : get the configuration,
 * - $this->layout : get the layout aka the global view,
 * - $this->pluginsManager : get the plugins manager,
 * - $this->request : get the request,
 * - $this->response : get the response,
 * - $this->view : get the view associated with the current action.
 *
 * @see Kernel::Core::Factory::HelpersFactory.
 * @see Kernel::Core::PluginsManager.
 * @see Kernel::Core::Request.
 * @see Kernel::Core::Response.
 * @see Kernel::Core::View.
 * @see Kernel::Core::ViewRenderer.
 * @see Kernel::Plugins::LayoutRenderer.
 * @see Kernel::Services::IniParser.
 * @see Kernel::Services::Tools.
 */
abstract class Module
{
    use Services\Tools;

    /**
     * @brief The default action name.
     */
    const DEFAULT_ACTION_NAME = 'default';
    
    /**
     * @brief The inaccessible properties.
     * @var ArrayObject.
     */
    private $data;
    /**
     * @brief The module helpers.
     * @var ArrayObject.
     */
    private $helpers;
    /**
     * @brief Whether the current execution process must produces action rendering.
     * @var Bool.
     */
    private $mustRender;
    /**
     * @brief The view renderer.
     * @var Kernel::Core::ViewRenderer.
     */
    private $viewRenderer;

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
        $this->data->offsetSet('view', new View());

        if($this->pluginsManager->has('LayoutRenderer'))
        {
            $this->data->offsetSet('layout', Plugins\LayoutRenderer\LayoutRenderer::$data);
        }

        $this->helpers = new \ArrayObject();
        $this->mustRender = true;
        $this->viewRenderer = new ViewRenderer($this->view, $this->config, $this->pluginsManager);
        
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
     * @brief Executes the given action.
     * @param String $actionName The action name.
     */
    public function execute($actionName)
    {
        if(!$this->view->hasName())
        {
            $this->setViewName($actionName);
        }

        $action = Module::formatActionName($actionName);
        $this->$action();
    }
    
    /**
     * @brief Finalizes the execution process.
     * @details This method is executed at the end of the execution process.
     */
    public function finalize()
    {
        if($this->mustRender)
        {
            $this->response->content = $this->viewRenderer->render();
        }
    }
    
    /**
     * @brief Initializes the module.
     * @details This method is executed in the constructor.
     */
    public function init() {}
    
    /**
     * @brief The post-execution method.
     * @details This method is executed after the action execution.
     */
    public function postExecute() {}
    
    /**
     * @brief The pre-execution method.
     * @details This method is executed before the action execution.
     */
    public function preExecute() {}
    
    /**
     * @brief Formats the action name.
     * @param String $actionName The action name.
     * @return String The formated action name.
     */
    public static function formatActionName($actionName)
    {
        return ucfirst($actionName).'Action';
    }

    /**
     * @brief Get action view data.
     * @param String $name The action view name.
     * @return stdClass The partial view data.
     *
     * @details
     * This method allows to modify get, post, cookie, files and server arrays of action views declared in view files.
     * 
     * Use :
     * - $this->actionData('NAME')->get->foo = bar;
     * - $this->actionData('NAME')->post->foo = bar;
     * - $this->actionData('NAME')->cookie->foo = bar;
     * - $this->actionData('NAME')->files->foo = bar;
     * - $this->actionData('NAME')->server->foo = bar;
     */
    protected function actionData($name)
    {
        return $this->viewRenderer->getActionDataInstance($name);
    }

    /**
     * @brief The default action.
     */
    protected function defaultAction()
    {
        $this->response->setHttpStatusCode(404);
    }

    /**
     * @brief Disables the action rendering.
     */
    protected function disableActionRendering()
    {
        $this->mustRender = false;
    }
    
    /**
     * @brief Disables the layout rendering.
     */
    protected function disableLayoutRendering()
    {
        $this->notify('disable', array('LayoutRenderer'));
    }

    /**
     * @brief Get dynamically module helper instance.
     * @param String $name The helper name.
     * @return Mixed The helper instance.
     */
    protected function helper($name)
    {
        if(!$this->helpers->offsetExists(ucfirst($name)))
        {
            $helpClassName = substr(get_class($this), 0, strripos(get_class($this), '\\')).'\\'.'Helpers'.'\\'.ucfirst($name);

            $helper = Factory\HelpersFactory::get($helpClassName);
            $this->helpers->offsetSet(ucfirst($name), $helper);
        }
        
        return $this->helpers->offsetGet(ucfirst($name));
    }

    /**
     * @brief Get partial view data.
     * @param String $name The partial view name.
     * @return stdClass The partial view data.
     *
     * @details
     * This method allows to modify data of partial views declared in view files.
     * 
     * Use :
     * - $this->partialData('NAME')->foo = bar;
     */
    protected function partialData($name)
    {
        return $this->viewRenderer->getPartialDataInstance($name);
    }
    
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
     * @brief Set the layout name.
     * @param String $name The layout name.
     */
    protected function setLayoutName($name)
    {
        if($this->pluginsManager->has('LayoutRenderer'))
        {
            Plugins\LayoutRenderer\LayoutRenderer::$layoutName = $name;
        }
    }

    /**
     * @brief Set the action view name.
     * @param String $name The name.
     */
    protected function setViewName($name)
    {
        $this->view->setName('Project/Modules/'.substr(get_class($this), strripos(get_class($this), '\\') + 1).'/Views/'.ucfirst($name));
    }
}

?>