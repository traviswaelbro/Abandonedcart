<?php

class Mbs_Abandonedcart_Model_Observer {

    public function sendReminderEmails() {
    	Mage::log("Mbs Abandoned Cart Observer Fired by cron!",null,"test.log");

    	// Load Configuration Settings
    	$emailTemplate       = Mage::getModel('core/email_template');
		$translate           = Mage::getSingleton('core/translate');
		$templateId          = Mage::getStoreConfig('mbs_options/settings/template_id');

		// Get Email Template information
		$template_collection = $emailTemplate->load($templateId);
		$template_data       = $template_collection->getData();

		$adapter = Mage::getSingleton('core/resource')->getConnection('sales_read');

        $minutesFrom = Mage::getStoreConfig('mbs_options/settings/minutes_from');   // Get abandoned carts that were updated less than x minutes ago
        $from = $adapter->getDateSubSql(
            $adapter->quote(now()), 
            $minutesFrom, 
            Varien_Db_Adapter_Interface::INTERVAL_MINUTE
        );
        $minutesTo = Mage::getStoreConfig('mbs_options/settings/minutes_to');       // But more than y minutes ago
        $to = $adapter->getDateSubSql(
            $adapter->quote(now()),
            $minutesTo,
            Varien_Db_Adapter_Interface::INTERVAL_MINUTE
        );

        Mage::log("We want carts abandoned more than ".$minutesTo." minutes old but less than ".$minutesFrom." minutes old.",null,"test.log");

        // If there exists a quote (cart) that has been made in the desired range, add it to the collection
        $quotes = Mage::getResourceModel('sales/quote_collection')->addFieldToFilter('updated_at', array('from' => $from, 'to' => $to));

        foreach( $quotes as $quote ) {
            Mage::log("Quote Last Update At: " . $quote->getData( 'updated_at' ),null,"test.log");

            // Get orders and check if there is an order number that corresponds to the quote (cart) number
            $orders = Mage::getModel('sales/order')->getCollection()
                                                   ->addFieldToFilter( 'quote_id', $quote->getId() );

            Mage::log("Is there an order matching the quote? ".$orders->count(),null,"test.log");

            // If there is no order made, then send the reminder email
            if($orders->count() == 0 /*&& $quote->getCustomerEmail() == "twaelbroeck@gmail.com"*/) {
            	$customerEmail  = $quote->getCustomerEmail();
                $customerFirstName = $quote->getData('customer_firstname');
                $customerLastName = $quote->getData('customer_lastname');

                Mage::log("Let's send an email (template #".$templateId.") to ".$customerFirstName." at ".$customerEmail."!",null,"test.log");

				if(!empty($template_data))
				{
				    $mailSubject  = $templateData['template_subject'];
				    $storeId     = Mage::app()->getStore()->getStoreId();

				    // Fetch sender data from System > Configuration > Store Email Addresses > General Contact
				    $from_email  = Mage::getStoreConfig('trans_email/ident_general/email'); // Fetch sender email
				    $from_name   = Mage::getStoreConfig('trans_email/ident_general/name');  // Fetch sender name
				    $sender      = array('name'=> $from_name,
				                         'email' => $from_email);

				    // For replacing the variables in email with data: array('variable'=>'value' )
				    $vars        = array('customerFirstName' => $customerFirstName,
				    					 'customerLastName'  => $customerLastName
				    					 );

				    $model = $emailTemplate->setReplyTo($sender['email'])->setTemplateSubject($mailSubject);
				    // echo "template ".$templateId." subject ".$mailSubject." email ".$email." name ".$custoemrName." store id ".$storeId."<br>";
				    // var_dump($sender);
				    // var_dump($vars);
				    try {
				        $model->sendTransactional($templateId, $sender, $customerEmail, $customerFirstName, $vars, $storeId);
				    if(!$emailTemplate->getSentSuccess()) {
				        Mage::log("Something went wrong trying to send an email to ".$customerEmail."...",null,"test.log");
				    } else {
				        Mage::log("Abandoned Cart Message Sent to ".$customerEmail."!",null,"test.log");
				    }
				    $translate->setTranslateInline(true);
				    } catch(Exception $e) {
				       Mage::logException($e)  ;
				    }
				} else Mage::log("It seems template data is empty for template with id ".$template_id);
			}
		}
    }
}

?>