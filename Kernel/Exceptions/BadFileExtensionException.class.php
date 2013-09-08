<?php

namespace Kernel\Exceptions;

/**
 * @brief This exception is throw when the file hasn't the desired extension.
 *
 * @see Kernel::Core::Request.
 */
class BadFileExtensionException extends \Exception
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