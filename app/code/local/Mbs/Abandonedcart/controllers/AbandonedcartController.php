<?php

class Mbs_Abandonedcart_AbandonedcartController extends Mage_Adminhtml_Controller_Action {

	public function indexAction() {
		$this->_title($this->__('Abandoned Cart Emails'));
		$this->loadLayout();
		$this->_setActiveMenu('report/abandonedcart');
		$this->_addContent($this->getLayout()->createBlock('mbs_abandonedcart/adminhtml_abandonedcart_email'));
		$this->renderLayout();
	}

	public function gridAction() {
		$this->loadLayout();
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('mbs_abandonedcart/adminhtml_abandonedcart_email_grid')->toHtml()
			);
	}

}

?>