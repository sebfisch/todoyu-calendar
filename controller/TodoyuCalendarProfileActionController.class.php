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

		$prefName				= 'is_mailpopupdeactivated';
		$isRequestDeactivated	= ( $params['general']['is_mailpopupdeactivated'] == '1' ) ? 1 : 0;

		TodoyuCalendarPreferences::savePref($prefName, $isRequestDeactivated, 0, true, 0, Todoyu::personid());
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
		$prefName	= 'is_reminderemailactive';
		$prefValue	= $params['reminders'][$prefName] == '1' ? 1 : 0;
		TodoyuCalendarPreferences::savePref($prefName, $prefValue, 0, true, 0, Todoyu::personid());

		$prefName	= 'reminderemail_advancetime';
		$prefValue	= intval($params['reminders'][$prefName]);
		TodoyuCalendarPreferences::savePref($prefName, $prefValue, 0, true, 0, Todoyu::personid());

			// Popup reminder prefs
		$prefName	= 'is_reminderpopupactive';
		$prefValue	= $params['reminders'][$prefName] == '1' ? 1 : 0;
		TodoyuCalendarPreferences::savePref($prefName, $prefValue, 0, true, 0, Todoyu::personid());

		$prefName	= 'reminderpopup_advancetime';
		$prefValue	= intval($params['reminders'][$prefName]);
		TodoyuCalendarPreferences::savePref($prefName, $prefValue, 0, true, 0, Todoyu::personid());
	}



	/**
	 * Generate new calendar data access token
	 *
	 * @param	Array 	$params
	 */
	public function getTokenAction(array $params) {
		$idTokenType	= intval($params['type']);

		return TodoyuTokenManager::generateHash(EXTID_CALENDAR, $idTokenType, Todoyu::personid(), true);
	}



	/**
	 * Save calendar sharing tokens (tokens from session, which ones from $params)
	 *
	 * @param	Array $params
	 */
	public function saveSharingTokensAction(array $params) {
		$isChangedPersonalSharingToken		= intval($params['share']['is_tokenpersonal_changed']);
		$isChangedFreebusySharingToken	= intval($params['share']['is_tokenfreebusy_changed']);

			// Store changed tokens
		if( $isChangedPersonalSharingToken ) {
			TodoyuTokenManager::saveTokenFromSession(EXTID_CALENDAR, CALENDAR_TYPE_SHARINGTOKEN_PERSONAL);
		}
		if( $isChangedFreebusySharingToken ) {
			TodoyuTokenManager::saveTokenFromSession(EXTID_CALENDAR, CALENDAR_TYPE_SHARINGTOKEN_FREEBUSY);
		}
	}

}

?>