<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;
use Kernel\Services as Services;

/**
 * @brief This class allows to render simple and composed views.
 *
 * @details
 * There are two types of composed views :
 * - The partial views which are equivalent to html view files inclusion with the possibility to use it as Pandore view.
 * - The action views which are equivalent to dispatch actions of modules (without the plugins pre and post dispath operations).
 *
 * @see Kernel::Core::Dispatcher.
 * @see Kernel::Core::PluginsManager.
 * @see Kernel::Core::Request.
 * @see Kernel::Core::Response.
 * @see Kernel::Core::Router.
 * @see Kernel::Core::View.
 * @see Kernel::Services::IniParser.
 * @see Kernel::Services::Tools.
 */
class ViewRenderer
{
    use Services\Tools;

    /**
     * @brief The action views data.
     * @var ArrayObject.
     */
    private $actions;
    /**
     * @brief The configuration.
     * @var Kernel::Services::IniParser.
     */
    private $config;
    /**
     * @brief The partial views data.
     * @var ArrayObject.
     */
    private $partials;
    /**
     * @brief The plugins manager.
     * @var Kernel::Core::PluginsManager.
     */
    private $pluginsManager;
    /**
     * @brief The view.
     * @var Kernel::Core::View.
     */
    private $view;
    
    /**
     * @brief Constructor.
     * @param Kernel::Core::View $view The view.
     * @param Kernel::Services::IniParser $config The configuration.
     * @param Kernel::Core::PluginsManager $pluginsManager The plugins manager.
     */
    public function __construct(View $view, Services\IniParser $config, PluginsManager $pluginsManager)
    {
        $this->config = $config;
        $this->pluginsManager = $pluginsManager;
        $this->view = $view;

        $this->actions = new \ArrayObject();
        $this->partials = new \ArrayObject();
    }

    /**
     * @brief Get data instance of the action view.
     * @param String $name The action view name.
     * @return stdClass The data.
     */
    public function getActionDataInstance($name)
    {
        if(!$this->actions->offsetExists($name))
        {
            $action = new \stdClass();
            $action->get = new \stdClass();
            $action->post = new \stdClass();
            $action->cookie = new \stdClass();
            $action->files = new \stdClass();
            $action->server = new \stdClass();

            $this->actions->offsetSet($name, $action);
        }

        return $this->actions->offsetGet($name);
    }

    /**
     * @brief Get data instance of the partial view.
     * @param String $name The partial view name.
     * @return stdClass The data.
     */
    public function getPartialDataInstance($name)
    {
        if(!$this->partials->offsetExists($name))
        {
            $partial = new \stdClass();
            
            $this->partials->offsetSet($name, $partial);
        }

        return $this->partials->offsetGet($name);
    }
    
    /**
     * @brief Renders the view.
     * @return String The rendered view.
     *
     * @exception Kernel::Exceptions::ViewRendererException When the view file doesn't exist.
     */
    public function render()
    {
        $name = $this->view->getName();
        if(!file_exists($this->getFilePath($name)))
        {
            throw new Exceptions\ViewRendererException('The view file called "'.$name.'" doesn\'t exist.');
        }

        ob_start();
        try {
            require($this->getFilePath($name));        
        } catch(\Exception $exception) {
            ob_end_clean();
            throw $exception;
        }
        return ob_get_clean();
    }

    /**
     * @brief Defines action view and render it.
     * @param String $name The action view name.
     * @param String $moduleName The module name.
     * @param String $actionName The action name.
     * @param Array $get The $_GET array.
     * @param Array $post The $_POST array.
     * @param Array $cookie The $_COOKIE array.
     * @param Array $files The $_FILES array.
     * @param Array $server The $_SERVER array.
     * @return Mixed The action rendering.
     *
     * @details
     * It's possible to overload data from modules using the action view name.
     */
    private function action($name, $moduleName, $actionName, $get = array(), $post = array(), $cookie = array(), $files = array(), $server = array())
    {
        if($this->actions->offsetExists($name))
        {
            $get = array_merge($get, get_object_vars($this->actions->offsetGet($name)->get));
            $post = array_merge($post, get_object_vars($this->actions->offsetGet($name)->post));
            $cookie = array_merge($cookie, get_object_vars($this->actions->offsetGet($name)->cookie));
            $files = array_merge($files, get_object_vars($this->actions->offsetGet($name)->files));
            $server = array_merge($server, get_object_vars($this->actions->offsetGet($name)->server));
        }

        $uri = $this->uri($moduleName, $actionName, $get);

        $router = new Router(array('q' => $uri), $this->config);

        $request = new Request($router, $post, $cookie, $files, $server);
        
        $response = new Response($this->config);

        $dispatcher = new Dispatcher($request, $response, $this->config, $this->pluginsManager);
        $dispatcher->dispatch();

        $response->send();
    }

    /**
     * @brief Get view file path.
     * @param String $name The view name.
     * @return String The view file path.
     */
    private function getFilePath($name)
    {
        return ROOT_PATH.$name.'.php';
    }

    /**
     * @brief Defines partial view and render it.
     * @param String $name The partial view name.
     * @param String $viewName The associated view name.
     * @param Array $data The view default data.
     * @return String The rendered view.
     *
     * @details
     * It's possible to overload data from modules using the partial view name.
     */
    private function partial($name, $viewName, $data = array())
    {
        $partial = new View($viewName, $data);

        if($this->partials->offsetExists($name))
        {
            $partial->mergeData(new \ArrayObject(get_object_vars($this->partials->offsetGet($name))));
        }

        $view = &$this->view;

        $this->view = $partial;
        $return = $this->render();

        $this->view = $view;

        echo $return;
    }
}

?>