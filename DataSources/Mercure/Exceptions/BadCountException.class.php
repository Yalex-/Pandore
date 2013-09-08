<?php

namespace Project\DataSources\Mercure\Exceptions;

/**
 * @brief This exception is throw when something bad occurs about count computation.
 *
 * @see Mercure::MercureQuery::MercureSelect.
 */
class BadCountException extends \Exception
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