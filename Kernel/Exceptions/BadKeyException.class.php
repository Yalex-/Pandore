<?php

namespace Kernel\Exceptions;

/**
 * @brief This exception is throw when trying to access value with an nonexistent key.
 *
 * @see Kernel::Core::FrontController.
 * @see Kernel::Core::Module.
 * @see Kernel::Core::Plugin.
 * @see Kernel::Core::PluginsManager.
 * @see Kernel::Core::Response.
 * @see Kernel::Core::View.
 * @see Kernel::Services::IniParser.
 * @see Kernel::Services::Session.
 */
class BadKeyException extends \Exception
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