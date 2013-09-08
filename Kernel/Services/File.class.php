<?php

namespace Kernel\Services;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class provides classic file services in object paradigm.
 */
class File
{
    /**
     * @brief The file resource.
     * @var Resource.
     */
    private $file;
    /**
     * @brief The opening mode.
     * @var String.
     */
    private $mode;
    /**
     * @brief The file path.
     * @var String.
     */
    private $path;
    
    /**
     * @brief Constructor.
     * @param String $path The file path.
     * @param String $mode The opening mode.
     *
     * @see http://php.net/manual/en/function.fopen.php.
     */
    public function __construct($path, $mode = 'r')
    {
        $this->path = $path;
        $this->mode = $mode;

        $this->open();
    }
    
    /**
     * @brief Destructor.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @brief Changes files mode.
     * @param Int $mode The mode.
     * @return Whether the changing is successful.
     */
    public function chmod($mode)
    {
        $this->close();

        $result = chmod($this->path, $mode);

        $this->open();

        return $result;
    }

    /**
     * @brief Closes file.
     * 
     * @exception Kernel::Exceptions::FileException When file is unclosable.
     */
    public function close()
    {
        if(is_resource($this->file))
        {
            if(fclose($this->file) === false)
            {
                throw new Exceptions\FileException('File called "'.$this->path.'" is unclosable.');
            }
        }
    }

    /**
     * @brief Copies file to destination.
     * @param String $destination The destination path.
     * @return Bool Whether the copying is successful.
     */
    public function copy($destination)
    {
        $this->close();

        $result = copy($this->path, $destination);

        $this->open();

        return $result;
    }
    
    /**
     * @brief Get file content.
     * @return String The file content.
     */
    public function getContent()
    {
        $str = '';
        $lines = file($this->path);
        foreach($lines as $line)
        {
            $str .= $line;
        }
        return $str;
    }

    /**
     * @brief Get line from file pointer and parse for CSV fields.
     * @param Int $length Must be greater than the longest line (in characters) to be found in the CSV file.
     * @param String $delimiter The delimiter character.
     * @param String $enclosure The enclosure character.
     * @param String $escape The escape character.
     * @return ArrayObject The reading result.
     * 
     * @see http://www.php.net/manual/en/function.fgetcsv.php
     */
    public function getCSVContent($length = 0, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        $res = new \ArrayObject();
        while(($data = fgetcsv($this->file, $length, $delimiter, $enclosure, $escape)) !== false)
        {
            $res->append(new \ArrayObject($data));
        }
        return $res;
    }

    /**
     * @brief Get file extension.
     * @return String The file extension.
     */
    public function getExtension()
    {
        if(($ext = strrchr($this->path, '.')) !== false)
        {
            return substr($ext, 1);
        }
        else
        {
            return null;
        }
    }
    
    /**
     * @brief Get file lines.
     * @return ArrayObject The file lines array.
     */
    public function getLines()
    {
        return new \ArrayObject(file($this->path));
    }
    
    /**
     * @brief Get file name.
     * @param Bool $withExtension Whether the name must have its associated extension.
     * @return String The file name.
     */
    public function getName($withExtension = false)
    {
        $name = strrchr($this->path, DIRECTORY_SEPARATOR);
        $name = ($name !== false) ? substr($name, 1) : $this->path;

        if(!$withExtension)
        {
            $name = $this->strcut($name, 0, strripos($name, '.') - 1);
        }

        return $name;
    }
    
    /**
     * @brief Get file path.
     * @return String The file path.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @brief Get file size.
     * @return Int The file size
     */
    public function getSize()
    {
        return filesize($this->path);
    }
    
    /**
     * @brief Whether the file is readable.
     * @return Bool Whether the file is readable.
     */
    public function isReadable()
    {
        return is_readable($this->path);
    }
    
    /**
     * @brief Whether the file is writable.
     * @return Bool Whether the file is writable.
     */
    public function isWritable()
    {
        return is_writable($this->path);
    }

    /**
     * @brief Moves file.
     * @param String $newPath The new file path.
     * @return Bool Whether the moving is successful.
     */
    public function move($newPath)
    {
        $this->close();

        $result = rename($this->path, $newPath);
        $this->path = ($result !== false) ? $newPath : $this->path;

        $this->open();

        return $result;
    }

    /**
     * @brief Reads file using binary mode.
     * @param Int $length The number of read bytes.
     * @return Mixed The read string or false in failure case.
     */
    public function read($length)
    {
        return fread($this->file, $length);
    }
    
    /**
     * @brief Renames file.
     * @param String $newName The new name (with extension).
     * @return Bool Whether the renaming is successful.
     */
    public function rename($newName)
    {
        return $this->move($this->strcut($this->path, 0, strpos($this->path, $this->getName())).$newName);
    }
    
    /**
     * @brief Removes file.
     * @return Bool Whether the removing is successful.
     *
     * @exception Kernel::Exceptions::UnclosableFileException When file is unclosable.
     */
    public function remove()
    {
        $this->close();
        return unlink($this->path);
    }
    
    /**
     * @brief Get stats informations about file.
     * @return ArrayObject The stats informations about file.
     */
    public function stats()
    {
        return new \ArrayObject(lstat($this->path));
    }
    
    /**
     * @brief Creates html link to file location.
     * @return String A html link to file location.
     */
    public function toHtml()
    {
        return '<a href=\'index.php?f='.$this->path.'\'>'.$this->getName().'</a>';
    }
    
    /**
     * @brief Writes content string.
     * @param String $content The content.
     *
     * @exception Kernel::Exceptions::FileException When file is unwritable.
     * 
     * @see http://fr2.php.net/manual/en/function.fopen.php.
     */
    public function write($content)
    {
        if(fwrite($this->file, $content) === false)
        {
            throw new Exceptions\FileException('File called "'.$this->path.'" is unwritable with '.$this->mode.' mode.');
        }
    }

    /**
     * @brief Opens file.
     *
     * @exception Kernel::Exceptions::FileException When file is unopenable.
     *
     * @see http://php.net/manual/en/function.fopen.php.
     */
    private function open()
    {
        $this->file = @fopen($this->path, $this->mode);
        
        if($this->file === false)
        {
            throw new Exceptions\FileException('File called "'.$this->path.'" is unopenable with '.$this->mode.' mode.');
        }
    }
}

?>