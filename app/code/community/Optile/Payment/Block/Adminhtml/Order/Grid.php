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

class Optile_Payment_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('optile_order_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('sales/order_grid_collection');
        /* @var $collection Mage_Sales_Model_Resource_Order_Grid_Collection */

        // Hardcoding fields here, so as not to be wasteful with memory.
        $collection->getSelect()
            ->reset(Varien_Db_Select::COLUMNS)
            ->columns(array(
                'entity_id',
                'increment_id',
                'created_at',
                'grand_total',
                'order_currency_code',
                'status',
                'billing_name',
            ))
            ->join(
                array('sfo' => $collection->getTable('sales/order')),
                'sfo.entity_id = main_table.entity_id',
                array()
            )
            ->join(
                array('oq' => $collection->getTable('optile/quote')),
                'oq.transaction_id = sfo.quote_id',
                array('transaction_id', 'long_id', 'monitor_link', 'payment_network')
            )
            ->join(
                array('pmt' => $collection->getTable('sales/order_payment')),
                'pmt.parent_id = main_table.entity_id',
                array()
            )
            ->where('pmt.method = "optile"')
        ;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
            'filter_index' => 'main_table.increment_id',
        ));

        $this->addColumn('transaction_id', array(
            'header'=> Mage::helper('optile')->__('Transaction #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'transaction_id',
            'filter_index' => 'oq.transaction_id',
        ));

        $this->addColumn('long_id', array(
            'header' => Mage::helper('optile')->__('optile Long Id'),
            'renderer' => 'optile/adminhtml_widget_grid_column_renderer_url',
            'index' => 'long_id',
            'filter_index' => 'oq.long_id',
            'url_index' => 'monitor_link',
            'attributes' => array(
                'title' => 'View in optile Monitor',
                'target' => '_blank',
            ),
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type' => 'datetime',
            'width' => '160px',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
            'filter_index' => 'main_table.billing_name',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'filter_index' => 'main_table.grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'type'  => 'options',
            'width' => '100px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        $this->addColumn('payment_network', array(
            'header' => Mage::helper('optile')->__('Payment Network'),
            'index' => 'payment_network',
            'filter_index' => 'oq.payment_network',
            'width' => '70px',
        ));
        if(Mage::getIsDeveloperMode()){
            $this->addDeveloperColumns();
        }

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    private function addDeveloperColumns(){
        $this->addColumn('action', array(
            'header'  => Mage::helper('optile')->__('IPN Simulate Action'),
            'width'   => '50px',
            'type'    => 'action',
            'getter'  => 'getTransactionId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('optile')->__('Simulate preauthorization canceled'),
                    'url'     =>
                        array(
                            'base'   => '*/*/simulate',
                            'params' => array('status_code'=>'canceled', 'reason_code'=>'preauthorization_canceled')
                        ),
                    'field'   => 'transaction_id'
                    ),
                array(
                    'caption' => Mage::helper('optile')->__('Simulate preauthorization expired'),
                    'url'     =>
                        array(
                            'base'   => '*/*/simulate',
                            'params' => array('status_code'=>'expired', 'reason_code'=>'preauthorization_expired')
                        ),
                    'field'   => 'transaction_id'
                    ),
                array(
                    'caption' => Mage::helper('optile')->__('Simulate preauthorization declined'),
                    'url'     =>
                        array(
                            'base'   => '*/*/simulate',
                            'params' => array('status_code'=>'declined', 'reason_code'=>'preauthorization_declined')
                        ),
                    'field'   => 'transaction_id'
                    ),
                array(
                    'caption' => Mage::helper('optile')->__('Simulate preauthorization failed'),
                    'url'     =>
                        array(
                            'base'   => '*/*/simulate',
                            'params' => array('status_code'=>'failed', 'reason_code'=>'preauthorization_failed')
                        ),
                    'field'   => 'transaction_id'
                    ),
                array(
                    'caption' => Mage::helper('optile')->__('Simulate preauthorized VISA'),
                    'url'     =>
                        array(
                            'base'   => '*/*/simulate',
                            'params' => array('status_code'=>'preauthorized', 'reason_code'=>'preauthorized', 'network'=>'VISA')
                        ),
                    'field'   => 'transaction_id'
                    ),
                ),
            'filter'    => false,
            'sortable'  => false,
        ));
    }

}
