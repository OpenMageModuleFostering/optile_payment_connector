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

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Create a table for keeping record of Optile IPNs
$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('optile/notification')} (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL COMMENT 'Date and time when notification was received',
  `received_data` text NOT NULL COMMENT 'Received data',
  `long_id` varchar(100) NOT NULL,
  `return_code` varchar(100) NOT NULL,
  `interaction_code` varchar(100) NOT NULL,
  `reason_code` varchar(100) NOT NULL,
  `result_info` varchar(100) NOT NULL,
  `status` int(11) NOT NULL COMMENT 'Notification status',
  PRIMARY KEY (`id`),
  KEY `long_id` (`long_id`),
  KEY `transaction_id` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");
$installer->run("
ALTER TABLE {$this->getTable('optile/quote')} ADD `deferred_mode` TINYINT NOT NULL DEFAULT '0'
");

$installer->endSetup();
