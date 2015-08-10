<?php
class Mbs_Abandonedcart_Block_Abandonedcart extends Mage_Core_Block_Template {
	protected $_Collection = null;
	public function getCollection() {
		if(is_null($this->_Collection)) {
			$this->_Collection = Mage::getModel('abandonedcart/abandonedcart')->getCollection();
		}
		return $this->_Collection;
	}
}