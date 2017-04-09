<?php

namespace Pmecrm\ApiClient;

use Pmecrm\ApiClient\Core\APICore;

/**
 * Class API
 */
class API extends APICore
{

    /**
     * PmeCrmAPI constructor.
     * @param array $config
     */
    public function __construct(array $config = array(), $apiURL = 'http://api.pme-crm.fr')
    {
        parent::__construct(1.0, $apiURL);

        //Définir les configurations
        $this->defineConfiguration($config);
    }

    /**
     * @param $moduleName
     * @param array $params
     * @throws \Exception
     */
    public function create($moduleName, Array $params)
    {
        $moduleName = preg_replace('/([^a-z0-9_-])/i', '', $moduleName);

        if (trim($moduleName) == '') {
            throw new \Exception('Module Name is Required');
        } elseif (in_array($moduleName, array('__construct', '__destruct', 'getResponse', '__call', 'defineConfiguration'))) {
            throw new \Exception('Module Name is not valid');
        } elseif (method_exists($this, $moduleName)) {
            throw new \Exception('Module Name is not valid');
        } else {
            $this->$moduleName($params, 'create');
        }

    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }
}