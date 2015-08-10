<?php 

class Mbs_Abandonedcart_Block_Adminhtml_Abandonedcart_Email extends Mage_Adminhtml_Block_Widget_Grid_Container {
	public function __construct() {
		$this->_blockGroup = 'mbs_abandonedcart';
		$this->_controller = 'adminhtml_abandonedcart_email';
		$this->_headerText = Mage::helper('mbs_abandonedcart')->__('Abandoned Cart Email');

		parent::__construct();
		$this->_removeButton('add');
	}
}

?>