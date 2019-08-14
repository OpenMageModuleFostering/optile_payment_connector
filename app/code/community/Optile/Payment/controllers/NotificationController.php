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

/**
 * Processes Optile payment status notification
 * Each time the status of a transaction changes, a back-channel notification is sent to the <notificationUrl> with additional parameters.
 * 
 * Note: notifications are sent in a clear way without any additional security.
 * HTTPS protocol should always be used in a production environment.
 * To allow notifications from Optile open payments servers to reach a merchant
 * system that is protected by firewall, the firewall has to be configured to
 * accept requests from Optile servers IP addresses.
 * In this case the following IPs should be used:
 * Test server (sandbox.oscato.com) => 78.46.61.206
 * Production server (oscato.com) => 213.155.71.162
 */
class Optile_Payment_NotificationController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $request = $this->getRequest();

        Mage::getModel('optile/notification')->processNotification($request);

    }
}