<?php

namespace Pmecrm\ApiClient\Core;

use Exception;

/**
 * Class APICore
 * @package Pmecrm\ApiClient
 */
class APICore
{

    /**
     * API VERSION
     **/
    private $version;

    /**
     * API URL CALLED ($_url/$action)
     **/
    private $url;

    /**
     * API URL
     */
    private $_url;

    /**
     * Send Method
     **/
    private $method;

    /**
     * Response
     **/
    protected $response = 'no sent';

    /**
     * Request Type
     **/
    private $request_mode = '';

    /**
     * Configuration API
     **/
    private $conf = array();

    /**
     * APICore constructor.
     * @param $version
     * @param $url
     * @param string $method
     */
    public function __construct($version, $url, $method = 'POST')
    {
        $this->version = $version;
        $this->_url = $url;
        $this->method = $method;
    }

    /**
     * Call API
     *
     * @param $request
     * @param $params
     * @throws Exception
     */
    public function __call($request, $params)
    {
        //Initialisation de l'URL :
        $this->requestUrlBuilder($request);

        //Initialisation de la Request Mode
        $this->getRequestMode($params);

        //Dernière analyse et envoie de la requête
        $this->sendRequest($params);
    }
    
    /**
     * @param array $config
     * @throws Exception
     */
    protected function defineConfiguration(Array $config)
    {
        //INSTANCE :
        $this->conf['instance'] = isset($config['instance']) ? $config['instance'] : '';

        //API KEY :
        $this->conf['apiKey'] = isset($config['apiKey']) ? $config['apiKey'] : '';

        //LOGIN KEY :
        $this->conf['LoginKey'] = isset($config['LoginKey']) ? $config['LoginKey'] : '';

        //USERID :
        $this->conf['userid'] = isset($config['userid']) ? (int)$config['userid'] : '';

        if (trim($this->conf['instance']) == '') {
            throw new Exception('Instance Name is mandatory (field: "instance")');
        }

        if (trim($this->conf['apiKey']) == '') {
            throw new Exception('API KEY is mandatory (field: "apiKey")');
        }

        if (trim($this->conf['LoginKey']) == '') {
            throw new Exception('LOGIN KEY is mandatory (field: "LoginKey")');
        }

        if (trim($this->conf['userid']) == '') {
            throw new Exception('USER ID is mandatory (field: "userid")');
        }
    }

    /**
     * Initialisation de l'URL
     **/
    private function requestUrlBuilder($request)
    {
        if (trim($this->_url) == '') {
            throw new Exception('API URL is not valid');
        }

        $this->url = $this->_url; // used 2 variables because of bug when we call API 2 or more times...

        if ($this->url{strlen($this->url) - 1} != '/') {
            $this->url .= '/';
        }

        $this->url .= $request;
    }

    /**
     * Initialisation du type de requête
     **/
    private function getRequestMode(array $params)
    {
        if (isset($params[1]) && !is_array($params[1]) && trim($params[1]) != '') {
            $mode = trim($params[1]);

            $mode = preg_replace('/([^a-z])/i', '', $mode);

            $request_mode = '';

            if ($mode == 'create') {
                $request_mode = 'POST';
            } elseif ($mode == 'update') {
                $request_mode = 'PUT';
            } elseif ($mode == 'get') {
                $request_mode = 'GET';
            } elseif ($mode == 'remove') {
                $request_mode = 'DELETE';
            } elseif ($mode == 'sendmail') {
                $request_mode = 'POST';
            }

            if (!in_array($request_mode, array('POST', 'PUT', 'GET', 'DELETE'))) {
                throw new Exception('REQUEST MODE is not allowed : ' . $mode);
            }

            $this->request_mode = $request_mode;
        } else {
            throw new Exception('REQUEST MODE is mandatory');
        }
    }

    /**
     * @param array $params
     */
    private function sendRequest(Array $params)
    {
        //DATA CONTENT
        $post_array = (isset($params[0]) && is_array($params[0]) && count($params[0]) > 0) ? $params[0] : array();

        //CURL
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $this->url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 2);

        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array(
            'Instance: '.$this->conf['instance'],
            'ApiKey: '.$this->conf['apiKey'],
            'LoginKey: '.$this->conf['LoginKey']
        ));
        
        if (($this->request_mode == 'POST') || ($this->request_mode == 'PUT')):
            curl_setopt($curl_handle, CURLOPT_POST, 1);
            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post_array);
        endif;

        if ($this->request_mode == 'DELETE') {
            curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "DELETE");
        }

        if ($this->request_mode == 'PUT') {
            curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "PUT");
        }

        $buffer = curl_exec($curl_handle);

        $return_code = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);

        $success_msg = false;

        if ($this->request_mode == 'POST' && $return_code == 201) {
            $success_msg = true;
        } elseif ($this->request_mode == 'DELETE' && $return_code == 204) {
            $success_msg = true;
        } elseif ($return_code == 200) {
            $success_msg = true;
        }
        
        $this->response = array(
            'url' => $this->url,
            'code' => $return_code,
            'message' => $buffer,
            'success' => $success_msg
        );

        curl_close($curl_handle);
    }
}