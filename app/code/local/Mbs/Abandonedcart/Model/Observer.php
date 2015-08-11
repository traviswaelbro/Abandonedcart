<?php

class Mbs_Abandonedcart_Model_Observer {

    public function sendReminderEmails() {
        Mage::log("Mbs Abandoned Cart Observer Fired by cron!",null,"test.log");

        // Load Configuration Settings
        $emailTemplate       = Mage::getModel('core/email_template');
        $translate           = Mage::getSingleton('core/translate');
        $templateId          = Mage::getStoreConfig('mbs_options/settings/template_id');
        $recentlySentMinutes = Mage::getStoreConfig('mbs_options/settings/recently_sent');

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

        $createdAgo = Mage::getStoreConfig('mbs_options/settings/created_ago');
        Mage::log("Created ago original value is ".$createdAgo,null,"test.log");
        if($createdAgo) {
            $createdAgo = $minutesFrom + $createdAgo;
            Mage::log("Created ago updated value is ".$createdAgo,null,"test.log");
            $createdAgo = $adapter->getDateSubSql(
                $adapter->quote(now()),
                $createdAgo,
                Varien_Db_Adapter_Interface::INTERVAL_MINUTE);
            Mage::log("Created ago updated value is ".$createdAgo,null,"test.log");
        }

        Mage::log("We want carts abandoned more than ".$minutesTo." minutes old but less than ".$minutesFrom." minutes old.",null,"test.log");

        // If there exists a quote (cart) that has been made in the desired range, add it to the collection
        if($createdAgo) { // If created ago is not blank, then user wants all carts updated within the time frame that were created within a 2nd time frame
            $quotes = Mage::getResourceModel('sales/quote_collection')
                            ->addFieldToFilter('updated_at', array('from' => $from, 'to' => $to))
                            ->addFieldToFilter('created_at', array('from' => $createdAgo));
        } else { // If created ago is blank, then user wants all carts updated within the time frame
            $quotes = Mage::getResourceModel('sales/quote_collection')
                            ->addFieldToFilter('updated_at', array('from' => $from, 'to' => $to));
        }

        foreach( $quotes as $quote ) {
            Mage::log("Quote Created At: ".$quote->getData('created_at'),null,"test.log");
            Mage::log("Quote Last Update At: " . $quote->getData( 'updated_at' ),null,"test.log");


            // Get orders and check if there is an order number that corresponds to the quote (cart) number
            // $orders = Mage::getModel('sales/order')->getCollection()
            //                                        ->addFieldToFilter( 'quote_id', $quote->getId() );
            $orders = Mage::getModel('sales/order')->getCollection()
                                                   ->addFieldToFilter('customer_id', $quote->getCustomerId() )
                                                   ->addFieldToFilter('created_at', array('to' => $to));

            Mage::log("Is there an order matching the quote? ".$orders->count(),null,"test.log");

            // If there is no order made, then send the reminder email
            if($orders->count() == 0 && ($quote->getCustomerEmail() == "travis.w@mbs-standoffs.com" || $quote->getCustomerEmail == "twaelbroeck@gmail.com")) {
                $recentlySent = strtotime(Mage::getModel('core/date')->date('Y-m-d H:i:s')."-".$recentlySentMinutes); // Time in seconds
                $customerEmail  = $quote->getCustomerEmail();
                $customerFirstName = $quote->getData('customer_firstname');
                $customerLastName = $quote->getData('customer_lastname');

                $previousEmails = Mage::getModel('abandonedcart/abandonedcart')->getCollection()
                                      ->addFieldToFilter('email', array('eq' => $customerEmail));

                foreach ($previousEmails as $previousEmail) {
                    Mage::log(strtotime($previousEmail['sent_at']).".....".$recentlySent,null,"test.log");
                    if(strtotime($previousEmail['sent_at']) > $recentlySent) {
                        $abort = true;
                        Mage::log('Another email was sent to '.$customerEmail.' at '.$previousEmail['sent_at'],null,"test.log");
                    }
                }
                if($abort == false) {Mage::log("Let's send an email (template #".$templateId.") to ".$customerFirstName." at ".$customerEmail."!",null,"test.log");}

                if(!empty($template_data) && $abort == false)
                {
                    $mailSubject  = $templateData['template_subject'];
                    $storeId     = Mage::app()->getStore()->getStoreId();

                    // Fetch sender data from System > Configuration > Store Email Addresses > General Contact
                    $from_email  = Mage::getStoreConfig('mbs_options/settings/sender_email'); // Fetch sender email
                    $from_name   = Mage::getStoreConfig('mbs_options/settings/sender_name');  // Fetch sender name
                    $sender      = array('name'=> $from_name,
                                         'email' => $from_email);

                    // For replacing the variables in email with data: array('variable'=>'value' )
                    $vars        = array('customerFirstName' => $customerFirstName,
                                         'customerLastName'  => $customerLastName
                                         );

                    Mage::log("Template ".$templateId." from ".$sender['name']." ".$sender['email']." to ".$customerFirstName." ".$customerEmail." ".$storeId);
                    $model = $emailTemplate->setReplyTo($sender['email'])->setTemplateSubject($mailSubject);
                    // echo "template ".$templateId." subject ".$mailSubject." email ".$email." name ".$custoemrName." store id ".$storeId."<br>";
                    // var_dump($sender);
                    // var_dump($vars);
                    Mage::log("Template ".$templateId." from ".$sender['name']." ".$sender['email']." to ".$customerFirstName." ".$customerEmail." ".$storeId,null,"test.log");
                    try {
                        $model->sendTransactional($templateId, $sender, $customerEmail, $customerFirstName, $vars, $storeId);
                    if(!$emailTemplate->getSentSuccess()) {
                        $status = "Failed";
                        Mage::log("Something went wrong trying to send an email to ".$customerEmail."...",null,"test.log");
                    } else {
                        $status = "Success";
                        Mage::log("Abandoned Cart Message Sent to ".$customerEmail."!",null,"test.log");
                    }
                    

                    // Log the email attempt, whether or not it succeeded
                    $newRow = array(
                            'name'    => $customerFirstName." ".$customerLastName,
                            'email'   => $customerEmail,
                            'status'  => $status,
                            'sent_at' => Mage::getModel('core/date')->date('Y-m-d H:i:s')
                        );
                    $table = Mage::getModel('abandonedcart/abandonedcart')->setData($newRow);
                    try {
                        $insertId = $table->save()->getId();
                        Mage::log("Message with id ".$insertId." logged.",null,"test.log");
                    } catch (Exception $e) {
                        echo $e->getMessage();
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