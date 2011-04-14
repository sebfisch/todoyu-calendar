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
 * Calendar Manager
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarManager {

	/**
	 * Get name of calendar mode from mode constant
	 *
	 * @param	Integer	$mode
	 * @return	String
	 */
	public static function getModeName($mode = CALENDAR_MODE_DAY) {
		if( is_string($mode) ) {
			return $mode;
		}

		$modes	= array(
			CALENDAR_MODE_DAY	=> 'day',
			CALENDAR_MODE_WEEK	=> 'week',
			CALENDAR_MODE_MONTH	=> 'month'
		);

		return $modes[$mode];
	}



	/**
	 * Get holidays in a timespan for the current holiday sets
	 *
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @return	Array
	 */
	public static function getHolidays($dateStart, $dateEnd) {
		$holidaySets	= self::getSelectedHolidaySets();

		if( sizeof($holidaySets) > 0 ) {
			$holidays	= TodoyuCalendarHolidayManager::getHolidaysInTimespan($dateStart, $dateEnd, $holidaySets);
			$grouped	= TodoyuCalendarHolidayManager::groupHolidaysByDays($holidays);
		} else {
			$grouped	= array();
		}

		return $grouped;
	}



	/**
	 * Get holidays for a day
	 *
	 * @param	Integer		$timestamp
	 * @return	Array
	 */
	public static function getHolidaysForDay($timestamp) {
		$dayRange	= TodoyuTime::getDayRange($timestamp);
		$holidays	= self::getHolidays($dayRange['start'], $dayRange['end']);

		$today		= $holidays[date('Ymd', $timestamp)];

		return is_array($today) ? $today : array();
	}



	/**
	 * Get amount of days between two week-day numbers (0-6)
	 *
	 * @param	Integer 	$startDay			Timestamp of the starting day
	 * @param	Integer 	$endDay				Timestamp of the ending day
	 * @param	Boolean		$insideTheSameWeek	If true, the two days are inside the same week
	 * @return	Integer
	 */
	public static function getAmountOfDaysInbetweenWeekdayNums($startDay, $endDay, $insideTheSameWeek = true) {
		if( $insideTheSameWeek ) {
				// Both days are within the same week
			$amount = ($endDay == 0 ? 7 : $endDay) - ($startDay == 0 ? 7 : $startDay) + 1;
		} else {
				// Days are not within the same week (spanning over tow or more weeks)
			if( $endDay != '' ) {
				$amount	= $endDay == 0 ? 7 : $endDay;
			} else {
				$amount	= $startDay != '' ? ($startDay == 0 ? 1 : 8 - $startDay) : false;
			}
		}

		return $amount;
	}



	/**
	 * Get amount of weeks visible in calendar depending on given amount of displayed days
	 *
	 * @param	Integer		$amountDays
	 * @return	Integer
	 */
	public static function getVisibleWeeksAmount($amountDays = 35) {
		if( $amountDays === 28 ) {
			$amount = 4;
		} elseif( $amountDays === 35 ) {
			$amount = 5;
		} else {
			$amount = 6;
		}

		return $amount;
	}



	/**
	 * Get various data related to month of given timestamp
	 *
	 * @param	Integer 	$timestamp		UNIX Timestamp of the selected date
	 * @return	Array
	 */
	public static function getMonthData($timestamp) {
		$month					= date('m', $timestamp);
		$year					= date('Y', $timestamp);
		$secondsOfMonth			= TodoyuTime::getDayRange(mktime(0, 0, 0, $month, 1, $year));

		$shownDaysOfLastMonth	= date('w', mktime(0, 0, 0, $month, 1, $year)) - 1;
		$shownDaysOfNextMonth	= 35 - (TodoyuTime::getDaysInMonth($timestamp)) - (TodoyuTime::getDaysInMonth($timestamp, -1));

		$eventsStart['date']	= $secondsOfMonth['start'] - $shownDaysOfLastMonth * TodoyuTime::SECONDS_DAY;
		$eventsStart['days']	= TodoyuTime::getDaysInMonth($timestamp) + $shownDaysOfLastMonth + $shownDaysOfNextMonth;

		return $eventsStart;
	}



	/**
	 * Get date range for month of the timestamp
	 * (include days of the previous and next month because of the calendar layout)
	 *
	 * @param	Integer		$time
	 * @return	Array
	 */
	public static function getMonthDisplayRange($timestamp) {
		$timestamp	= intval($timestamp);
		$monthRange	= TodoyuTime::getMonthRange($timestamp);

		return array(
			'start'	=> TodoyuTime::getWeekStart($monthRange['start']),
			'end'	=> TodoyuTime::getWeekStart($monthRange['end']) + 7 * TodoyuTime::SECONDS_DAY - 1
		);
	}



	/**
	 * Get timestamps shown in calendar month view (days of month before selected, of the selected and of the month after the selected month)
	 *
	 * Explanation:
	 * As the month view of the calendar displays 5 weeks from monday to sunday, there are always some days
	 * out of the months before and after the selected month being displayed, this function calculates their timestamps.
	 *
	 * @param	Integer	$activeDate	Unix timestamp (selected date)
	 * @return	Array				Timestamps of days to be shown in month view of calendar
	 */
	public static function getDayTimestampsForMonth($activeDate) {
		$activeDate	= intval($activeDate);
		$monthRange	= TodoyuTime::getMonthRange($activeDate);

		$weekDayStart	= date('w', $monthRange['start']);
		$weekDayEnd		= date('w', $monthRange['end']);

		$daysLastMonth	= ($weekDayStart + 6) % 7;
		$daysNextMonth	= (7 - $weekDayEnd) % 7;

		$viewStart		= TodoyuTime::addDays($monthRange['start'], -$daysLastMonth);
		$viewEnd		= TodoyuTime::addDays($monthRange['end'], $daysNextMonth);
		$currentDate	= $viewStart;
		$timestamps		= array();

		while( $currentDate <= $viewEnd ) {
			$timestamps[]	= $currentDate;
			$currentDate	= TodoyuTime::addDays($currentDate, 1);
		}

		return $timestamps;
	}



	/**
	 * Check whether overbooking (more than one event assigned to one person at the same time) is allowed
	 *
	 * @return	Boolean
	 */
	public static function isOverbookingAllowed() {
		$extConf	= TodoyuSysmanagerExtConfManager::getExtConf('calendar');

		return intval($extConf['allowoverbooking']) === 1;
	}



	/**
	 * Get context menu items
	 *
	 * @param	Integer	$timestamp
	 * @param	Array	$items
	 * @return	Array
	 */
	public static function getContextMenuItems($timestamp, array $items) {
		$allowed= array();
		$own	= Todoyu::$CONFIG['EXT']['calendar']['ContextMenu']['Area'];

		if( allowed('calendar', 'event:add') ) {
			$allowed[] = $own['add'];
		}

		return array_merge_recursive($items, $allowed);
	}



	/**
	 * Add reminder JS init to page
	 */
	public static function addReminderJsInitToPage() {
		if( allowed('calendar', 'reminders:popup') ) {
			$jsInitCode	= TodoyuCalendarReminderPopupManager::getReminderJsPageInit();

			if( $jsInitCode !== false ) {
				TodoyuPage::addJsOnloadedFunction($jsInitCode, 100);
			}
		}
	}



	/**
	 * Get calendar tabs configuration array
	 *
	 * @return	Array
	 */
	public static function getCalendarTabsConfig() {
		return TodoyuArray::assure(Todoyu::$CONFIG['EXT']['calendar']['tabs']);
	}



	/**
	 * Get currently selected persons (defined by the panel widget)
	 * If no person is selected, the current person will automatically be selected
	 *
	 * @return	Array
	 */
	public static function getSelectedPersons() {
		$widget	= TodoyuPanelWidgetManager::getPanelWidget('contact', 'StaffSelector');
		/**
		 * @var	TodoyuContactPanelWidgetStaffSelector	$widget
		 */
		return $widget->getSelectedPersons();



//		return TodoyuContactPanelWidgetStaffSelectorOLD::getSelectedPersons();
	}



	/**
	 * Get currently selected event types
	 *
	 * @return	Array
	 */
	public static function getSelectedEventTypes() {
		return TodoyuCalendarPanelWidgetEventTypeSelector::getSelectedEventTypes();
	}



	/**
	 * Get currently selected holiday sets
	 *
	 * @return	Array
	 */
	public static function getSelectedHolidaySets() {
		return TodoyuCalendarPanelWidgetHolidaySetSelector::getSelectedHolidaySetIDs();
	}



	/**
	 * Extend company address form (hooked into contact's form building)
	 *
	 * @param	TodoyuForm		$form			Address form object
	 * @return	TodoyuForm
	 */
	public static function modifyAddressFormfields(TodoyuForm $form, $index, array $params) {
		if( $params['field'] instanceof TodoyuFormElement ) {
			$parentForm	= $params['field']->getForm()->getName();

			if( $parentForm == 'company' ) {
					// Extend company record form with holiday set selector
				$xmlPath	= 'ext/calendar/config/form/addressholidayset.xml';
				$form->addElementsFromXML($xmlPath);
			}
		}

		return $form;
	}



	/**
	 * Get birthday persons in time range, grouped by day
	 * Subgroups are date keys in format Ymd
	 *
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @return	Array
	 */
	public static function getBirthdaysByDay($dateStart, $dateEnd) {
		$dateStart	= intval($dateStart);
		$dateEnd	= intval($dateEnd);

		$birthdaysByDay	= array();

		$birthdayPersons= TodoyuContactPersonManager::getBirthdayPersons($dateStart, $dateEnd);

		foreach($birthdayPersons as $birthdayPerson) {
			$dateKey = date('Ymd', $birthdayPerson['date']);

			$birthdaysByDay[$dateKey][] = $birthdayPerson;
		}

		return $birthdaysByDay;
	}



	/**
	 * Get day events mapping for week view
	 *
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @param	Array		$eventTypes
	 * @param	Array		$persons
	 * @return	Array
	 */
	public static function getDayEventsWeekMapping($dateStart, $dateEnd, array $eventTypes, array $persons) {
		$events			= TodoyuCalendarEventManager::getEventsInTimespan($dateStart, $dateEnd, $persons, $eventTypes, true);
		$rangeKeys		= self::getDayKeys($dateStart, $dateEnd);
		$mapping		= array();
		$emptyMap		= array();

		foreach($rangeKeys as $rangeKey) {
			$emptyMap[$rangeKey] = 0;
		}

		$mapping[] = $emptyMap;

		foreach($events as $event) {
			$eventDayKeys		= self::getDayKeys($event['date_start'], $event['date_end']);
			$event['dayLength']	= sizeof($eventDayKeys);
			$event['daysInView']= sizeof(TodoyuTime::getIntersectingDayTimestamps($dateStart, $dateEnd, $event['date_start'], $event['date_end']));
			$found				= false;

				// Check all map lines for an empty space
			foreach($mapping as $lineIndex => $lineMap) {
					// Check all days of the line
				foreach($eventDayKeys as $eventDayKey) {
					if( $lineMap[$eventDayKey] != 0 ) {
						continue 2;
					}
				}

					// If a free spot was found (loop not cancelled)
				$firstDayKey	= array_shift($eventDayKeys);
				$mapping[$lineIndex][$firstDayKey] = $event;
				$found	= true;

				foreach($eventDayKeys as $eventDayKey) {
					$mapping[$lineIndex][$eventDayKey] = 1;
				}
				ksort($mapping[$lineIndex]);

					// Free space found, stop checking
				break;
			}

			if( $found === false ) {
				$mapping[]	= $emptyMap;
				$newIndex	= sizeof($mapping)-1;

				$firstDayKey	= array_shift($eventDayKeys);
				$mapping[$newIndex][$firstDayKey] = $event;

				foreach($eventDayKeys as $eventDayKey) {
					$mapping[$newIndex][$eventDayKey] = 1;
				}

				ksort($mapping[$newIndex]);
			}
		}

		return $mapping;
	}



	/**
	 * Get day keys (format Ymd) for every day in a date range
	 *
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @return	Array
	 */
	public static function getDayKeys($dateStart, $dateEnd) {
		$keys	= array();
		$start	= TodoyuTime::getStartOfDay($dateStart);
		$end	= TodoyuTime::getEndOfDay($dateEnd);

		for($date = $start; $date <= $end; $date += TodoyuTime::SECONDS_DAY) {
			$keys[] = date('Ymd', $date);
		}

		return $keys;
	}



	/**
	 * Get autocomplete persons for events (only staff)
	 *
	 * @param	String		$input
	 * @param	Array		$formData
	 * @param	String		$name
	 * @return	Array
	 */
	public static function autocompleteEventPersons($input, array $formData, $name) {
		$items = array();

		$fieldsToSearchIn = array(
			'p.firstname',
			'p.lastname',
			'p.shortname'
		);
		$searchWords	= TodoyuArray::trimExplode(' ', $input, true);

		if( sizeof($searchWords) > 0 ) {
			$fields	= '	p.id';
			$table	= '	ext_contact_person p,
						ext_contact_mm_company_person mmcp,
						ext_contact_company c';
			$where	= '		c.is_internal	= 1
						AND	c.id			= mmcp.id_company
						AND p.id			= mmcp.id_person';
			$like	= Todoyu::db()->buildLikeQuery($searchWords, $fieldsToSearchIn);
			$where	.= ' AND ' . $like;
			$order	= '	p.lastname, p.firstname';

			$personIDs	= Todoyu::db()->getColumn($fields, $table, $where, '', $order, '', 'id');

			foreach($personIDs as $idPerson) {
				$items[$idPerson] = TodoyuContactPersonManager::getLabel($idPerson);
			}
		}

		return $items;
	}

}

?>