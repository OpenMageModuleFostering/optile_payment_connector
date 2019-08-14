<?php 
/**
 * This file is part of the Optile Payment Connector extension.
 *
 * Optile Payment Connector is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Optile Payment Connector is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Optile Payment Connector.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      i-Ways <dev@i-ways.hr>
 * @copyright   Copyright (c) 2013 Optile. (http://www.optile.de)
 * @license     http://www.gnu.org/licenses/gpl.txt
 */

$rootFolder = dirname(__FILE__).'/';

include_once $rootFolder.'ValidationService.php';
include_once $rootFolder.'Request/Validator.php';
include_once $rootFolder.'Request/Entity.php';
include_once $rootFolder.'Request/OptileAccount.php';
include_once $rootFolder.'Request/OptileUrlException.php';
include_once $rootFolder.'Request/RequestException.php';
include_once $rootFolder.'Request/OptileRequest.php';
include_once $rootFolder.'Request/OptileProduct.php';
include_once $rootFolder.'Request/OptilePayment.php';
include_once $rootFolder.'Request/OptileCustomer.php';
include_once $rootFolder.'Request/OptileCallback.php';
include_once $rootFolder.'Request/OptileConnection.php';
include_once $rootFolder.'Request/OptileListRequest.php';
include_once $rootFolder.'Request/OptileChargeRequest.php';
include_once $rootFolder.'Request/OptileReloadListRequest.php';
include_once $rootFolder.'Response/OptileResponseEntity.php';
include_once $rootFolder.'Response/OptileInteraction.php';
include_once $rootFolder.'Response/OptileNetwork.php';
include_once $rootFolder.'Response/OptileNetworkLink.php';
include_once $rootFolder.'Response/OptileRedirect.php';
include_once $rootFolder.'Response/OptileResponse.php';
include_once $rootFolder.'Response/OptileResponseFactory.php';
