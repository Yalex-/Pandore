<?php

namespace Kernel\Services;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class provides access to the directory structure and allows to manipulate it.
 *
 * @details
 * The directory structure manipulations contains the possibility of applying callback function to the selected files.
 *
 * @see Kernel::Services::Tools.
 */
class Directory
{
    use Tools;

    /**
     * @brief The directory resource.
     * @var Resource.
     */
	private $dir;
    /**
     * @brief The path.
     * @var String.
     */
	private $path;
	
    /**
     * @brief Constructor.
     * @param String $path The directory path.
     *
     * @exception Kernel::Exceptions::DirectoryException When the path isn't a valid directory path.
     */
	public function __construct($path)
	{
        $this->path = $path;

        if(!is_dir($this->path))
        {
            throw new Exceptions\DirectoryException('"'.$this->path.'" isn\'t a directory.');
        }

        if($this->path[strlen($this->path) - 1] != DIRECTORY_SEPARATOR)
        {
            $this->path .= DIRECTORY_SEPARATOR;
        }
	}
    
    /**
     * @brief Applies callback on directory files.
     * @param Function $callback The callback.
     * @param Array $authorizedExt The authorized extension array.
     * @param Array $prohibitedExt The prohibited extension array.
     * @param Bool $isRecursive Whether the callback must be apply recursively.
     *
     * @details
     * The callback must have the following format :
     * callback($directoryPath, $relativefilePath), where the parameters are :
     * String $directoryPath The directory path.
     * String $relativefilePath The relative file path (directory complement).
     * The extension arrays must be like array('.php', '.html').
     * All extensions is authorized with an empty authorized array.
     *
     * @see Kernel::Services::Directory::applyRecursiveCallbackOnFiles.
     * @see http://fr2.php.net/manual/en/function.readdir.php
     */
    public function applyCallbackOnFiles($callback, $authorizedExt = array(), $prohibitedExt = array(), $isRecursive = false)
    {
        if($isRecursive)
        {
            $this->applyRecursiveCallbackOnFiles($callback, $authorizedExt, $prohibitedExt, $this->path);
        }
        else
        {
            $this->open();

            while(false !== ($file = readdir($this->dir)))
            {
                if($file != '.' && $file != '..' && !is_dir($this->path.$file) && (empty($authorizedExt) || (!empty($authorizedExt) && in_array(strrchr($file, '.'), $authorizedExt))) && !in_array($strrchr($file, '.'), $prohibitedExt))
                {
                    $callback($this->path, $file);
                }
            }

            $this->close();
        }
    }
	
    /**
     * @brief Get directory files name.
     * @param Array $authorizedExt The authorized extensions array.
     * @param Array $prohibitedExt The prohibited extensions array.
     * @return ArrayObject The files name array.
     *
     * @details
     * All extensions are authorized with an empty authorized array.
     * 
     * Use :
     * - getFiles(array('.php, .html'), array('.js', '.css')).
     * 
     * @see http://fr2.php.net/manual/en/function.readdir.php
     */
	public function getFilesName($authorizedExt = array(), $prohibitedExt = array())
	{
        $files = new \ArrayObject();

        $this->open();

		while(false !== ($file = readdir($this->dir)))
        {
			if($file != '.' && $file != '..' && !is_dir($this->path.$file) && (empty($authorizedExt) || (!empty($authorizedExt) && in_array(strrchr($file, '.'), $authorizedExt))) && !in_array(strrchr($file, '.'), $prohibitedExt))
            {
				$files[] = $file;
            }
        }

        $this->close();

		return $files;
	}

    /**
     * @brief Get directory name.
     * @return String The directory name.
     *
     * @details
     * It's not the directory path.
     */
    public function getName()
    {
        $name = strrchr(substr($this->path, 0, -1), DIRECTORY_SEPARATOR);
        $name = ($name !== false) ? substr($name, 1) : $this->path;
        return $name;
    }
    
    /**
     * @brief Creates directory.
     * @param String $path The sub directory path from the current directory.
     * @param Int $mode The chmod.
     * @param Bool $isRecursive Whether it is possible to create nested directories.
     * @return Bool Whether the creation is successful.
     *
     * @details
     * In case of the sub directory already exists, this method return true.
     */
    public function mkdir($path, $mode = 0777, $isRecursive = false)
	{
		if(is_dir($this->path.$path))
        {
            return true;
        }
		else
        {
            return mkdir($this->path.$path, $mode, $isRecursive);
        }
	}
    
    /**
     * @brief Applies callback on directory files with recursion.
     * @param Function $callback The callback.
     * @param Array $authorizedExt The authorized extension array.
     * @param Array $prohibitedExt The prohibited extension array.
     * @param String $originalPath The original directory path.
     *
     * @details
     * All extensions are authorized with an empty authorized array.
     *
     * @see http://fr2.php.net/manual/en/function.readdir.php
     */
    private function applyRecursiveCallbackOnFiles($callback, $authorizedExt, $prohibitedExt, $originalPath)
    {
        $this->open();

        while(false !== ($file = readdir($this->dir)))
        {
            if(is_dir($this->path.$file))
            {
                if($file != '.' && $file != '..')
                {
                    $subDirectory = new Directory($this->path.$file);
                    $subDirectory->applyRecursiveCallbackOnFiles($callback, $authorizedExt, $prohibitedExt, $originalPath);
                }
            }
            elseif((empty($authorizedExt) || (!empty($authorizedExt) && in_array(strrchr($file, '.'), $authorizedExt))) && !in_array(strrchr($file, '.'), $prohibitedExt))
            {
                $additionnalPath = $this->strcut($this->path, strlen($originalPath), strlen($this->path));
                $callback($originalPath, $additionnalPath.$file);
            }
        }

        $this->close();
    }

    /**
     * @brief Closes directory.
     */
    private function close()
    {
        closedir($this->dir);
    }

    /**
     * @brief Opens directory.
     *
     * @exception Kernel::Exceptions::DirectoryException When the directory is unopenable.
     */
    private function open()
    {
        $this->dir = @opendir($this->path);
        
        if($this->dir === false)
        {
            throw new Exceptions\DirectoryException('Directory called "'.$this->path.'" is unopenable.');
        }
    }
}

?>