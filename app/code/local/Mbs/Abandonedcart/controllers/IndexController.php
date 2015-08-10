<?php
class Mbs_Abandonedcart_IndexController extends Mage_Core_Controller_Front_Action {
	public function indexAction() {
		$this->LoadLayout();
		$this->renderLayout();
	}
}