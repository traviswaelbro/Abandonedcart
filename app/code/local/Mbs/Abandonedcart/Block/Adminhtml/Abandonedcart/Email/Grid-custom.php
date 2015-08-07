<?php

class Mbs_Abandonedcart_Block_Adminhtml_Abandonedcart_Email_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	public function __construct() {
		parent::__construct();
		$this->setId('mbs_abandonedcart_email');
		$this->setDefaultSort('increment_id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	protected function _prepareCollection() {
		$quotes = Mage::getResourceModel('sales/quote_collection')
	        ->addFieldToFilter('converted_at', array('null' => true))
	        ->addFieldToFilter('customer_email', array('notnull' => true))
	        ->addFieldToFilter('items_count', array('gteq' => 1))
	        ->addFieldToFilter('customer_id', array('in' => $this->getCustomerIds()))
	        ->addFieldToFilter('is_active', 1);

        $this->setCollection($quotes);
        parent::_prepareCollection();
        return $this;
	}

	protected function _prepareColumns() {
		$helper = Mage::helper('mbs_abandonedcart');
		$currency = (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);

		$this->addColumn('customer_id', array(
			'header' => $helper->__('Customer Id'),
			'index'	 => 'customer_id'
			));

		$this->addColumn('customer_email', array(
			'header' => $helper->__('Email'),
			'index'	 => 'customer_email'
			));

		$this->addColumn('items_count', array(
			'header' => $helper->__('Number of Items'),
			'index'	 => 'items_count'
			));

		$this->addColumn('converted_at', array(
			'header' => $helper->__('Converted At'),
			'index'  => 'converted_at'
			));

		$this->addColumn('is_active', array(
			'header' => $helper->__('Is Active'), 
			'index'  => 'is_active'
			));

		return parent::_prepareColumns();
	}

	public function getGridUrl() {
		return $this->getUrl('*/*/grid', array('_current'=>true));
	}
}

?>