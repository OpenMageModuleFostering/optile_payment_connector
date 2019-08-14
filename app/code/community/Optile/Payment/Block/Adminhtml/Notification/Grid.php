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

class Optile_Payment_Block_Adminhtml_Notification_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('optile_notification_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('optile/notification_collection');
        /* @var $collection Mage_Sales_Model_Resource_Order_Grid_Collection */

        $collection->getSelect()
            ->joinLeft(
                array('oo' => $collection->getTable('optile/quote')),
                'oo.transaction_id = main_table.transaction_id',
                array('monitor_link')
            );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'=> Mage::helper('sales')->__('Notification #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'id'
        ));

        $this->addColumn('transaction_id', array(
            'header'=> Mage::helper('sales')->__('Transaction #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'transaction_id',
            'filter_index' => 'main_table.transaction_id'
        ));

        $this->addColumn('date', array(
            'header' => Mage::helper('sales')->__('Date received'),
            'index' => 'date',
            'type' => 'datetime',
            'width' => '160px',
        ));

        $this->addColumn('long_id', array(
            'header' => Mage::helper('optile')->__('Optile Long Id'),
            'renderer' => 'optile/adminhtml_widget_grid_column_renderer_url',
            'index' => 'long_id',
            'filter_index' => 'main_table.long_id',
            'url_index' => 'monitor_link',
            'attributes' => array(
                'title' => 'View in optile Monitor',
                'target' => '_blank',
            ),
        ));

//        $this->addColumn('network', array(
//            'header' => Mage::helper('sales')->__('Payment network'),
//            'index' => 'network'
//        ));

//        $this->addColumn('currency', array(
//            'header' => Mage::helper('sales')->__('Currency'),
//            'index' => 'currency'
//        ));

//        $this->addColumn('amount', array(
//            'header' => Mage::helper('sales')->__('Amount'),
//            'type'  => 'number',
//            'index' => 'amount',
//        ));


        $this->addColumn('return_code', array(
            'header' => Mage::helper('sales')->__('Return code'),
            'index' => 'return_code',
        ));
        $this->addColumn('interaction_code', array(
            'header' => Mage::helper('sales')->__('Interaction code'),
            'index' => 'interaction_code',
        ));
        $this->addColumn('reason_code', array(
            'header' => Mage::helper('sales')->__('Reason code'),
            'index' => 'reason_code',
        ));
        $this->addColumn('result_info', array(
            'header' => Mage::helper('sales')->__('Result info'),
            'index' => 'result_info',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status code returned to Optile'),
            'type'  => 'number',
            'index' => 'status',
        ));
        
        
        $this->addColumn('action', array(
            'header'  => Mage::helper('optile')->__('IPN Process again'),
            'width'   => '50px',
            'type'    => 'action',
            'getter'  => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('optile')->__('Process IPN again'),
                    'url'     =>
                        array(
                            'base'   => '*/*/reprocess',
                        ),
                    'field'   => 'id'
                    ),
                ),
            'filter'    => false,
            'sortable'  => false,
        ));



        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('notification_id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

}
