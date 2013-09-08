<?php

namespace Project\Plugins\Authentification;

use Kernel\Core as Core;
use Kernel\Services as Services;

/**
 * @brief This plugin allows to authorize (or not) the access to each actions of every modules.
 * 
 * @see Kernel::Core::Plugin.
 * @see Kernel::Core::Response.
 * @see Kernel::Services::Session.
 * 
 * @see https://github.com/fabpot/yaml.
 */
class Authentification extends Core\Plugin
{
    /**
     * @brief The plugin configuration path.
     */
    const CONFIG_PATH = 'Project/Plugins/Authentification/Config/Config.yaml';
    /**
     * @brief Yaml Key for authorized permission.
     */
    const YAML_KEY_AUTHORIZED = 'authorized';
    /**
     * @brief Yaml Key for unauthorized permission.
     */
    const YAML_KEY_UNAUTHORIZED = 'unauthorized';

    /**
     * @brief The default value associated to the key.
     * @var Mixed.
     */
    private $defaultValue;
    /**
     * @brief Activation flag.
     * @var Bool.
     */
    private $enable;
    /**
     * @brief The key to check value for authentification.
     * @var String.
     */
    private $keyToCheck;
    /**
     * @brief The users permissions.
     * @var ArrayObject.
     */
    private $permissions;
    
    /**
     * @brief Verifies authentification during the pre dispatch step.
     */
    public function preDispatch()
    {
        if($this->enable)
        {
            $session = new Services\Session('AuthentificationPlugin');
            try {
                $keyToCheck = $session->__get($this->keyToCheck);
            } catch(\Exception $e) {
                $keyToCheck = $this->defaultValue;
            }
            
            try {
                $modulePermissions = $this->permissions[ucfirst(strtolower($this->request->getModuleName()))];
                $permissions = $modulePermissions['actions'][strtolower($this->request->getActionName() ? $this->request->getActionName() : 'default')];
            } catch(\ErrorException $e) {
                try {
                    $permissions = $this->permissions[$this->request->getModuleName()]['permissions'];
                } catch(\ErrorException $e) {
                    $permissions = array();
                }
            }
            
            if(!empty($permissions))
            {
                if(array_key_exists(self::YAML_KEY_AUTHORIZED, $permissions))
                {
                    if(!in_array($keyToCheck, $permissions[self::YAML_KEY_AUTHORIZED]))
                    {
                        $this->response->setHttpStatusCode(403);
                    }
                }
                if(array_key_exists(self::YAML_KEY_UNAUTHORIZED, $permissions))
                {
                    if(in_array($keyToCheck, $permissions[self::YAML_KEY_UNAUTHORIZED]))
                    {
                        $this->response->setHttpStatusCode(403);
                    }
                }
            }
        }
    }

    /**
     * @brief Initializes permissions.
     */
    protected function init()
    {
        $config = Services\Yaml\Yaml::load(ROOT_PATH.self::CONFIG_PATH);
        $this->defaultValue = $config['DefaultValue'];
        $this->enable = empty($config['KeyToCheck']) ? false : true;
        $this->keyToCheck = $config['KeyToCheck'];
        $this->permissions = $config['Modules'];
    }
}

?>