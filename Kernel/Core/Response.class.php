<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;
use Kernel\Services as Services;

/**
 * @brief This class encapsulates the rendering aka the content and allows to set http code status.
 *
 * @details
 * Smart variable access :
 * - $this->content : get the content.
 * 
 * @see Kernel::Services::IniParser.
 */
class Response
{
    /**
     * @brief The configuration.
     * @var Kernel::Services::IniParser.
     */
    private $config;
    /**
     * @brief The inaccessible properties.
     * @var ArrayObject.
     */
    private $data;
    
    /**
     * @brief Constructor.
     * @param Kernel::Services::IniParser $config The configuration.
     */ 
    public function __construct(Services\IniParser $config)
    {
        $this->config = $config;

        $this->data = new \ArrayObject();
        $this->data->offsetSet('content', '');
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
        if(!$this->data->offsetExists($key))
        {
            throw new Exceptions\BadKeyException('The key "'.$key.'" doesn\'t exist.');
        }
        return $this->data->offsetGet($key);
    }
    
    /**
     * @brief Writes data to inaccessible properties.
     * @param String $key The key.
     * @param Mixed $value The value.
     *
     * @exception Kernel::Exceptions::BadKeyException When the key isn't settable.
     */
    public function __set($key, $value)
    {
        if(!$this->data->offsetExists($key))
        {
            throw new Exceptions\BadKeyException('The key "'.$key.'" isn\'t settable.');
        }
        $this->data->offsetSet($key, $value);
    }

    /**
     * @brief Sends response.
     */
    public function send()
    {
        echo $this->content;
    }

    /**
     * @brief Set http response code.
     * @param Int $code The http response code.
     *
     * @details
     * This method throws an exception about the http error associated with the given code and its message is built from the config.ini.
     * 
     * @exception Kernel::Exceptions::HttpStatusCodeException When a http status code is set.
     */
    public function setHttpStatusCode($code)
    {
        try {
            $message = ' '.$this->config->getArrayValue('httpCodes', $code);
        } catch(\Exception $exception) {
            $message = '';
        }

        throw new Exceptions\HttpStatusCodeException($_SERVER['SERVER_PROTOCOL'].' '.$code.$message.'.', $code);
    }
}

?>