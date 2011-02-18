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
		restrict('calendar', 'general:use');
		restrictInternal();
	}



	/**
	 * Save calendar preference from profile
	 *
	 * @param	Array		$params
	 * @return	String		Form HTML or bookmark ID
	 */
	public function saveMainAction(array $params) {
		restrict('calendar', 'mailing:sendAsEmail');

		$prefName				= 'is_mailpopupdeactivated';
		$isRequestDeactivated	= ( $params['general']['is_mailpopupdeactivated'] == '1' ) ? 1 : 0;

		TodoyuCalendarPreferences::savePref($prefName, $isRequestDeactivated, 0, true, 0, personid());
	}



	/**
	 * Deactivate showing of mailing popup after drag and drop change of events
	 *
	 * @param	Array	$params
	 */
	public static function deactivatePopupPreferenceAction(array $params) {
		$prefName				= 'is_mailpopupdeactivated';
		$isRequestDeactivated	= '1';

		TodoyuCalendarPreferences::savePref($prefName, $isRequestDeactivated, 0, true, 0, personid());
	}

}

?>