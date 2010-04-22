<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
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
	 * Get calendar title
	 *
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @param	Integer		$mode			CALENDAR_MODE_DAY / ..WEEK / ..MONTH
	 * @return	String
	 */
	public static function getCalendarTitle($dateStart, $dateEnd, $mode = CALENDAR_MODE_DAY) {
		$dateStart	= intval($dateStart);
		$dateEnd	= intval($dateEnd);

		switch($mode) {
			case CALENDAR_MODE_DAY:
				$title	= TodoyuTime::format($dateStart, 'DlongD2MlongY4') . ' (' . TodoyuTime::format($dateStart, 'calendarweek') . ')';
				break;

			case CALENDAR_MODE_WEEK:
				if( date('n', $dateStart) === date('n', $dateEnd) ) {
					$title = TodoyuTime::format($dateStart, 'day') . ' - ' . TodoyuTime::format($dateEnd, 'day') .  ' ' . TodoyuTime::format($dateStart, 'Mlong') . ' (' . TodoyuTime::format($dateStart, 'calendarweek') . ')';
				} else {
					$title = TodoyuTime::format($dateStart, 'MlongD2') . ' - ' . TodoyuTime::format($dateEnd, 'D2MlongY4') . ' (' . TodoyuTime::format($dateStart, 'calendarweek') . ')';
				}
				break;

			case CALENDAR_MODE_MONTH:
				$date	= $dateStart + TodoyuTime::SECONDS_WEEK;
				$kw1	= TodoyuTime::format($dateStart, 'calendarweek');
				$kw2	= TodoyuTime::format($dateEnd, 'calendarweek');
				$title	= TodoyuTime::format($date, 'MlongY4') . ' (' . $kw1 . ' - ' . $kw2 . ')';
				break;

			default:
				$title = 'Invalid mode';
		}

		return $title;
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

}

?>