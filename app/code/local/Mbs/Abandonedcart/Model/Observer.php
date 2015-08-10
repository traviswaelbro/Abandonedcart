<?php

class Mbs_Abandonedcart_Model_Observer {

    public function sendReminderEmails() {

        Mage::log("Mbs Abandoned Cart Observer Fired by cron!",null,"test.log");

        // Load Configuration Settings
        $mailTemplate    = Mage::getSingleton('core/email_template');
        $translate       = Mage::getSingleton('core/translate'); 
        $emailTemplateId = Mage::getStoreConfig('mbs_options/settings/template_id');

        // Get Email Template information
        $templateCollection = $mailTemplate->load($emailTemplateId);
        $templateData       = $templateCollection->getData();

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
                $customerName = $quote->getName();

                Mage::log("Let's send an email to ".$customerName." at ".$email."!",null,"test.log");
                Mage::log("Template ID: ".$emailTemplateId,null,"test.log");

                if(!empty($templateData)) {
                    $templateId   = $templateData['template_id'];
                    $mailSubject  = $templateData['template_subject'];

                    // Fetch sender data from System > Configuration > Store Email Addresses > General Contact
                    $senderEmail  = Mage::getStoreConfig('trans_email/ident_general/email'); // Fetch sender email
                    $senderName   = Mage::getStoreConfig('trans_email/ident_general/name');  // Fetch sender name
                    $sender       = array('name'  => $senderName, 'email' => $senderEmail);

                    $vars         = null; //for replacing the variables in email with data: array('variable'=>'value' )
                    $storeId      = Mage::app()->getStore()->getId();

                    $model        = $mailTemplate->setReplyTo($sender['email'])->setTemplateSubject($mailSubject);

                    Mage::log("Sending Template with subject: ".$mailSubject." from ".$senderName." at ".$senderEmail." to ".$customerName." at ".$customerEmail,null,"test.log");
                    $model->sendTransactional($templateId,
                                            $sender, 
                                            $customerEmail, 
                                            $customerName, 
                                            $vars, 
                                            $storeId);

                    if (!$mailTemplate->getSentSuccess()) {
                        Mage::log("Something went wrong trying to send an email to ".$customerEmail."...",null,"test.log");
                        throw new Exception();
                    }
                    Mage::log("Abandoned Cart Message Sent to ".$customerEmail."!",null,"test.log");
                    $translate->setTranslateInline(true);
                } else {Mage::log("Template data empty!",null,"test.log");}
            }
        }
    }
}

?>