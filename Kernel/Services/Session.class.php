<?php

namespace Kernel\Services;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class allows to use session with advanced features like :
 * - namespace in order to create sessions with namespace,
 * - locking in order to lock/unlock the namespace session,
 * - least instance control in order to limit instances creation in futur,
 * - expiration control in order to limit the namespace or value lifetime.
 *
 * @details
 * This class isn't compatible with the session classic way.
 * The $_SESSION structure grouped by namespace :
 * - data (grouped by key)
 *      - value
 *      - expirationHop
 *      - expirationTimestamp
 * - expirationHop
 * - expirationTimestamp
 * - leastInstance
 * - locked
 */
class Session
{
    /**
     * @brief The data.
     */
    const DATA = 'data';
    /**
     * @brief The expiration hop.
     */
    const EXP_HOP = 'expirationHop';
    /**
     * @brief The expiration timestamp.
     */
    const EXP_TIME = 'expirationTimestamp';
    /**
     * @brief The least instance controller.
     */
    const LEAST = 'leastInstance';
    /**
     * @brief The expiration timestamp.
     */
    const LOCKED = 'locked';
    /**
     * @brief The value identifier.
     */
    const VALUE = 'value';

    /**
     * @brief Whether the session is the least instantiable.
     * @var Bool.
     */
    private $isLeastInstance;
    /**
     * @brief The session namespace.
     * @var String.
     */
    private $namespace;

    /**
     * @brief Constructor.
     * @param String $namespace The session namespace.
     * @param Bool $isLeastInstance Whether the session is the least instantiable.
     */
    public function __construct($namespace, $isLeastInstance = false)
    {
        $this->isLeastInstance = $isLeastInstance;
        $this->namespace = $namespace;

        $this->start();
    }

    /**
     * @brief Reads session data from inaccessible properties.
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

        $this->checkExpiration($key);

        return $_SESSION[$this->namespace]->offsetGet(self::DATA)->offsetGet($key)->offsetGet(self::VALUE);
    }

    /**
     * @brief Writes session data to inaccessible properties.
     * @param String $key The key.
     * @param Mixed $value The value.
     *
     * @exception Kernel::Exceptions::SessionException When the session is locked.
     */
    public function __set($key, $value)
    {
        if($this->isLocked())
        {
            throw new Exceptions\SessionException('It\'s not possible to write in locked session.');
        }

        $_SESSION[$this->namespace]->offsetGet(self::DATA)->offsetSet($key, new \ArrayObject(array(
            self::VALUE => $value,
            self::EXP_HOP => null,
            self::EXP_TIME => null,    
        )));
    }

    /**
     * @brief Destroys all data registered to a session.
     * @see http://www.php.net/manual/en/function.session-destroy.php.
     */
    public function destroy()
    {
        session_destroy();
    }

    /**
     * @brief Frees session variables.
     * @param Bool $all Whether the unset is not limited to this namespace.
     *
     * @details
     * This method preserves the Pandore session structure.
     * 
     * @see http://www.php.net/manual/en/function.session-unset.php.
     */
    public function free($all = false)
    {
        if($all === false)
        {
            unset($_SESSION[$this->namespace]);
        }
        else
        {
            session_unset();
        }

        $this->start();
    }

    /**
     * @brief Whether key exists in session.
     * @return Bool Whether key exists in session.
     */
    public function has($key)
    {
        return $_SESSION[$this->namespace]->offsetGet(self::DATA)->offsetExists($key);
    }

    /**
     * @brief Whether the session is the least instantiable.
     * @return Bool Whether the session is the least instantiable.
     */
    public function isLeastInstance()
    {
        return $this->isLeastInstance;
    }

    /**
     * @brief Whether the session is locked.
     * @return Bool Whether the session is locked.
     */ 
    public function isLocked()
    {
        return $_SESSION[$this->namespace]->offsetGet(self::LOCKED);
    }

    /**
     * @brief Locks the session.
     */
    public function lock()
    {
        $_SESSION[$this->namespace]->offsetSet(self::LOCKED, true);
    }

    /**
     * @brief Set number of user request before session or specific value expiration.
     * @param Int $hops The number of user request.
     * @param Mixed $key The key associated with the value.
     *
     * @exception Kernel::Exceptions::BadKeyException When the key doesn't exist.
     */
    public function setExpirationHops($hops, $key = null)
    {
        if($key == null)
        {
            $_SESSION[$this->namespace]->offsetSet(self::EXP_HOP, $hops);
        }
        else
        {
            if(!$this->has($key))
            {
                throw new Exceptions\BadKeyException('The key "'.$key.'" doesn\'t exist.');
            }

            $_SESSION[$this->namespace]->offsetGet(self::DATA)->offsetGet($key)->offsetSet(self::EXP_HOP, $hops);
        }
    }

    /**
     * @brief Set seconds before session or specific value expiration.
     * @param Int $seconds The number of second.
     * @param Mixed $key The key associated with the value.
     *
     * @exception Kernel::Exceptions::BadKeyException When the key doesn't exist.
     */
    public function setExpirationSeconds($seconds, $key = null)
    {   
        $timestamp = time() + $seconds;

        if($key == null)
        {
            $_SESSION[$this->namespace]->offsetSet(self::EXP_TIME, $timestamp);
        }
        else
        {
            if(!$this->has($key))
            {
                throw new Exceptions\BadKeyException('The key "'.$key.'" doesn\'t exist.');
            }

            $_SESSION[$this->namespace]->offsetGet(self::DATA)->offsetGet($key)->offsetSet(self::EXP_TIME, $timestamp);
        }
    }

    /**
     * @brief Unlocks the session.
     */
    public function unlock()
    {
        $_SESSION[$this->namespace]->offsetSet(self::LOCKED, false);
    }

    /**
     * @brief Checks whether the namespace or the value is expired.
     * @param String $key The key associated with the value.
     *
     * @exception Kernel::Exceptions::SessionException When the current namespace is expired by hops expiration.
     * @exception Kernel::Exceptions::SessionException When the desired value is expired by hops expiration.
     * @exception Kernel::Exceptions::SessionException When the current namespace is expired by time expiration.
     * @exception Kernel::Exceptions::SessionException When the desired value is expired by time expiration.
     */
    private function checkExpiration($key)
    {
        // Checks hops epxiration.
        if($_SESSION[$this->namespace]->offsetGet(self::EXP_HOP) === 0)
        {
            throw new Exceptions\SessionException('The current namespace "'.$this->namespace.'" is expired by hops expiration.');
        }

        if($_SESSION[$this->namespace]->offsetGet(self::DATA)->offsetGet($key)->offsetGet(self::EXP_HOP) === 0)
        {
            throw new Exceptions\SessionException('The value associated with "'.$key.'" is expired by hops expiration.');
        }

        // Checks time expiration.
        $timestamp = time();

        if($_SESSION[$this->namespace]->offsetGet(self::EXP_TIME) !== null && $_SESSION[$this->namespace]->offsetGet(self::EXP_TIME) < $timestamp)
        {
            throw new Exceptions\SessionException('The current namespace "'.$this->namespace.'" is expired by time expiration.');
        }

        if($_SESSION[$this->namespace]->offsetGet(self::DATA)->offsetGet($key)->offsetGet(self::EXP_TIME) !== null && $_SESSION[$this->namespace]->offsetGet(self::DATA)->offsetGet($key)->offsetGet(self::EXP_TIME) < $timestamp)
        {
            throw new Exceptions\SessionException('The value associated with "'.$key.'" is expired by time expiration.');
        }
    }
    
    /**
     * @brief Starts global session.
     *
     * @exception Kernel::Exceptions::SessionException When the session start has failed.
     * @exception Kernel::Exceptions::SessionException When the previous instance of this namespace was declared as the least instanciable. 
     */
    private function start()
    {
        // Session isn't started yet.
        if(session_id() == '')
        {
            session_name($_SERVER['HTTP_HOST']);
            if(session_start() === false)
            {
                throw new Exceptions\SessionException('Session start has failed.');
            }
        }

        // Creates the Pandore session structure.
        if(!array_key_exists($this->namespace, $_SESSION))
        {
            $_SESSION[$this->namespace] = new \ArrayObject();
            $_SESSION[$this->namespace]->offsetSet(self::DATA, new \ArrayObject());
            $_SESSION[$this->namespace]->offsetSet(self::EXP_HOP, null);
            $_SESSION[$this->namespace]->offsetSet(self::EXP_TIME, null);
            $_SESSION[$this->namespace]->offsetSet(self::LEAST, $this->isLeastInstance);
            $_SESSION[$this->namespace]->offsetSet(self::LOCKED, false);
        }
        // Checks the possibility to get instance of this part of session.
        else
        {
            if($_SESSION[$this->namespace]->offsetGet(self::LEAST) === true)
            {
                throw new Exceptions\SessionException('The previous instance of this namespace was declared as the least instanciable.');
            }
        }
    }
}

?>