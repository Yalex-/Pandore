<?php

namespace Kernel\Services;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This trait encapsulates Pandore utilities methods.
 */
trait Tools
{
    /**
     * @brief Converts datetime to timestamp.
     * @param String $date The datetime.
     * @return Int The timestamp.
     */
    public function datetimeToTimestamp($date)
    {
        $hour = substr($date, 11, 2);
        $min = substr($date, 14, 2);
        $sec = substr($date, 17, 2);
        $day = substr($date, 8, 2);
        $month = substr($date, 5, 2);
        $year = substr($date, 0, 4);
        return mktime((int)$hour, (int)$min, (int)$sec, (int)$month, (int)$day, (int)$year);
    }
    
    /**
     * @brief Converts DSN to array.
     * @param String $DSN The DSN.
     * @return ArrayObject The parameters array.
     *
     * @exception Kernel::Exceptions::BadDSNException When the DSN isn't correctly defined.
     *
     * @details
     * The DSN has the following format : key1:value1+key2:value2+...+keyN:valueN.
     *
     * @see http://php.net/manual/en/ref.pdo-mysql.connection.php.
     */
    public function DSNToArray($DSN)
    {
        $DSNArray = new \ArrayObject();
        
        try {
            $array = explode('+', $DSN);
            foreach($array as $value)
            {
                $exp = explode(':', $value);
                $DSNArray[$exp[0]] = $exp[1];
            }
        } catch(\Exception $e) {
            throw new Exceptions\BadDSNException('The DSN isn\'t correctly defined');
        }
        
        if(!($DSNArray->offsetExists('dbms') &&
             $DSNArray->offsetExists('host') &&
             $DSNArray->offsetExists('dbname') &&
             $DSNArray->offsetExists('username') &&
             $DSNArray->offsetExists('password')))
        {
            throw new Exceptions\BadDSNException('The DSN isn\'t correctly defined');
        }
        
        return $DSNArray;
    }
    
    /**
     * @brief Displays value with human readable format.
     * @param Mixed $value The value.
     */
    public function dump($value)
    {
        echo('<pre>'.print_r($value, true).'</pre>');
    }
    
    /**
     * @brief Get current page name.
     * @return String The current page name.
     */
    public function getCurrentPageName()
    {
        $page = null;
        if(isset($_SERVER['QUERY_STRING']))
        {
            $page = explode('=', $_SERVER['QUERY_STRING']);
            $page = explode('&', $page[1]);
            $page = $page[0];
        }
        return ($page != null && $page != '') ? $page : 'undefined';
    }
    
    /**
     * @brief Get visitor ip.
     * @return String The visitor ip.
     */
    public function getIp()
    {
        $ip = '';
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        elseif(isset($_SERVER['HTTP_CLIENT_IP']))
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        else
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    /**
     * @brief Get current datetime.
     * @return String The current datetime.
     */
    public function now()
    {
        return date('Y\-m\-d H\:i\:s');
    }
    
    /**
     * @brief Removes defined characters of the string.
     * @param String $string The string.
     * @param ArrayObject $useless The useless characters array.
     * @return String The cleaned string.
     */
    public function strclean($string, \ArrayObject $useless)
    {
        $cleaned = '';
        for($i = 0; $i < strlen($string) ; $i++)
        {
            if(!$useless->offsetExists($string[$i]))
            {
                $cleaned .= $string[$i];
            }
        }
        return $cleaned;
    }
    
    /**
     * @brief Cuts string.
     * @param String $string The string.
     * @param Int $start The first char position.
     * @param Int $end The last char position.
     * @return String The cut string.
     */
    public function strcut($string, $start, $end)
    {
        $substring = '';
        for($i = 0 ; $i < strlen($string) ; $i ++)
        {
            if($i >= $start && $i <= $end)
            {
                $substring .= $string[$i];
            }
        }
        return $substring;
    }
    
    /**
     * @brief Formats string with lowercased for all letters except the first.
     * @param String $string The string.
     * @return String The formated string.
     */
    public function strformat($string)
    {
        return ucfirst(strtolower($string));
    }
    
    /**
     * @brief Computes random string.
     * @param Int $size The string size.
     * @return String The generated string.
     */
    public function strrand($size)
    {
        $str = '';
        $dummy = 'abcdefgh*^%!-@#ijklmnpqrstuvwxy0123456789';
        srand((double)microtime()*1000);
        
        for($i = 0; $i < $size; $i++)
        {
            $str .= $dummy[rand() % strlen($dummy)];
        }
        
        return $str;
    }

    /**
     * @brief Computes uri from parameters.
     * @param String $moduleName The module name.
     * @param String $actionName The action name.
     * @param Array $get The $_GET array.
     * @return String the uri.
     */
    public function uri($moduleName = '', $actionName = '', $get = array())
    {
        $uri = '';
        if(!empty($moduleName))
        {
            $uri .= $moduleName.'/';

            if(!empty($actionName))
            {
                $uri .= $actionName.'/';

                if(!is_array($get))
                {
                    throw new Exceptions\BadTypeException('The "get" parameters must be an array.');
                }

                if(!empty($get))
                {
                    foreach($get as $key => $value)
                    {
                        $uri .= $key.'/'.$value.'/';
                    }
                }
            }
        }
        return $uri;
    }
     
    /**
     * @brief Computes absolute url from the relative url.
     * @param String $url The relative url.
     * @return String The absolute url.
     *
     * @details
     * The given url is relative to the root path.
     */
    public function url($url)
    {
        return 'http://'.$_SERVER['HTTP_HOST'].$this->strcut($_SERVER['SCRIPT_NAME'], 0, strripos($_SERVER['SCRIPT_NAME'], '/')).$url;
    }
}

?>