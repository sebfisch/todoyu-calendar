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
 * Calendar Portal Manager
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarPortalManager {

	/**
	 * Get events for the portal tab
	 *
	 * @return	Array
	 */
	public static function getAppointments() {
		$weeksEvents= intval(Todoyu::$CONFIG['EXT']['calendar']['appointmentTabConfig']['weeksEvents']);

		$dateStart	= TodoyuTime::getStartOfDay(NOW);
		$dateEnd	= NOW + ($weeksEvents * TodoyuTime::SECONDS_WEEK);

		return TodoyuEventManager::getEventsInTimespan($dateStart, $dateEnd, array(personid()));
	}



	/**
	 * Get holidays for portal tab
	 *
	 * @return	Array
	 */
	public static function getHolidays() {
		$weeksHoliday 	= intval(Todoyu::$CONFIG['EXT']['calendar']['appointmentTabConfig']['weeksHoliday']);
		$endTime		= NOW + $weeksHoliday * TodoyuTime::SECONDS_WEEK;

		return TodoyuHolidayManager::getPersonHolidaysInTimespan(array(personid()), NOW, $endTime);
	}



	/**
	 * Get birthdays to be displayed in appointments tab in portal, for members of staff only!
	 *
	 * @return	Array
	 */
	public static function getBirthdays() {
		if( TodoyuEventRights::isAllowedSeeBirthdaysInPortal() ) {
			$weeksBirthday 	= intval(Todoyu::$CONFIG['EXT']['calendar']['appointmentTabConfig']['weeksBirthday']);
			$dateStart		= TodoyuTime::getStartOfDay();
			$dateEnd		= TodoyuTime::addDays($dateStart, $weeksBirthday * 7);

			return TodoyuPersonManager::getBirthdayPersons($dateStart, $dateEnd);
		}

		return array();
	}

}

?>