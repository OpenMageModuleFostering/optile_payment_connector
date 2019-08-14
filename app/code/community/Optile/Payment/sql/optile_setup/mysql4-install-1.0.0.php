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

// Create Optile table
$table = new Varien_Db_Ddl_Table;
$table
    ->setName($this->getTable('optile/quote'))
    ->setOption('type', 'InnoDB')
    ->setOption('charset', 'utf8')
    ->addColumn('transaction_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
    ))
    ->addColumn('long_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255)
    ->addColumn('payment_network', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255)
    ->addForeignKey(
        'FK_OPTILE_QUOTE_TRANSACTION_ID',
        'transaction_id',
        'sales_flat_quote',
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
;
$installer->getConnection()->createTable($table);

// Installation successful: notify admin to configure the module.
$inbox = Mage::getModel('adminnotification/inbox');
/* @var $inbox Mage_AdminNotification_Model_Inbox */
$inbox->add(
    Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR,
    'Optile Payment Extension: Please disable other payment methods',
    'Some payment methods other than Optile are still enabled. Please go to System Configuration to disable them by configuring the Optile Payment Extension.'
);

$installer->endSetup();
