<?php

$installer = $this;
$installer->startSetup();

$installer->addAttribute('customer', 'optout', array(
	'type'     => 'int',
	'input'    => 'select',
	'backend'  => '',
	'frontend' => '',
	'label'    => 'Abandoned Cart Email Opt Out',
	'source'   => 'eav/entity_attribute_source_boolean',
	'visible'  => true,
	'required' => true,
	'unique'   => false,
	'default'  => false,
	'note'     => 'If Yes, customer will no longer receive any Abandoned Cart Email Reminders'
));

$attribute = Mage::getSingleton('eav/config')->getAttribute('customer','optout');

$used_in_forms   = array();
$used_in_forms[] = 'adminhtml_customer';

$attribute->setData('used_in_forms', $used_in_forms)
		  ->setData('is_used_for_customer_segment', true)
		  ->setData('is_system', 0)
		  ->setData('is_user_defined', 1)
		  ->setData('is_visible', 1)
		  ->setData('sort_order', 100);
$attribute->save();

$installer->endSetup();

?>