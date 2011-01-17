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
 * Calendar action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarCalendarActionController extends TodoyuActionController {

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
	 * Calendar update action method: Saves date and active tab and rerenders the calendar
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function updateAction(array $params) {
		$tab	= trim($params['tab']);
		$time	= strtotime($params['date']);

		TodoyuPanelWidgetCalendar::saveDate($time);
		TodoyuCalendarPreferences::saveActiveTab($tab);

		$calendar = TodoyuCalendarRenderer::renderCalendar($time, $tab);

		return $calendar;
	}

}

?>