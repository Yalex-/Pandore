<?php

namespace Kernel\Services;

/**
 * @brief This class allows to create log file and write to it.
 *
 * @details
 * Log content are formated as follows :
 * MM/DD/YYYY HH:MM:SS  |  Message.
 *
 * @see Kernel::Services::File.
 */
class Logger
{   
    /**
     * @brief Logs message.
     * @param String $logName The log file name. 
     * @param String $message The message.
     */
    public static function log($logName, $message)
    {
        $path = ROOT_PATH.'Log/'.$logName.'.log';
        $log = date('m\/d\/Y H\:i\:s  |  ').$message.PHP_EOL.PHP_EOL;
        
        $file = new File($path, 'a+');
        $file->write($log);
    }
}

?>
