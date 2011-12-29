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
 * Calendar extension calendar renderer
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarCalendarRenderer {

	/**
	 * Render calendar day view
	 *
	 * @param	Integer		$timestamp		Selected date
	 * @return	String
	 */
	public static function renderCalendarDay($timestamp) {
		$timestamp		= intval($timestamp);
		$persons	= TodoyuCalendarManager::getSelectedPersons();
		$dayRange	= TodoyuTime::getDayRange($timestamp);

		$personColors	= TodoyuContactPersonManager::getSelectedPersonColor($persons);
		$eventTypes		= TodoyuCalendarManager::getSelectedEventTypes();

		$tmpl		= 'ext/calendar/view/calendar/day.tmpl';
		$data		= array(
			'timestamp'		=> $dayRange['start'],
			'fullDayView'	=> TodoyuCalendarPreferences::getFullDayView(),
			'dateKey'		=> date('Ymd', $dayRange['start']),
			'events'		=> self::preRenderEventsForDay($dayRange['start'], $eventTypes, $persons, $personColors),
			'dayEvents'		=> self::preRenderAllDayEvents(CALENDAR_MODE_DAY, $dayRange['start'], $dayRange['end'], $eventTypes, $persons),
			'personBirthdays'=> in_array(EVENTTYPE_BIRTHDAY, $eventTypes) ? self::preRenderPersonBirthdays($dayRange['start'], $dayRange['end'], CALENDAR_MODE_DAY) : '',
			'holidays'		=> TodoyuCalendarManager::getHolidays($dayRange['start'], $dayRange['end']),
			'title'			=> TodoyuCalendarViewHelper::getCalendarTitle($dayRange['start'], $dayRange['end'], CALENDAR_MODE_DAY)
		);

		return Todoyu::render($tmpl, $data);
	}



	/**
	 * Render calendar week view
	 *
	 * @param	Integer		$timestamp		Selected date
	 * @return	String
	 */
	public static function renderCalendarWeek($timestamp) {
		$timestamp	= intval($timestamp);

		$weekRange	= TodoyuTime::getWeekRange($timestamp);
		$dateStart	= $weekRange['start'];
		$dateEnd	= $weekRange['end'];

		$persons		= TodoyuCalendarManager::getSelectedPersons();
		$personColors	= TodoyuContactPersonManager::getSelectedPersonColor($persons);

		$eventTypes		= TodoyuCalendarManager::getSelectedEventTypes();

		$tmpl	= 'ext/calendar/view/calendar/week.tmpl';
		$data	= array(
			'timestamp'			=> $timestamp,
			'timestamp_today'	=> TodoyuTime::getStartOfDay(NOW),
			'timestamps'		=> TodoyuTime::getTimestampsForWeekdays($timestamp),
			'title'				=> TodoyuCalendarViewHelper::getCalendarTitle($dateStart, $dateEnd, CALENDAR_MODE_WEEK),
			'fullDayView'		=> TodoyuCalendarPreferences::getFullDayView(),
			'displayWeekend'	=> TodoyuCalendarPreferences::getIsWeekendDisplayed(),
			'events'			=> self::preRenderEventsDayAndWeek(CALENDAR_MODE_WEEK, $dateStart, $dateEnd, $eventTypes, $persons, $personColors),
			'dayEvents'			=> self::preRenderWeekDayEvents($dateStart, $dateEnd, $eventTypes, $persons),
			'birthdays'			=> in_array(EVENTTYPE_BIRTHDAY, $eventTypes) ? self::preRenderPersonBirthdays($dateStart, $dateEnd, CALENDAR_MODE_WEEK) : array(),
			'holidays'			=> TodoyuCalendarManager::getHolidays($dateStart, $dateEnd)
		);

		return Todoyu::render($tmpl, $data);
	}



	/**
	 * Render calendar month view
	 *
	 * @param	Integer		$timestamp		Selected date
	 * @return	String
	 */
	public static function renderCalendarMonth($timestamp) {
		$timestamp	= intval($timestamp);

		$monthRange	= TodoyuCalendarManager::getMonthDisplayRange($timestamp);
		$timestamps	= TodoyuCalendarManager::getDayTimestampsForMonth($monthRange['start'], $monthRange['end']);

		$persons		= TodoyuCalendarManager::getSelectedPersons();
        $personColors	= TodoyuContactPersonManager::getSelectedPersonColor($persons);

		$eventTypes	= TodoyuCalendarManager::getSelectedEventTypes();

		$tmpl		= 'ext/calendar/view/calendar/month.tmpl';
		$data		= array(
			'timestamp'			=> $timestamp,
			'timestamp_today'	=> TodoyuTime::getStartOfDay(NOW),
			'timestamps'		=> $timestamps,
			'visibleWeeks'		=> todoyuCalendarManager::getVisibleWeeksAmount(sizeof($timestamps)),
			'selMonth'			=> date('n', $monthRange['start'] + TodoyuTime::SECONDS_WEEK),
			'selYear'			=> date('Y', $monthRange['start'] + TodoyuTime::SECONDS_WEEK),
			'selMonthYear'		=> date('nY', $monthRange['start'] + TodoyuTime::SECONDS_WEEK),
			'title'				=> TodoyuCalendarViewHelper::getCalendarTitle($monthRange['start'], $monthRange['end'], CALENDAR_MODE_MONTH),
			'events'			=> self::preRenderEventsForMonth($monthRange['start'], $monthRange['end'], $eventTypes, $persons, $personColors),
			'dayEvents'			=> self::preRenderAllDayEvents(CALENDAR_MODE_MONTH, $monthRange['start'], $monthRange['end'], $eventTypes, $persons),
			'birthdays'			=> in_array(EVENTTYPE_BIRTHDAY, $eventTypes) ? self::preRenderPersonBirthdays($monthRange['start'], $monthRange['end'], CALENDAR_MODE_MONTH) : array(),
			'holidays'			=> TodoyuCalendarManager::getHolidays($monthRange['start'], $monthRange['end']),
		);

		return Todoyu::render($tmpl, $data);
	}



	/**
	 * Render events for day view
	 *
	 * @param	Integer		$dateStart
	 * @param	Array		$eventTypes
	 * @param	Array		$persons
	 * @param	Array		$personColors
	 * @return	Array		Pre-rendered events HTML
	 */
	public static function preRenderEventsForDay($dateStart, array $eventTypes, array $persons, array $personColors) {
		$dayRange	= TodoyuTime::getDayRange($dateStart);
		$dateKey	= date('Ymd', $dayRange['start']);
		$events		= self::preRenderEventsDayAndWeek(CALENDAR_MODE_DAY, $dayRange['start'], $dayRange['end'], $eventTypes, $persons, $personColors);

		return $events[$dateKey];
	}



	/**
	 * Render events for month view
	 *
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @param	Array		$eventTypes
	 * @param	Array		$persons
	 * @param	Array		$personColors
	 * @return	Array		Pre-rendered events HTML
	 */
	public static function preRenderEventsForMonth($dateStart, $dateEnd, array $eventTypes, array $persons, array $personColors) {
		$dateStart	= intval($dateStart);
		$dateEnd	= intval($dateEnd);

			// Get all events in current view
		$events		= TodoyuCalendarEventManager::getEventsInTimespan($dateStart, $dateEnd, $persons, $eventTypes, false);
			// Group the events by day
		$eventsByDay= TodoyuCalendarEventManager::groupEventsByDay($events, $dateStart, $dateEnd);

			// Render events array
		$renderedEvents	= array();
		foreach($eventsByDay as $dateKey => $eventsOfDay) {
			foreach($eventsOfDay as $event) {
				$renderedEvents[$dateKey][] = TodoyuCalendarEventRenderer::renderEvent($event, 'month');
			}
		}

		return $renderedEvents;
	}



	/**
	 * Render events for day or week view
	 *
	 * @param	Integer		$mode				CALENDAR_MODE_DAY / CALENDAR_MODE_WEEK
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @param	Array		$eventTypes
	 * @param	Array		$persons
	 * @param	Array		$personColors
	 * @return	Array       Pre-rendered events HTML
	 */
	public static function preRenderEventsDayAndWeek($mode, $dateStart, $dateEnd, array $eventTypes, array $persons, array $personColors) {
		$dateStart	= intval($dateStart);
		$dateEnd	= intval($dateEnd);

		if( $mode === CALENDAR_MODE_DAY ) {
			$eventFullWidth	= CALENDAR_DAY_EVENT_WIDTH;
		} else {
			$eventFullWidth	= TodoyuCalendarPreferences::getIsWeekendDisplayed() ? CALENDAR_WEEK_EVENT_WIDTH : CALENDAR_WEEK_FIVEDAY_EVENT_WIDTH;
		}

			// Get all events in timespan displayed current view
		$events	= TodoyuCalendarEventManager::getEventsInTimespan($dateStart, $dateEnd, $persons, $eventTypes, false);
			// Group the events by day
		$eventsByDay= TodoyuCalendarEventManager::groupEventsByDay($events, $dateStart, $dateEnd);
			// Add overlap information to events for each day
		$eventsByDay= TodoyuCalendarEventManager::addOverlapInformationToEvents($eventsByDay);

			// Render events array
		$renderedEvents	= array();
		foreach($eventsByDay as $dateKey => $eventsOfDay) {
			$dayTime = mktime(0, 0, 0, substr($dateKey, 4, 2), substr($dateKey, 6, 2), substr($dateKey, 0, 4));

			foreach($eventsOfDay as $event) {
					// Set with and left position based on the overlapping information
				$event['width']	= round($eventFullWidth / $event['_amountColumns'], 0);
				$event['left']	= round($eventFullWidth / $event['_amountColumns'], 0) * $event['_indexColumn'];

					// If the event started before the current day, set top = 0
				if( $event['date_start'] <= $dayTime ) {
					$event['top'] 	= 0;
				} else {
					$event['top']	= TodoyuCalendarEventRenderer::getTimeCoordinate($event['date_start']);
				}

				$event['height']	= TodoyuCalendarEventRenderer::getEventHeight($dayTime, $event['date_start'], $event['date_end']);

					// Add rendered event HTML to array
				$renderedEvents[$dateKey][] = TodoyuCalendarEventRenderer::renderEvent($event, $mode);
			}
		}

		return $renderedEvents;
	}



	/**
	 * Render all-day events for given calendar mode (day / week / month)
	 *
	 * @param	Integer		$mode		CALENDAR_MODE_DAY / ..WEEK / ..MONTH
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd	last shown date in current calendar view
	 * @param	Array		$eventTypes
	 * @param	Array		$persons
	 * @return	Array		Pre-rendered events HTML
	 */
	public static function preRenderAllDayEvents($mode, $dateStart, $dateEnd, array $eventTypes, array $persons) {
		$events	= TodoyuCalendarEventManager::getEventsInTimespan($dateStart, $dateEnd, $persons, $eventTypes, true);
		$grouped= TodoyuCalendarEventManager::groupEventsByDay($events, $dateStart, $dateEnd);

		$dayEvents	= array();
		$rendered	= array();

		foreach($grouped as $dateKey => $eventsOfDay) {
			foreach($eventsOfDay as $event) {
				if( ! in_array($event['id'], $rendered) || $mode === CALENDAR_MODE_MONTH ) {
					$rendered[] = $event['id'];

					$dayEvents[$dateKey][]	= TodoyuCalendarEventRenderer::renderAllDayEvent($mode, $event);
				}
			}
		}

		return $dayEvents;
	}



	/**
	 * Render all-day events of given types and persons, laying within weeks of given timespan
	 *
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @param	Array 		$eventTypes
	 * @param	Array		$persons
	 * @return	Array		Pre-rendered events HTML
	 */
	public static function preRenderWeekDayEvents($dateStart, $dateEnd, array $eventTypes, array $persons) {
		$mapping	= TodoyuCalendarManager::getDayEventsWeekMapping($dateStart, $dateEnd, $eventTypes, $persons);

		foreach($mapping as $index => $dayEvents) {
			foreach($dayEvents as $eventIndex => $dayEvent) {
				if( is_array($dayEvent) ) {
					$mapping[$index][$eventIndex]['html'] = TodoyuCalendarEventRenderer::renderAllDayEvent(CALENDAR_MODE_WEEK, $dayEvent);
				}
			}
		}

		return $mapping;
	}



	/**
	 * Render all birthdays in the range
	 * The birthday records are person records with the following special keys: age, year of birth
	 *
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @param	Integer		$mode
	 * @return	Array		Pre-rendered birthdays HTML, key: date, e.g. $birthdays[20091231][0] = array('id'=>...)
	 */
	public static function preRenderPersonBirthdays($dateStart, $dateEnd, $mode = CALENDAR_MODE_MONTH) {
		$dateStart	= intval($dateStart);
		$dateEnd	= intval($dateEnd);

		$tmpl			= 'ext/calendar/view/calendar/birthday.tmpl';
		$birthdaysByDay	= TodoyuCalendarManager::getBirthdaysByDay($dateStart, $dateEnd);

		$isWeekendDisplayed	= TodoyuCalendarPreferences::getIsWeekendDisplayed();

		foreach($birthdaysByDay as $dateKey => $birthdaysOfTheDay) {
			if( is_array($birthdaysOfTheDay) ) {
				foreach($birthdaysOfTheDay as $index => $data) {
					$data['fullname']		= $data['lastname'] . ', ' . $data['firstname'];
					$data['calendarMode']	= TodoyuCalendarManager::getModeName($mode);
					$data['titleCropLength']= $mode != CALENDAR_MODE_WEEK || $isWeekendDisplayed ? 16 : 24;

					$birthdaysByDay[$dateKey][$index] = Todoyu::render($tmpl, $data);
				}
			}
		}

		return $birthdaysByDay;
	}

}

?>