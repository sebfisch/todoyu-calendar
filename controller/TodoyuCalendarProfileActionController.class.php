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
 * Calendar profile action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarProfileActionController extends TodoyuActionController {

	/**
	 * Initialize calendar default action: check permission
	 *
	 * @param	Array	$params
	 */
	public function init(array $params) {
		Todoyu::restrict('calendar', 'general:use');
		Todoyu::restrictInternal();
	}



	/**
	 * Load tab content
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function tabAction(array $params) {
		return TodoyuCalendarProfileRenderer::renderContent($params);
	}



	/**
	 * Save calendar general preference from main tab of profile
	 *
	 * @param	Array		$params
	 */
	public function saveMainAction(array $params) {
		Todoyu::restrict('calendar', 'mailing:sendAsEmail');

		$formData				= TodoyuArray::assure($params['general']);
		$isEmailPopupDisabled	= intval($formData['is_mailpopupdeactivated']);

		TodoyuCalendarPreferences::savePref('is_mailpopupdeactivated', $isEmailPopupDisabled, 0, true);
	}



	/**
	 * Deactivate showing of mailing popup after drag and drop change of events
	 *
	 * @param	Array	$params
	 */
	public static function deactivatePopupPreferenceAction(array $params) {
		$prefName				= 'is_mailpopupdeactivated';
		$isRequestDeactivated	= '1';

		TodoyuCalendarPreferences::savePref($prefName, $isRequestDeactivated, 0, true, 0, Todoyu::personid());
	}



	/**
	 * Save calendar preference from reminders tab of profile
	 *
	 * @param	Array		$params
	 */
	public function saveRemindersAction(array $params) {
			// Email reminder prefs
		$prefName	= 'reminderemail_advancetime';
		$timeEmail	= intval($params['reminders'][$prefName]);
			// Popup reminder prefs
		$prefName	= 'reminderpopup_advancetime';
		$timePopup	= intval($params['reminders'][$prefName]);

		TodoyuCalendarPreferences::saveReminderEmailTime($timeEmail);
		TodoyuCalendarPreferences::saveReminderPopupTime($timePopup);
	}

}

?>