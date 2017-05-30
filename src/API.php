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
     * Create entity
     * @param $moduleName
     * @param array $params
     * @throws \Exception
     */
    public function create($moduleName, Array $params)
    {
        $this->callModule('create', $moduleName, $params);
    }

    /**
     * Retrieve entity
     * @param $moduleName
     * @param array $params
     * @throws \Exception
     */
    public function retrieve($moduleName, Array $params)
    {
        $this->callModule('get', $moduleName, $params);
    }

    public function sendmail($parameters = array()) {
        if(!is_array($parameters))
            $parameters = array();
        
        if(empty($parameters['to']) or filter_var($parameters['to'],FILTER_VALIDATE_EMAIL) == false) {
            throw new  \Exception('"to" email field is mandatory');
        }

        if(!empty($parameters['cc']) and filter_var($parameters['to'],FILTER_VALIDATE_EMAIL) == false) {
            throw new  \Exception('"cc" email field must be a valid email');
        }

        if(!empty($parameters['bcc']) and filter_var($parameters['bcc'],FILTER_VALIDATE_EMAIL) == false) {
            throw new  \Exception('"bcc" email field must be a valid email');
        }

        if(!empty($parameters['replyto']) and filter_var($parameters['replyto'],FILTER_VALIDATE_EMAIL) == false) {
            throw new  \Exception('"replyto" email field must be a valid email');
        }

        if(empty($parameters['title'])) {
            throw new  \Exception('"title" field is mandatory');
        }

        if(empty($parameters['body'])) {
            throw new  \Exception('"body" field is mandatory');
        }
        
        $this->mailer($parameters, 'sendmail');
    }

    /**
     * call module
     * @param $method
     * @param $moduleName
     * @param $params
     * @throws \Exception
     */
    private function callModule($method, $moduleName, $params) {
        $moduleName = preg_replace('/([^a-z0-9_-])/i', '', $moduleName);

        if (trim($moduleName) == '') {
            throw new \Exception('Module Name is Required');
        } elseif (in_array($moduleName, array('__construct', '__destruct', 'getResponse', '__call', 'defineConfiguration'))) {
            throw new \Exception('Module Name is not valid');
        } elseif (method_exists($this, $moduleName)) {
            throw new \Exception('Module Name is not valid');
        } else {
            $this->$moduleName($params, $method);
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