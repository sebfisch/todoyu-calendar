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
 * Calendar view helper
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarViewHelper {

	/**
	 * Get context menu header
	 *
	 * @param	Integer	$idEvent
	 * @return	String
	 */
	public static function getContextMenuHeader($idEvent) {
		$idEvent	= intval($idEvent);
		$event		= TodoyuEventManager::getEvent($idEvent);

		return TodoyuDiv::cropText($event->getTitle(), 24, '...', false);
	}



	/**
	 * Get calendar title
	 *
	 * @param	String	$mode
	 * @param	Integer	$dateStart
	 * @param	Integer	$dateEnd
	 * @return	String
	 */
	public static function getCalendarTitle($mode, $dateStart, $dateEnd) {
		$dateStart	= intval($dateStart);
		$dateEnd	= intval($dateEnd);

		switch($mode) {
			case 'day':
				$title	= TodoyuTime::format($dateStart, 'DlongD2MlongY4') . ' (' . TodoyuTime::format($dateStart, 'calendarweek') . ')';
				break;

			case 'week':
				if( date('n', $dateStart) === date('n', $dateEnd) ) {
					$title = date('j.', $dateStart) . ' - ' . TodoyuTime::format($dateEnd, 'D2MlongY4') . ' (' . TodoyuTime::format($dateStart, 'calendarweek') . ')';
				} else {
					$title = TodoyuTime::format($dateStart, 'MlongD2') . ' - ' . TodoyuTime::format($dateEnd, 'D2MlongY4') . ' (' . TodoyuTime::format($dateStart, 'calendarweek') . ')';
				}
				break;

			case 'month':
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
	 * @param	TodoyuForm $source
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
	 * @param	TodoyuForm $source
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
		$eventTypes	= TodoyuEventTypeManager::getEventTypes(true);
		$options	= array();

		foreach($eventTypes as $eventType) {
			$options[] = array(
				'value'	=> $eventType['index'],
				'label'	=> $eventType['label'],
			);
		}

		return $options;
	}

}

?>