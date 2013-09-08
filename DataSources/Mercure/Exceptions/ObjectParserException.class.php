<?php

namespace Project\DataSources\Mercure\Exceptions;

/**
 * @brief This exception is throw when somethin bad occurs about object parser.
 *
 * @see Mercure::ObjectParser::DatabaseObjectParser.
 * @see Mercure::ObjectParser::SchemaObjectParser.
 */
class ObjectParserException extends \Exception
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