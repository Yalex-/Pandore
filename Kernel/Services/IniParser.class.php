<?php

namespace Kernel\Services;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class parses ini files and controls the value(s) access.
 */
class IniParser
{
    /**
     * @brief The ini file content.
     * @var ArrayObject.
     */
    private $data;
    
	/**
	 * @brief Constructor.
	 * @param String $iniFilePath The ini file path.
	 *
     * @exception Kernel::Exceptions::FileException When file is unopenable.
	 */
	public function __construct($iniFilePath)
	{
        $array = parse_ini_file($iniFilePath);
        
        if($array === false)
        {
			throw new Exceptions\FileException('File called "'.$iniFilePath.'" is unreadable or doesn\'t exist.');
		}
        
        $this->data = new \ArrayObject($array);
	}
    
    /**
	 * @brief Get array associated with the name.
	 * @param String $name The array name.
	 * @return ArrayObject The values array.
     *
     * @exception Kernel::Exceptions::BadKeyException When the associated array doesn't exist in the configuration array.
     * @exception Kernel::Exceptions::BadTypeException When the value associated with the name isn't an array.
	 */
	public function getArray($name)
	{
        if(!$this->data->offsetExists($name))
        {
            throw new Exceptions\BadKeyException('The key "'.$name.'" doesn\'t exist in the configuration file.');
        }

        if(!is_array($this->data->offsetGet($name)))
        {
            throw new Exceptions\BadTypeException('The value associated with "'.$name.'" isn\'t an array (use getValue or getArrayValue).');
        }

        return new \ArrayObject($this->data->offsetGet($name));
    }

    /**
     * @brief Get array value associated with the name and the key.
     * @param String $name The array name.
     * @param String $key The value key.
     * @return Mixed The value.
     *
     * @exception Kernel::Exceptions::BadKeyException When the key doesn't exist in the desired array.
     */
    public function getArrayValue($name, $key)
    {
        $array = $this->getArray($name);

        if(!$array->offsetExists($key))
        {
            throw new Exceptions\BadKeyException('The key "'.$key.'" doesn\'t exist in the "'.$name.'" array.');
        }

        return $array->offsetGet($key);
    }

    /**
     * @brief Get value associated with the name.
     * @param String $name The value name.
     * @return Mixed The value.
     *
     * @exception Kernel::Exceptions::BadKeyException When the associated value doesn't exist in the configuration array.
     * @exception Kernel::Exceptions::BadTypeException When the value associated with the name is an array.
     */
    public function getValue($name)
    {
        if(!$this->data->offsetExists($name))
        {
            throw new Exceptions\BadKeyException('The key "'.$name.'" doesn\'t exist in the configuration file.');
        }

        if(is_array($this->data->offsetGet($name)))
        {
            throw new Exceptions\BadTypeException('The value associated with "'.$name.'" is an array (use getArray or getArrayValue).');
        }

        return $this->data->offsetGet($name);
    }
}

?>