<?php 

class Mbs_Abandonedcart_Model_Adminhtml_System_Config_Backend_Abandonedcart_Cron extends Mage_Core_Model_Config_Data {

	const CRON_STRING_PATH = 'crontab/jobs/abandonedcart_cron/schedule/cron_expr';

	protected function _afterSave() {
		// Get Configuration Settings
		$time 			= $this->getData('groups/settings/fields/time/value');
		$everyXMinutes	= Mage::getStoreConfig('mbs_options/settings/per_hour');
		$cron_frequency = Mage::getStoreConfig('mbs_options/settings/frequency');

		// Get Cron Frequency Select Values (Custom defined)
		$frequencyPerHour = Mbs_Abandonedcart_Model_Adminhtml_System_Config_Source_Cron_Frequency::CRON_PER_HOUR;
		$frequencyHourly  = Mbs_Abandonedcart_Model_Adminhtml_System_Config_Source_Cron_Frequency::CRON_HOURLY;
		$frequencyDaily   = Mbs_Abandonedcart_Model_Adminhtml_System_Config_Source_Cron_Frequency::CRON_DAILY;
		$frequencyWeekly  = Mbs_Abandonedcart_Model_Adminhtml_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
		$frequencyMonthly = Mbs_Abandonedcart_Model_Adminhtml_System_Config_Source_Cron_Frequency::CRON_MONTHLY;

		$cronDayOfWeek	  = date('N');

		// * * * * * (Minute, Hour, Day of Month, Month of Year, Day of Week)
		$cronExprArray	  = array(
			($cron_frequency == $frequencyPerHour) 
				? '*/'.$everyXMinutes											// Minute
			   	: intval($time[1]),
			($cron_frequency == $frequencyHourly || $cron_frequency == $frequencyPerHour) 
				? '*' 															// Hour
				: intval($time[0]),	
			($cron_frequency == $frequencyMonthly) ? '1' : '*',					// Day of Month
			'*',																// Month of Year
			($cron_frequency == $frequencyWeekly)  ? '1' : '*',					// Day of Week
		);
		$cronExprString	  = join(' ', $cronExprArray);

		try {
			Mage::getModel('core/config_data')
				->load(self::CRON_STRING_PATH, 'path')
				->setValue($cronExprString)
				->setPath(self::CRON_STRING_PATH)
				->save();
		}
		catch (Exception $e) {
			throw new Exception(Mage::helper('cron')->__('Unable to save the cron expression.'));
		}
	}

}

?>