<?php

namespace Kernel\Core;

/**
 * @brief This class boots Pandore mechanism through the front controller manipulation.
 *
 * @see Kernel::Core::FrontController.
 */
class Bootstrap
{
    /**
     * @brief The configuration file path.
     * @param String.
     */
    private $configFilePath;

    /**
     * @brief The relative configuration files directory.
     * @param String.
     */
    private static $configDir = 'Project/Config/';

    /** 
     * @brief Initializes Pandore mechanism.
     */
    public function init()
    {
        $configName = file_exists(ROOT_PATH.self::$configDir.$_SERVER['HTTP_HOST'].'.ini') ? $_SERVER['HTTP_HOST'] : 'default';
        $this->configFilePath = ROOT_PATH.self::$configDir.$configName.'.ini';
    }

    /**
     * @brief Runs Pandore mechanism.
     */
    public function run()
    {
        $frontController = new FrontController($this->configFilePath);
        $frontController->dispatch();
        $frontController->sendResponse();
    }
}

?>