<?php
/**
 * Access Amazon's API
 *
 * @category    Amazon
 * @package     Amazon_Login
 * @copyright   Copyright (c) 2014 Amazon.com
 * @license     http://opensource.org/licenses/Apache-2.0  Apache License, Version 2.0
 */

class Amazon_Payments_Model_Login extends Mage_Core_Model_Abstract
{

    private $http_client_config = array(
            'maxredirects' => 2,
            'timeout' => 30);

    protected function _construct()
    {
        $this->_init('amazon_login/login');
    }
    /**
     * Get config Client ID
     */
    public function getClientId()
    {
        return trim(Mage::getStoreConfig('amazon_login/settings/client_id'));
    }

    /**
     * Get config Client Secret
     */
    public function getClientSecret()
    {
        return trim(Mage::getStoreConfig('amazon_login/settings/client_secret'));
    }

    /**
     * Perform an API request to Amazon
     *
     * @param string $path
     *    REST path e.g. user/profile
     * @param array $postParams
     *    POST paramaters
     * @return result
     */
    public function request($path, array $postParams = array())
    {
        $sandbox = (Mage::getStoreConfig('payment/amazon_payments/sandbox')) ? 'sandbox.' : '';
        $region = Mage::getStoreConfig('payment/amazon_payments/region');
        $client = new Zend_Http_Client();
        if($region == 'jp') {
            if($sandbox) {
                $sandbox = '-'. $sandbox;
            } else {
                $sandbox = '.';
            }
            $client->setUri("https://api{$sandbox}amazon.co.jp/$path");
        } else {
            $client->setUri("https://api.{$sandbox}amazon.com/$path");
        }

        $client->setConfig($this->http_client_config);
        $client->setMethod($postParams ? 'POST' : 'GET');

        foreach ($postParams as $key => $value) {
            $client->setParameterPost($key, $value);
        }

        try {
            $response = $client->request();
        } catch (Zend_Http_Client_Adapter_Exception $e) {
            Mage::logException($e);
            return;
        }

        $data = $response->getBody();

        try {
            $data = Zend_Json::decode($data, true);
        }
        catch (Exception $e) {
            Mage::logException($e);
        }

        if (empty($data)) {
            return false;
        }
        else {
            return $data;
        }
    }
}