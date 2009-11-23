<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 snowflake productions gmbh
*  All rights reserved
*
*  This script is part of the todoyu project.
*  The todoyu project is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License, version 2,
*  (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html) as published by
*  the Free Software Foundation;
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Quick info action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarQuickinfoActionController extends TodoyuActionController {

	public function init() {
		restrict('calendar', 'event:quickinfo');
	}

	/**
	 * Get quickinfo for an event
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function eventAction(array $params) {
		$idEvent	= intval($params['key']);

		$tmpl	= 'ext/calendar/view/quickinfo-event.tmpl';
		$data	= TodoyuEventManager::getEventRecord($idEvent);

		$data['assignedUsers']	= TodoyuEventManager::getAssignedUsersOfEvent($idEvent);

		return render($tmpl, $data);
	}



	/**
	 * Get quickinfo for a holiday
	 *
	 * @param	Array		$params
	 */
	public function holidayAction(array $params) {
		$timestamp	= intval($params['key']);
		$holidays	= TodoyuCalendarManager::getHolidaysForDay($timestamp);

		$tmpl	= 'ext/calendar/view/quickinfo-holiday.tmpl';
		$data 	= array(
			'timestamp'	=> $timestamp,
			'holidays'	=> $holidays,
		);

		echo render($tmpl, $data);
	}

}

?>