<?php

namespace Kernel\Plugins\LayoutRenderer;

use Kernel\Core as Core;

/**
 * @brief This plugin is dedicated to the layout (aka the global view) rendering.
 * 
 * @details
 * It's usualy the penultimate plugin in the plugins list but the last executed during the plugins post dispatch operation.
 *
 * @see Kernel::Core::Plugin.
 * @see Kernel::Core::View.
 * @see Kernel::Core::ViewRenderer.
 */
class LayoutRenderer extends Core\Plugin
{
    /**
     * @brief The layouts path.
     */
    const LAYOUTS_PATH = 'Project/Views/Layouts/';

    /**
     * @brief The layout data.
     * @var stdClass.
     */
    public static $data;
    /**
     * @brief The layout name.
     * @var String.
     */
    public static $layoutName;

    /**
     * @brief Whether the layout rendering is enabled.
     * @var Bool.
     */
    private $isEnabled;
    
    /**
     * @brief Initializes the layout renderer.
     */
    public function init()
    {
        self::$data = new \stdClass();
        self::$layoutName = $this->config->getValue('layout');

        $this->enable();
    }
    
    /**
     * @brief Disables the layout rendering.
     */
    public function disable()
    {
        $this->isEnabled = false;
    }
    
    /**
     * @brief Enables the layout rendering.
     */
    public function enable()
    {
        $this->isEnabled = true;
    }
    
    /**
     * @brief Executes the layout rendering during the plugins post dispatch operation.
     */
    public function postDispatch()
    {
        if($this->isEnabled)
        {
            $layout = new Core\View(self::LAYOUTS_PATH.self::$layoutName, get_object_vars(self::$data));

            $layout->content = $this->response->content;

            $viewRenderer = new Core\ViewRenderer($layout, $this->config, $this->pluginsManager);
            
            $this->response->content = $viewRenderer->render();
        }
    }
}
 
?>