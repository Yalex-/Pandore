<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class controls the view elements.
 */
class View
{
    /**
     * @brief The inaccessible properties.
     * @var ArrayObject.
     */
    private $data;
    /**
     * @brief The view file path from ROOT_PATH.
     * @var String.
     */
    private $name;

    /**
     * @brief Constructor.
     * @param String $name The view file path from ROOT_PATH.
     * @param Array $data The view data.
     *
     * @details
     * The name is the relative path from ROOT_PATH directory like Modules/Foo/Views/Bar.php.
     */
    public function __construct($name = '', $data = array())
    {
        $this->name = $name;
        $this->data = new \ArrayObject($data);
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
        if(!$this->has($key))
        {
            throw new Exceptions\BadKeyException('The key "'.$key.'" doesn\'t exist.');
        }
        return $this->data->offsetGet($key);
    }
    
    /**
     * @brief Writes data to inaccessible properties.
     * @param String $key The key.
     * @param Mixed $value The value.
     */
    public function __set($key, $value)
    {
        $this->data->offsetSet($key, $value);
    }

    /**
     * @brief Get view data.
     * @return ArrayObject The data.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @brief Get view name.
     * @return String The view name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @brief Whether key exists in inaccessible properties.
     * @param String $key The key.
     * @return Bool Whether key exists in inaccessible properties.
     */
    public function has($key)
    {
        return $this->data->offsetExists($key);
    }

    /**
     * @brief Whether view already has a name.
     * @return Bool Whether view already has a name.
     */
    public function hasName()
    {
        return !empty($this->name);
    }

    /**
     * @brief Merges new data with the existing view data.
     * @param ArrayObject $data The data to merge.
     * @param Bool $isReversed Whether the merge must be done from the given data.
     *
     * @details
     * The data merge is based on the php array merge function.
     */
    public function mergeData(\ArrayObject $data, $isReversed = false)
    {
        if(!$isReversed)
        {
            $this->data = new \ArrayObject(array_merge($this->data->getArrayCopy(), $data->getArrayCopy()));
        }
        else
        {
            $this->data = new \ArrayObject(array_merge($data->getArrayCopy(), $this->data->getArrayCopy()));
        }
    }

    /**
     * @brief Set view data.
     * @param ArrayObject $data The data.
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @brief Set the view name.
     * @param String $name The view file path from ROOT_PATH.
     *
     * @details
     * The name is the relative path from ROOT_PATH directory like Modules/Foo/Views/Bar.php.
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}

?>