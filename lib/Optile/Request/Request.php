<?php
/**
 * Copyright optile GmbH 2013
 * Licensed under the Software License Agreement in effect between optile and
 * Licensee/user (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at
 * http://www.optile.de/software-license-agreement; in addition, a countersigned
 * copy has been provided to you for your records. Unless required by applicable
 * law or agreed to in writing or otherwise stipulated in the License, software
 * distributed under the License is distributed on an "as is” basis without
 * warranties or conditions of any kind, either express or implied.  See the
 * License for the specific language governing permissions and limitations under
 * the License.
 *
 * @author      i-Ways <dev@i-ways.hr>
 * @copyright   Copyright (c) 2013 optile GmbH. (http://www.optile.de)
 * @license     http://www.optile.de/software-license-agreement
 */

namespace Optile\Request;
require_once("Logger.php");

/**
 * Request
 *
 * Abstract class that defines how the request is being executed to the Optile.
 * It's subclasses should implement send() method and handle the response.
 * For more info, refer to the ListRequest, ChargeRequest, CloseRequest, ... clases
 */
abstract class Request extends Component {

    const METHOD_POST = 'post';
    const METHOD_GET = 'get';
    const METHOD_DELETE = 'delete';

    protected $url;
    protected $urlSuffix = '';
    protected $merchantCode;
    protected $merchantToken;
    protected $use_cache = false;
    protected $cache_lifetime = 3600;

    /**
     * @param string $url
     */
    public function __construct($url) {
        $this->url = (string) $url;
    }

    public function setUseCache($use_cache){
        $this->use_cache = $use_cache;
        return $this;
    }
    public function setCacheLifetime($cache_lifetime){
        $this->cache_lifetime = $cache_lifetime;
        return $this;
    }

    public function setMerchantCode($merchantCode){
        $this->merchantCode = $merchantCode;
        return $this;
    }

    public function setMerchantToken($merchantToken){
        $this->merchantToken = $merchantToken;
        return $this;
    }

    // @TODO: this should probably not be part of the generic Request object.
    public function setUrl($value) {
        $this->url = rtrim($value, '/');
        return $this;
    }


    public function send($method){
        if (!isset($this->url) || !strlen($this->url)) {
            $e = RequestFactory::getException('url', 'URL must be set');
            throw $e;
        }

        if($this->use_cache == true){
            //Try to get from cache
            Logger::log(__METHOD__.": Retrieving from cache");
            $cache_key = md5($this->url);
            $result = Cache::load($cache_key);

            if($result === false){
                Logger::log(__METHOD__.": No cached version found, doing a cURL call");
                $result = $this->_send($method);
                $success = Cache::save($cache_key, $result, $this->cache_lifetime);
                Logger::log(__METHOD__.": Caching success: ".($success ? "YES" : "NO"));
            }

            return $result;
        } else {
            Logger::log(__METHOD__.": Sending request");
            return $this->_send($method);
        }

    }

    /**
     * Send request to Optile and return response.
     * @param string $method post|get
     * @return string
     * @throws @static.mtd:OptileRequestFactory.getException
     */
    public function _send($method) {

        $url = rtrim($this->url, '/') . $this->urlSuffix;
        $data = $this->getValidatedData();
        $message = $this->serialize($data, $method);

        $curlOpts = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT => 30
        );

        if ($method == self::METHOD_POST) {
            $curlOpts += array(
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $message,
                CURLOPT_HEADER => 1,
                CURLOPT_VERBOSE => 0,
                CURLOPT_HTTPHEADER => array(
                    'Content-length:'. strlen($message),
                    'Accept:application/vnd.optile.payment.enterprise-v1-extensible+json', //T: should we change this to simple-v1-extensible ?
                    'Content-type:application/vnd.optile.payment.enterprise-v1-extensible+json',
                ),
            );
        } elseif ($method == self::METHOD_DELETE){

            $curlOpts += array(
                CURLOPT_HTTPHEADER => array(
                    'Accept:application/vnd.optile.payment.enterprise-v1-extensible+json', //T: should we change this to simple-v1-extensible ?
                    'Content-type:application/vnd.optile.payment.enterprise-v1-extensible+json',
                    ),
                CURLOPT_CUSTOMREQUEST => 'DELETE',
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,
            );
        } else {
            $url .= $message;
        }

        Logger::log(__METHOD__.': URL: '.$url);
        Logger::log(__METHOD__.': Request: '.$message);

        if(isset($this->merchantCode, $this->merchantToken)) {
            $curlOpts[CURLOPT_USERPWD] = $this->merchantCode.'/'.$this->merchantToken;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, $curlOpts);

        $response = (string) curl_exec($ch);
        $response_info = curl_getinfo($ch);
        $response_error = curl_error($ch);

        Logger::log(__METHOD__.': Response: '.$response. ', Response error: ' . $response_error);
        Logger::log(__METHOD__.': Auth: '. $curlOpts[CURLOPT_USERPWD] = $this->merchantCode.'/'.$this->merchantToken);


        curl_close($ch);

        if($response_info['http_code'] >= 500){
//            return null;
            Logger::log(__METHOD__.': Response info: ');
            Logger::log($response_info);
            throw new \Exception("Optile server error");
        }
        $parts = explode("\r\n", $response);
        $result = array_pop($parts);

        return $result;
    }

    protected function serialize(array $data, $method) {
        switch ($method) {
            case self::METHOD_GET:
                return $this->serializeForGet($data);

            case self::METHOD_POST:
                return $this->serializeForPost($data);

            case self::METHOD_DELETE:
                return $this->serializeForPost($data);
        }
    }

    protected function serializeForPost(array $data) {
        return json_encode($data, JSON_NUMERIC_CHECK);
    }

    protected function serializeForGet(array $data) {
        if (empty($data))
            return '';

        $kvPairs = array();
        foreach ($data as $key => $value) {
            $kvPairs[] = $key .'='. urlencode($value);
        }

        $query = '?'. implode('&', $kvPairs);

        return $query;
    }

}
