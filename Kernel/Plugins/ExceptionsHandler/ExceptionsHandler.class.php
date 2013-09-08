<?php

namespace Kernel\Plugins\ExceptionsHandler;
 
use Kernel\Core as Core;
use Kernel\Exceptions as Exceptions;
use Kernel\Services as Services;

/**
 * @brief This plugin handles the exception catching process using config options.
 *
 * @details
 * There are several ways to handles the exception catching process :
 * - with common http response code, exceptions can be logged and an exception view is produced (this view is customizable).
 * - with common exception, a 500 http error can be returned, exceptions can be logged and an associated debug view can be produced.
 *
 * @see Kernel::Core::Plugin.
 * @see Kernel::Core::PluginsManager.
 * @see Kernel::Core::View.
 * @see Kernel::Core::ViewRenderer.
 * @see Kernel::Exceptions::HttpStatusCodeException.
 * @see Kernel::Services::IniParser.
 * @see Kernel::Services::Logger.
 * @see Kernel::Services::Tools.
 */
class ExceptionsHandler extends Core\Plugin
{
    /**
     * @brief The debug view path.
     */
    const DEBUG_VIEW_PATH = 'Kernel/Plugins/ExceptionsHandler/Views/Debug';
    /**
     * @brief The http codes views path.
     */
    const HTTP_CODES_VIEWS_PATH = 'Project/Views/HttpCodes/';

    /**
     * @brief The exception.
     * @var Exception.
     */
    public static $exception;

    /**
     * @brief Whether debug view is produced when an exception occurs.
     * @var Bool.
     */
    private $debug;
    /**
     * @brief Whether 500 http error is sent when an exception occurs.
     * @var Bool.
     */
    private $httpCode500;
    /**
     * @brief Whether log error is produced when an exception occurs.
     * @var Bool.
     */
    private $log;
    /**
     * @brief Whether log error about http status code is produced when an exception occurs.
     * @var Bool.
     */
    private $logHttpCode;

    /**
     * @brief Executes the exception catching process.
     */
    public function execute()
    {
        if(self::$exception instanceof Exceptions\HttpStatusCodeException)
        {
            if($this->logHttpCode)
            {
                $this->logException();
            }

            $this->executeHttpError(self::$exception->getCode());
        }
        else
        {
            if($this->log)
            {
                $this->logException();
            }

            if($this->debug)
            {
                $this->executeDebug();
            }
        }
    }

    /**
     * @brief Initializes the plugin.
     */
    public function init()
    {
        try {
            $this->debug = (bool) $this->config->getValue('debug');
        } catch(\Exception $exception) {
            $this->debug = false;
        }

        try {
            $this->httpCode500 = (bool) $this->config->getValue('httpCode500');
        } catch(\Exception $exception) {
            $this->httpCode500 = false;
        }

        try {
            $this->log = (bool) $this->config->getValue('log');
        } catch(\Exception $exception) {
            $this->log = true;
        }

        try {
            $this->logHttpCode = (bool) $this->config->getValue('logHttpCode');
        } catch(\Exception $exception) {
            $this->logHttpCode = false;
        }
    }

    /**
     * @brief Executes the debug rendering.
     */
    private function executeDebug()
    {
        if($this->httpCode500)
        {
            try {
                $message = $this->config->getArrayValue('httpCodes', 500);
            } catch(\Exception $exception) {
                $message = 'Internal Server Error';
            }

            header($_SERVER['SERVER_PROTOCOL'].' 500 '.$message.'.');
        }
        
        $data = array('exception' => self::$exception);
        $view = new Core\View(self::DEBUG_VIEW_PATH, $data);
        $viewRenderer = new Core\ViewRenderer($view, $this->config, $this->pluginsManager);
        $this->response->content = $viewRenderer->render();
    }

    /**
     * @brief Executes the http error rendering.
     */
    private function executeHttpError($code)
    {
        try {
            $name = self::HTTP_CODES_VIEWS_PATH.$code;
            $view = new Core\View($name);
            $viewRenderer = new Core\ViewRenderer($view, $this->config, $this->pluginsManager);
            $this->response->content = $viewRenderer->render();
        } catch(\Exception $exception) {
            if($this->debug)
            {
                $this->executeDebug();
            }
        }
    }

    /**
     * @brief Logs exception in error.log file.
     */
    private function logException()
    {
        Services\Logger::log('error', self::$exception);
    }
}
 
?>