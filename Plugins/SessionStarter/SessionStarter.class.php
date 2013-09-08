<?php

namespace Project\Plugins\SessionStarter;

use Kernel\Core as Core;
use Kernel\Services as Services;
use Project\Plugins\SessionStarter\Exceptions as Exceptions;

/**
 * @brief This plugin starts the session in order to avoid the session start fail because of headers are already sent.
 *
 * @details
 * Typically, this plugin is useful in development environment.
 *
 * @see Kernel::Core::Plugin.
 * @see Kernel::Services::Session.
 */
class SessionStarter extends Core\Plugin
{
    /**
     * @brief Starts session during the pre dispatch step.
     *
     * @exception Plugins::SessionStarter::Exceptions::SessionException When the session start has failed.
     */
    public function preDispatch()
    {
        if(session_id() == '')
        {
            session_name($_SERVER['HTTP_HOST']);
            if(session_start() === false)
            {
                throw new Exceptions\SessionException('Session start has failed.');
            }
        }
    }
}