<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2011, snowflake productions GmbH, Switzerland
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSD License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/

/**
 * Renderer for profile module of calendar preferences
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarProfileRenderer {

	/**
	 * Render tabs in general area
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public static function renderTabs(array $params) {
		$name		= 'calendar';
		$class		= 'calendar';

		$jsHandler	= 'Todoyu.Ext.calendar.Profile.onTabClick.bind(Todoyu.Ext.calendar.Profile)';

		$tabs		= TodoyuTabManager::getAllowedTabs(Todoyu::$CONFIG['EXT']['profile']['calendarTabs']);
		$active		= $params['tab'];

		if( is_null($active) ) {
			$active = $tabs[0]['id'];
		}

		return TodoyuTabheadRenderer::renderTabs($name, $tabs, $jsHandler, $active, $class);
	}



	/**
	 * Render tab content
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public static function renderContent(array $params) {
		$tab	= $params['tab'];

		switch($tab) {
			case 'main':
			default:
				return self::renderContentMain();
				break;

			case 'reminders':
				return self::renderContentReminders();
				break;
		}
	}



	/**
	 * Render content for profile's main tab of calendar section
	 *
	 * @return	String
	 */
	public static function renderContentMain() {
		$xmlPath= 'ext/calendar/config/form/profile-main.xml';
		$form	= TodoyuFormManager::getForm($xmlPath);

		$mailPopupDeactivated	= TodoyuCalendarPreferences::getPref('is_mailpopupdeactivated', 0, 0, false, personid());

		$formData	= array(
			'is_mailpopupdeactivated'	=> $mailPopupDeactivated ? true : false
		);
		$form->setFormData($formData);

		$tmpl	= 'ext/calendar/view/profile-main.tmpl';
		$data	= array(
			'name'	=> Todoyu::person()->getFullName(),
			'form'	=> $form->render()
		);

		return render($tmpl, $data);
	}



	/**
	 * Render content for profile's reminders tab of calendar section
	 *
	 * @return	String
	 */
	public static function renderContentReminders() {
		$xmlPath	= 'ext/calendar/config/form/profile-reminders.xml';
		$form		= TodoyuFormManager::getForm($xmlPath);
		$formData	= array();

			// Preset form data from prefs or remove disallowed prefs from form

			// Reminders send via email
		if( allowed('calendar', 'reminders:email') ) {
			$emailActive		= TodoyuCalendarPreferences::getPref('is_reminderemailactive', 0, 0, false, personid());
			$emailAdvanceTime	= TodoyuCalendarPreferences::getPref('reminderemail_advancetime', 0, 0, false, personid());

			$formData['is_reminderemailactive']		= $emailActive ? true : false;
			$formData['reminderemail_advancetime']	= intval($emailAdvanceTime);
		} else {
			$form->getFieldset('reminders')->removeField('is_reminderemailactive');
			$form->getFieldset('reminders')->removeField('reminderemail_advancetime');
		}
			// Reminders shown as popup
		if( allowed('calendar', 'reminders:popup') ) {
			$popupActive		= TodoyuCalendarPreferences::getPref('is_reminderpopupactive', 0, 0, false, personid());
			$popupAdvanceTime	= TodoyuCalendarPreferences::getPref('reminderpopup_advancetime', 0, 0, false, personid());

			$formData['is_reminderpopupactive']		= $popupActive ? true : false;
			$formData['reminderpopup_advancetime']	= intval($popupAdvanceTime);
		} else {
			$form->getFieldset('reminders')->removeField('is_reminderpopupactive');
			$form->getFieldset('reminders')->removeField('reminderpopup_advancetime');
		}

			// Set form data
		$form->setFormData($formData);

			// Render tab content
		$tmpl	= 'ext/calendar/view/profile-reminders.tmpl';
		$data	= array(
			'form'	=> $form->render()
		);

		return render($tmpl, $data);
	}

}

?>