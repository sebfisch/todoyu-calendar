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


	
	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function eventAction(array $params) {
		$idEvent	= intval($params['key']);
		$event		= TodoyuEventManager::getEventRecord($idEvent);
		
		$event['assignedUsers']	= TodoyuEventManager::getAssignedUsersOfEvent($idEvent);
		
		return render('ext/calendar/view/quickinfo-event.tmpl', $event);
	}

	
	
	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function holidayAction(array $params) {
		$dateStart	= intval($params['key']);
		$dateEnd	= $dateStart + 86400 -1;

		$holidays	= TodoyuCalendarManager::getHolidays($dateStart, $dateEnd);

		$data = array(
			'timestamp'	=> $dateStart,
			'holidays'	=> $holidays[ date('Ymd', $dateStart) ],
		);

		echo render('ext/calendar/view/quickinfo-holiday.tmpl', $data);
	}
		
}

?>