<?php

namespace Project\Plugins\SessionExpiration;

use Kernel\Core as Core;
use Kernel\Services as Services;
use Project\Plugins\SessionExpiration\Exceptions as Exceptions;

/**
 * @brief This plugin decrements the hops count of the session namespace(s) and/or value(s) in order to use the hop expiration mechanism of Pandore sessions.
 *
 * @details
 * The session starter plugin is useless when this plugin is used before it because this plugin starts session too.
 *
 * @see Kernel::Core::Plugin.
 * @see Kernel::Services::Session.
 */
class SessionExpiration extends Core\Plugin
{
    /**
     * @brief Decrements the hops count during the pre dispatch step.
     *
     * @exception Plugins::SessionExpiration::Exceptions::SessionException When the session start has failed.
     * @exception Plugins::SessionExpiration::Exceptions::SessionException When something bad occurs about session hops manipulations.
     */
    public function preDispatch()
    {
        // Starts session if necessary.
        if(session_id() == '')
        {
            session_name($_SERVER['HTTP_HOST']);
            if(session_start() === false)
            {
                throw new Exceptions\SessionException('Session start has failed.');
            }
        }

        try {
            foreach($_SESSION as $session)
            {
                // Decrements namespace hop.
                $hops = $session->offsetGet(Services\Session::EXP_HOP);
                if($hops !== null && $hops > 0)
                {
                    $hops--;
                    $session->offsetSet(Services\Session::EXP_HOP, $hops);
                }

                // Decrements value(s) hop.
                $iterator = $session->offsetGet(Services\Session::DATA)->getIterator();
                while($iterator->valid())
                {
                    $hops = $iterator->current()->offsetGet(Services\Session::EXP_HOP);
                    if($hops !== null && $hops > 0)
                    {
                        $hops--;
                        $iterator->current()->offsetSet(Services\Session::EXP_HOP, $hops);
                    }

                    $iterator->next();
                }
            }
        } catch(\Exceptions $exception) {
            throw new Exceptions\SessionException('Something bad occurs about sessions hops manipulations.');
        }
    }
}