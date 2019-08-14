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

require_once 'Component.php';
require_once 'Request.php';
/**
 * RequestFactory
 *
 * Factory class for various Optile Request handlers. This should be a starting
 * point when generating a request to Optile API.
 * For more iformation, see methods below.
 */
class RequestFactory {

    /**
     * @param string $apiUrl URL (domain) to use as base for request URL.
     * @return \Optile\Request\ListRequest
     */
    public static function getListRequest($apiUrl) {
        require_once 'ListRequest.php';
        $url = rtrim($apiUrl, '/');
        return new ListRequest($url);
    }

    /**
     * @param string $url URL to request.
     * @return \Optile\Request\ReloadListRequest
     */
    public static function getReloadListRequest($url) {
        require_once 'SimpleRequest.php';
        require_once 'ReloadListRequest.php';
        return new ReloadListRequest($url);
    }

    /**
     * @param string $apiUrl URL (domain) to use as base for request URL.
     * @return \Optile\Request\ChargeRequest
     */
    public static function getChargeRequest($apiUrl) {
        require_once 'ChargeRequest.php';
        $url = rtrim($apiUrl, '/');
        return new ChargeRequest($url);
    }

    /**
     * @param string $apiUrl URL (domain) to use as base for request URL.
     * @return \Optile\Request\ListRequest
     */
    public static function getCloseRequest($apiUrl) {
        require_once 'CloseRequest.php';
        $url = rtrim($apiUrl, '/');
        return new CloseRequest($url);
    }

    /**
     * @param string $apiUrl URL (domain) to use as base for request URL.
     * @return \Optile\Request\ListRequest
     */
    public static function getCancelRequest($apiUrl) {
        require_once 'CancelRequest.php';
        $url = rtrim($apiUrl, '/');
        return new CancelRequest($url);
    }

    /**
     * @param string $apiUrl URL (domain) to use as base for request URL.
     * @return \Optile\Request\PayoutRequest
     */
    public static function getPayoutRequest($apiUrl) {
        require_once 'PayoutRequest.php';
        $url = rtrim($apiUrl, '/');
        return new PayoutRequest($url);
    }

    /**
     * @param string $url
     * @return \Optile\Request\SimpleRequest
     */
    public static function getSimpleRequest($url) {
        require_once 'SimpleRequest.php';
        return new SimpleRequest($url);
    }

    /**
     * @param string $component
     * @param string $subcomponent
     * @return \Optile\Request\class
     */
    public static function getComponent($component, $subcomponent='') {
        $filename = ucfirst($component).'.php';
        $class = '\Optile\Request\\'.ucfirst($component) . ucfirst($subcomponent);

        require_once $filename;
        return new $class();
    }

    /**
     * @param string $name
     * @param mixed $message
     * @param int $code [optional]
     * @param \Exception $previous [optional]
     * @return \Exception
     */
    public static function getException($name, $message, $code=null, $previous=null) {
        $filename = 'Exception.php';
        $class = '\Optile\Request\\'.ucfirst($name) .'Exception';

        require_once $filename;
//        if(class_exists($class)){
//            return new $class($message, $code, $previous);
//        }
//        else{
            return new Exception($message, $code, $previous);
//        }

    }

    public static function setLogger($logger){
        require_once 'Logger.php';
        Logger::setLogger($logger);
    }
    public static function setCacher($cacher){
        require_once 'Cache.php';
        Cache::setCacher($cacher);
    }

}
