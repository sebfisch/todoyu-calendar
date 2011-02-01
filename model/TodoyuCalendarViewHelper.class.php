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
 * Calendar view helper
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarViewHelper {

	/**
	 * Render title (description of shown timespan) of given calendar view
	 *
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @param	Integer		$mode			CALENDAR_MODE_DAY / ..WEEK / ..MONTH
	 * @return	String
	 */
	public static function getCalendarTitle($dateStart, $dateEnd, $mode = CALENDAR_MODE_DAY) {
		$dateStart	= intval($dateStart);
		$dateEnd	= intval($dateEnd);
		$title		= '';

		if( $mode === CALENDAR_MODE_DAY ) {
			$format= label('calendar.calendartitle.dateformat.day');
			$title	.= strftime($format, $dateStart);
		} elseif( $mode === CALENDAR_MODE_WEEK ) {
			$title	.= strftime(label('calendar.calendartitle.dateformat.week.part1'), $dateStart);
			$title .= strftime(label('calendar.calendartitle.dateformat.week.part2'), $dateEnd);
		} elseif( $mode === CALENDAR_MODE_MONTH ) {
			$date	= $dateStart + TodoyuTime::SECONDS_WEEK;
			$title	.= strftime(label('calendar.calendartitle.dateformat.month.part1'), $date);
			$title	.= strftime(label('calendar.calendartitle.dateformat.month.part2'), $dateStart);
			$title	.= strftime(label('calendar.calendartitle.dateformat.month.part3'), $dateEnd);
		} else {
			$title = 'Invalid mode';
		}

		return TodoyuString::getAsUtf8($title);
	}



	/**
	 * Gets an options array of all defined holidays
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getHolidayOptions(TodoyuFormElement $field)	{
		$options = array();

		$holidays	= TodoyuHolidayManager::getAllHolidays();
		foreach($holidays as $holiday)	{
			$options[] = array(
				'value'	=> $holiday['id'],
				'label'	=> $holiday['title'] . ' (' . TodoyuTime::format($holiday['date'], 'D2M2Y4') . ')'
			);
		}

		return $options;
	}



	/**
	 * Gets an options array of all defined holidaySets
	 *
	 * @param	TodoyuFormElement		$field
	 * @return	Array
	 */
	public static function getHolidaySetOptions(TodoyuFormElement $field)	{
		$options = array();

		$holidaySets	= TodoyuHolidaySetManager::getAllHolidaySets();
		foreach($holidaySets as $set)	{
			$options[] = array(
				'value'	=> $set['id'],
				'label'	=> $set['title']
			);
		}

		return $options;
	}



	/**
	 * Get options of event types
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getEventTypeOptions(TodoyuFormElement $field) {
		return TodoyuEventViewHelper::getEventTypeOptions($field);
	}



	/**
	 * Get options for reminder interval
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getReminderOptions(TodoyuFormElement $field, $idEvent) {
		$idEvent	= intval($idEvent);

		$event		= TodoyuEventManager::getEvent($idEvent);
		$timeLeft	=  $event->getStartDate() - NOW;
		$options	= array();

		$intervals	= Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_RESCHEDULEINTERVALS'];
		foreach($intervals as $minutes) {
			$minutesUntil	= TodoyuTime::SECONDS_MIN * $minutes;
			if( $timeLeft > $minutesUntil ) {
				$options[] = array(
					'value'	=> $minutesUntil,
					'label'	=> $minutes . ' Minutes'
				);
			}
		}

		return $options;
	}

}

?>