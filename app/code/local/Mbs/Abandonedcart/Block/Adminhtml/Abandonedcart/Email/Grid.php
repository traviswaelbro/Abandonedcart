<?php

class Mbs_Abandonedcart_Block_Adminhtml_Abandonedcart_Email_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('mbs_abandonedcart_email');
		$this->setDefaultSort('email_id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	protected function _prepareCollection() {
		$carts = Mage::getModel('abandonedcart/abandonedcart')->getCollection();

        $this->setCollection($carts);
        parent::_prepareCollection();
        return $this;
	}

	protected function _prepareColumns() {
		$helper = Mage::helper('mbs_abandonedcart');
		$currency = (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);

		$this->addColumn('email_id', array(
			'header' => $helper->__('Email ID'),
			'index'	 => 'email_id'
			));

		$this->addColumn('name', array(
			'header' => $helper->__('Customer Name'),
			'index'	 => 'name'
			));

		$this->addColumn('email', array(
			'header' => $helper->__('Customer Email'),
			'index'	 => 'email'
			));

		$this->addColumn('status', array(
			'header' => $helper->__('Status'),
			'index'  => 'status'
			));

		$this->addColumn('sent_at', array(
			'header' => $helper->__('Sent At'), 
			'index'  => 'sent_at'
			));

		return parent::_prepareColumns();
	}

	public function getGridUrl() {
		return $this->getUrl('*/*/grid', array('_current'=>true));
	}

}

?>