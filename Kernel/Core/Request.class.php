<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;
use Kernel\Services as Services;

/**
 * @brief This class encapsulates the request and provides secures data by sanitize filtering.
 *
 * @see Kernel::Core::Router.
 * @see Kernel::Services::Tools.
 */
class Request
{
    use Services\Tools;

    /**
     * @brief The action name.
     * @var String.
     */
    private $actionName;
    /**
     * @brief The cookies parameters available in $_COOKIE.
     * @var ArrayObject.
     */
    private $cookieParameters;
    /**
     * @brief The files parameters available in $_FILES;
     * @var ArrayObject.
     */
    private $filesParameters;
    /**
     * @brief The request parameters available in $_GET.
     * @var ArrayObject.
     */
    private $requestParameters;
    /**
     * @brief The module name.
     * @var String.
     */
    private $moduleName;
    /**
     * @brief The post parameters available in $_POST.
     * @var ArrayObject.
     */
    private $postParameters;
    /**
     * @brief The server parameters available in $_SERVER.
     * @var ArrayObject.
     */
    private $serverParameters;
    
    /**
     * @brief Constructor.
     * @param Kernel::Core::Router $router The router.
     * @param Array $post The $_POST array.
     * @param Array $cookie The $_COOKIE array.
     * @param Array $files The $_FILES array.
     * @param Array $server The $_SERVER server.
     */
    public function __construct(Router $router, $post, $cookie, $files, $server)
    {
        $this->moduleName = $router->getModuleName();
        $this->actionName = $router->getActionName();
        $this->requestParameters = $router->getParameters();
        
        $this->cookieParameters = new \ArrayObject($cookie);
        $this->filesParameters = new \ArrayObject($files);
        $this->postParameters = new \ArrayObject($post);
        $this->serverParameters = new \ArrayObject($server);
    }
    
    /**
     * @brief Get cookie value associated with the key.
     * @param String $key The key name.
     * @param String $type The value type.
     * @param Mixed $default The default value in case of key doesn't exist or type isn't valid.
     * @return Mixed The value.
     */
    public function cookie($key = '', $type = 'undefined', $default = null)
    {
        if($this->cookieParameters->offsetExists($key))
        {
            return $this->secure($this->cookieParameters[$key], $type, $default);
        }
        return $default;
    }

    /**
     * @brief Get file values associated with the key and the extension.
     * @param String $key The key name.
     * @param String $ext The file extension ('.ext').
     * @param Mixed $default The default value in case of key doesn't exist or type isn't valid.
     * @return Array The file values.
     *
     * @exception Kernel::Exceptions::RequestException When something bad occurs about file uploading.
     * @exception Kernel::Exceptions::BadFileExtensionException When the uploaded file hasn't the desired extension.
     */
    public function files($key = '', $ext = '', $default = null)
    {
        if($this->filesParameters->offsetExists($key))
        {
            if($this->filesParameters[$key]['error'] !== UPLOAD_ERR_OK)
            {
                switch($this->filesParameters[$key]['error'])
                {
                    case UPLOAD_ERR_INI_SIZE: 
                        $message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini'; 
                        break;
                    case UPLOAD_ERR_FORM_SIZE: 
                        $message = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'; 
                        break;
                    case UPLOAD_ERR_PARTIAL: 
                        $message = 'The uploaded file was only partially uploaded'; 
                        break;
                    case UPLOAD_ERR_NO_FILE: 
                        $message = 'No file was uploaded'; 
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR: 
                        $message = 'Missing a temporary folder'; 
                        break;
                    case UPLOAD_ERR_CANT_WRITE: 
                        $message = 'Failed to write file to disk'; 
                        break;
                    case UPLOAD_ERR_EXTENSION: 
                        $message = 'File upload stopped by extension'; 
                        break;
                    default: 
                        $message = 'Unknown upload error'; 
                        break;
                } 

                throw new Exceptions\RequestException($message);
            }
            elseif(strrchr($this->filesParameters[$key]['name'], '.') != $ext)
            {
                throw new Exceptions\BadFileExtensionException('The uploaded file hasn\'t the desired extension.');
            }
            return $this->filesParameters[$key];
        }
        return $default;
    }

    /**
     * @brief Get request value associated with the key.
     * @param String $key The key name.
     * @param String $type The value type.
     * @param Mixed $default The default value in case of key doesn't exist or type isn't valid.
     * @return Mixed The value.
     */
    public function get($key = '', $type = 'undefined', $default = null)
    {        
        if($this->requestParameters->offsetExists($key))
        {
            return $this->secure($this->requestParameters[$key], $type, $default);
        }
        return $default;
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
     * @brief Get request.
     * @return String The request.
     */
    public function getRequest()
    {
        return $this->url($this->uri($this->moduleName, $this->actionName, $this->requestParameters->getArrayCopy()));
    }
    
    /**
     * @brief Get post value associated with the key.
     * @param String $key The key name.
     * @param String $type The value type.
     * @param Mixed $default The default value in case of key doesn't exist or type isn't valid.
     * @return Mixed The value.
     */
    public function post($key = '', $type = 'undefined', $default = null)
    {        
        if($this->postParameters->offsetExists($key))
        {
            return $this->secure($this->postParameters[$key], $type, $default);
        }
        return $default;
    }
    
    /**
     * @brief Get server value associated with the key.
     * @param String $key The key name.
     * @param String $type The value type.
     * @param Mixed $default The default value in case of key doesn't exist or type isn't valid.
     * @return Mixed The value.
     */
    public function server($key = '', $type = 'undefined', $default = null)
    {        
        if($this->serverParameters->offsetExists($key))
        {
            return $this->secure($this->serverParameters[$key], $type, $default);
        }
        return $default;
    }
    
    /**
     * @brief Secures value using type filtering.
     * @param Mixed $value The value which needs to be secured.
     * @param String $type The value type.
     * @param Mixed $default A default value in case of filtering failure.
     * @return Mixed The secured value.
     */
    private function secure($value, $type = 'undefined', $default = null)
    {
        $filters = array(
            'INT' => FILTER_SANITIZE_NUMBER_INT,
            'UINT' => FILTER_SANITIZE_NUMBER_INT,
            'STRING' => FILTER_SANITIZE_STRING,
            'FLOAT' => FILTER_SANITIZE_NUMBER_FLOAT,
            'MAIL' => FILTER_SANITIZE_EMAIL,
            'DATE' => FILTER_SANITIZE_STRING,
            'BOOLEAN' => FILTER_VALIDATE_BOOLEAN,
            'UNDEFINED' => FILTER_DEFAULT,
            'ARRAY' => FILTER_DEFAULT
        );
        $filteredValue = filter_var($value, $filters[strtoupper($type)]);
        return ($value === false) ? $default : preg_replace('/  +/', ' ', trim($filteredValue));
    }
}

?>