<?php

namespace Kernel\Exceptions;

/**
 * @brief This exception is throw when something bad occurs about directory.
 *
 * @see Kernel::Services::Directory.
 */
class DirectoryException extends \Exception
{
    /**
     * @brief Constructor.
     * @param String $message The exception message.
     * @param Int $code The exception code.
     */
    public function __construct($message = null, $code = 0)
    {
        parent::__construct($message, $code);
    }
}

?>