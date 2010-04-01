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
 * Calendar Renderer
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarRenderer {

	/**
	 * Render the whole calendar (header, tabs and the actual calendar grid)
	 *
	 * @param	String	$activeTab		Displayed tab
	 * @param	Array	$params			Request parameters sub functions
	 * @return	String	Code of the calendar
	 */
	public static function render($activeTab = '', array $params = array()) {
		$time	= TodoyuPanelWidgetCalendar::getDate(EXTID_CALENDAR);

			// Get tab from preferences if not set
		if( empty($activeTab) ) {
			$activeTab	= TodoyuCalendarPreferences::getActiveTab();
		}

		$tmpl	= 'ext/calendar/view/main.tmpl';
		$data	= array(
			'active'		=> $activeTab,
			'content'		=> self::renderCalendar($time, $activeTab, $params),
			'showCalendar'	=> ( in_array($activeTab, array('day', 'week', 'month')) ) ? true : false
		);

			// If event-view is selected, set date and add it to data array
		if( $activeTab === 'view' ) {
			$event	= TodoyuEventManager::getEvent($params['event']);
			TodoyuPanelWidgetCalendar::saveDate($event->getStartDate());
			$data['date']	= $event->getStartDate();
		}

		return render($tmpl, $data);
	}



	/**
	 * Render content of the calendar modes (day / week / month)
	 *
	 * @param	Integer		$time
	 * @param	String		$activeTab
	 * @param	Array		$params
	 * @return	String
	 */
	public static function renderCalendar($time, $activeTab, array $params = array()) {
		$time	= intval($time);

		switch( $activeTab ) {
			case 'day':
				return self::renderCalendarDay($time);
				break;

			case 'week':
				return self::renderCalendarWeek($time);
				break;

			case 'month':
				return self::renderCalendarMonth($time);
				break;

			case 'view':
				$idEvent	= intval($params['event']);
				return TodoyuEventRenderer::renderEventView($idEvent);
				break;

			default:
				return 'Invalid type';
		}
	}



	/**
	 * Render calendar view for day view
	 *
	 * @param	Integer		$time		UNIX timestamp of selected date
	 * @return	String
	 */
	public static function renderCalendarDay($time) {
		$time		= intval($time);
		$persons	= TodoyuCalendarManager::getSelectedPersons();
		$dayRange	= TodoyuTime::getDayRange($time);
		$dateStart	= $dayRange['start'];
		$dateEnd	= $dayRange['end'];

		$personColors	= TodoyuPersonManager::getSelectedPersonColor($persons);
		$eventTypes		= TodoyuCalendarManager::getSelectedEventTypes();

		$tmpl		= 'ext/calendar/view/calendar-day.tmpl';
		$data		= array(
			'timestamp'		=> $dateStart,
			'fullDayView'	=> TodoyuCalendarPreferences::getFullDayView(),
			'dateKey'		=> date('Ymd', $dateStart),
			'events'		=> self::preRenderEventsForDay($dateStart, $eventTypes, $persons, $personColors),
			'dayEvents'		=> self::preRenderDayevents(CALENDAR_MODE_DAY, $dateStart, $dateEnd, $eventTypes, $persons),
			'personBirthdays'=> in_array(EVENTTYPE_BIRTHDAY, $eventTypes) ? self::preRenderPersonBirthdays($dateStart, $dateEnd, CALENDAR_MODE_DAY) : '',
			'holidays'		=> TodoyuCalendarManager::getHolidays($dateStart, $dateEnd),
			'title'			=> TodoyuCalendarViewHelper::getCalendarTitle($dateStart, $dateEnd, CALENDAR_MODE_DAY)
		);

		return render($tmpl, $data);
	}



	/**
	 * Render calendar view for week view
	 *
	 * @param	Integer		$time		UNIX timestamp of selected date
	 * @return	String
	 */
	public static function renderCalendarWeek($time) {
			// Get display infos
		$time		= intval($time);
		$persons	= TodoyuCalendarManager::getSelectedPersons();
		$weekRange	= TodoyuTime::getWeekRange($time);
		$dateStart	= $weekRange['start'];
		$dateEnd	= $weekRange['end'];

		$personColors	= TodoyuPersonManager::getSelectedPersonColor($persons);
		$eventTypes		= TodoyuCalendarManager::getSelectedEventTypes();

		$tmpl		= 'ext/calendar/view/calendar-week.tmpl';
		$data		= array(
			'timestamps'		=> TodoyuTime::getDayTimesOfWeek($time),
			'fullDayView'		=> TodoyuCalendarPreferences::getFullDayView(),
			'timestamp'			=> $time,
			'timestamp_today'	=> TodoyuTime::getStartOfDay(NOW),
			'events'			=> self::preRenderEventsForWeek($dateStart, $eventTypes, $persons, $personColors),
			'dayEvents'			=> self::preRenderDayevents(CALENDAR_MODE_WEEK, $dateStart, $dateEnd, $eventTypes, $persons),
			'birthdays'			=> in_array(EVENTTYPE_BIRTHDAY, $eventTypes) ? self::preRenderPersonBirthdays($dateStart, $dateEnd, CALENDAR_MODE_WEEK) : array(),
			'holidays'			=> TodoyuCalendarManager::getHolidays($dateStart, $dateEnd),
			'title'				=> TodoyuCalendarViewHelper::getCalendarTitle($dateStart, $dateEnd, CALENDAR_MODE_WEEK)
		);

		return render($tmpl, $data);
	}



	/**
	 * Render calendar view for month view
	 *
	 * @param	Integer		$time		UNIX timestamp of selected date
	 * @return	String
	 */
	public static function renderCalendarMonth($time) {
		$time	= intval($time);
		$persons		= TodoyuCalendarManager::getSelectedPersons();

		$monthRange	= TodoyuCalendarManager::getMonthDisplayRange($time);

		$dateStart	= $monthRange['start'];
		$dateEnd	= $monthRange['end'];

		$personColors	= TodoyuPersonManager::getSelectedPersonColor($persons);
		$eventTypes		= TodoyuCalendarManager::getSelectedEventTypes();

		$tmpl		= 'ext/calendar/view/calendar-month.tmpl';
		$data		= array(
			'timestamps'		=> TodoyuCalendarManager::getShownDaysTimestampsOfMonthView($time),
			'timestamp'			=> $time,
			'selMonth'			=> date('n', $dateStart + TodoyuTime::SECONDS_WEEK),
			'selYear'			=> date('Y', $dateStart + TodoyuTime::SECONDS_WEEK),
			'selMonthYear'		=> date('nY', $dateStart + TodoyuTime::SECONDS_WEEK),
			'timestamp_today'	=> TodoyuTime::getStartOfDay(NOW),
			'events'			=> self::preRenderEventsForMonth($dateStart, $eventTypes, $persons, $personColors, $dateEnd),
			'dayEvents'			=> self::preRenderDayevents(CALENDAR_MODE_MONTH, $dateStart, $dateEnd, $eventTypes, $persons),//, $amountDays),
			'birthdays'			=> in_array(EVENTTYPE_BIRTHDAY, $eventTypes) ? self::preRenderPersonBirthdays($dateStart, $dateEnd, CALENDAR_MODE_MONTH) : array(),
			'holidays'			=> TodoyuCalendarManager::getHolidays($dateStart, $dateEnd),
			'title'				=> TodoyuCalendarViewHelper::getCalendarTitle($dateStart, $dateEnd, CALENDAR_MODE_MONTH)
		);

		return render($tmpl, $data);
	}



	/**
	 * Render events for day view in array elements
	 *
	 * @param	Integer		$dateStart
	 * @param	Array		$eventTypes
	 * @param	Array		$persons
	 * @param	Array		$personColors
	 * @return	Array
	 */
	public static function preRenderEventsForDay($dateStart, array $eventTypes, array $persons, array $personColors) {
		$dayRange	= TodoyuTime::getDayRange($dateStart);
		$dateKey	= date('Ymd', $dayRange['start']);
		$events		= self::preRenderEventsDayAndWeek(CALENDAR_MODE_DAY, $dayRange['start'], $dayRange['end'], $eventTypes, $persons, $personColors);

		return $events[$dateKey];
	}



	/**
	 * Prerender events for week view
	 *
	 * @param	Integer		$dateStart
	 * @param	Array		$eventTypes
	 * @param	Array		$persons
	 * @param	Array		$personColors
	 * @return	Array
	 */
	public static function preRenderEventsForWeek($dateStart, array $eventTypes, array $persons, array $personColors) {
		$weekRange	= TodoyuTime::getWeekRange($dateStart);
		$dateStart	= $weekRange['start'];
		$dateEnd	= $weekRange['end'];

		$events		= self::preRenderEventsDayAndWeek(CALENDAR_MODE_WEEK, $weekRange['start'], $weekRange['end'], $eventTypes, $persons, $personColors);

		return $events;
	}



	/**
	 * Render events for month view in array elements
	 *
	 * @param	Integer		$dateStart
	 * @param	Array		$eventTypes
	 * @param	Array		$persons
	 * @param	Array		$personColors
	 * @return	Array
	 */
	public static function preRenderEventsForMonth($dateStart, array $eventTypes, array $persons, array $personColors, $monthEndAlternative = 0) {
		$monthRange	= TodoyuCalendarManager::getMonthDisplayRange($dateStart);
		$dateStart	= $monthRange['start'];
		$dateEnd	= $monthEndAlternative > 0 ? $monthEndAlternative : $monthRange['end'];

		$renderedEvents	= array();

			// Get all events in current view
		$events		= TodoyuEventManager::getEventsInTimespan($dateStart, $dateEnd, $persons, $eventTypes, false);

			// Group the events by day
		$eventsByDay= TodoyuEventManager::groupEventsByDay($events, $dateStart, $dateEnd);
			// Add overlap informations to events for each day
		//$eventsByDay= TodoyuEventManager::addOverlapInformationToEvents($eventsByDay);

			// Render events array
		foreach($eventsByDay as $dateKey => $eventsOfDay) {
			foreach($eventsOfDay as $event) {
				$renderedEvents[$dateKey][] = TodoyuEventRenderer::renderEvent($event, 'month');
			}
		}

		return $renderedEvents;
	}



	/**
	 * Render events for day or week view in array elements
	 *
	 * @param	Integer		$mode				CALENDAR_MODE_DAY / CALENDAR_MODE_WEEK
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @param	Array		$eventTypes
	 * @param	Array		$persons
	 * @param	Array		$personColors
	 * @return	Array
	 */
	public static function preRenderEventsDayAndWeek($mode, $dateStart, $dateEnd, array $eventTypes, array $persons, array $personColors) {
		$dateStart		= intval($dateStart);
		$dateEnd		= intval($dateEnd);

		$renderedEvents	= array();
		$eventFullWidth	= ($mode === CALENDAR_MODE_DAY) ? CALENDAR_DAY_EVENT_WIDTH : CALENDAR_WEEK_EVENT_WIDTH;

			// Get all events in current view
		$events		= TodoyuEventManager::getEventsInTimespan($dateStart, $dateEnd, $persons, $eventTypes, false );

			// Group the events by day
		$eventsByDay= TodoyuEventManager::groupEventsByDay($events, $dateStart, $dateEnd);
			// Add overlap informations to events for each day
		$eventsByDay= TodoyuEventManager::addOverlapInformationToEvents($eventsByDay);

			// Render events array
		foreach($eventsByDay as $dateKey => $eventsOfDay) {
			$dayTime = mktime(0, 0, 0, substr($dateKey, 4, 2), substr($dateKey, 6, 2), substr($dateKey, 0, 4) );

			foreach($eventsOfDay as $event) {

				if( $event['_overlapNum'] > 0 ) {
						// Event intersects with other events
					$event['width']	= $eventFullWidth / ( $event['_maxPosition'] ) * ( $event['_overlapNum'] );
					$event['left']	= ( $eventFullWidth / ($event['_maxPosition'] ) ) * $event['_overlapIndex'];
				} else {
						// No intersections to this event
					$event['width']	= $eventFullWidth;
					$event['left']	= 0;
				}

					// If the event started before the current day, set top = 0
				if( $event['date_start'] <= $dayTime ) {
					$event['top'] = 0;
				} else {
					$event['top']		= TodoyuEventRenderer::getTimeCoordinate($event['date_start']);
				}

				$event['height']	= TodoyuEventRenderer::getEventHeight($dayTime, $event['date_start'], $event['date_end'] );

					// Render
				$renderedEvents[$dateKey][] = TodoyuEventRenderer::renderEvent($event, $mode);
			}
		}

		return $renderedEvents;
	}



	/**
	 * Render full day events for day, week or month
	 *
	 * @param	Integer		$mode		CALENDAR_MODE_DAY / ..WEEK / ..MONTH
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd	last shown date in current calendar view
	 * @param	Array		$eventTypes
	 * @param	Array		$persons
	 * @return	Array
	 */
	public static function preRenderDayevents($mode, $dateStart, $dateEnd, array $eventTypes, array $persons) {
		$events	= TodoyuEventManager::getEventsInTimespan($dateStart, $dateEnd, $persons, $eventTypes, true);
		$grouped= TodoyuEventManager::groupEventsByDay($events, $dateStart, $dateEnd);

		$dayEvents	= array();
		$rendered	= array();

		foreach($grouped as $dateKey => $eventsOfDay) {
			foreach($eventsOfDay as $event) {
				if( ! in_array($event['id'], $rendered) || $mode === CALENDAR_MODE_MONTH ) {
					$rendered[] = $event['id'];

					$event['tstamp_lastDay']	= $dateEnd;
					$event['tstamp_firstDay']	= $dateStart;
					$dayEvents[$dateKey][]		= TodoyuEventRenderer::renderFulldayEvent($mode, $event);
				}
			}
		}

		return $dayEvents;
	}



	/**
	 * Render all birthdays in the range
	 * Prerendered birthdays are stored as elements of their dateKey
	 * $birthdays[20091231][0] = array('id'=>)
	 * The birthday records are person records with the following special keys: age, birthyear
	 *
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @param	Integer		$mode
	 * @return	Array
	 */
	public static function preRenderPersonBirthdays($dateStart, $dateEnd, $mode = CALENDAR_MODE_MONTH) {
		$dateStart	= intval($dateStart);
		$dateEnd	= intval($dateEnd);

		$tmpl			= 'ext/calendar/view/birthday.tmpl';
		$birthdaysByDay	= TodoyuCalendarManager::getBirthdaysByDay($dateStart, $dateEnd);

		foreach($birthdaysByDay as $dateKey => $birthdaysOfTheDay) {
			if( is_array($birthdaysOfTheDay) ) {
				foreach($birthdaysOfTheDay as $index => $birthday) {
					$birthday['fullname']		= $birthday['lastname'] . ', ' . $birthday['firstname'];
					$birthday['calendarMode']	= TodoyuCalendarManager::getModeName($mode);

					$birthdaysByDay[$dateKey][$index] = render($tmpl, $birthday);
				}
			}
		}

		return $birthdaysByDay;
	}



	/**
	 * Render calendar panel widgets
	 *
	 * @return	String	HTML
	 */
	public static function renderPanelWidgets() {
		$params	= array();

		return TodoyuPanelWidgetRenderer::renderPanelWidgets('calendar', $params);
	}



	/**
	 * Renders the calendar tabs (day, week, month)
	 *
	 * @return	String	HTML
	 */
	public static function renderTabs($activeTab = '')	{
		if( empty($activeTab) ) {
			$activeTab = TodoyuCalendarPreferences::getActiveTab();
		}

		$name		= 'calendar';
		$tabs		= TodoyuCalendarManager::getCalendarTabsConfig();
		$jsHandler	= 'Todoyu.Ext.calendar.Tabs.onSelect.bind(Todoyu.Ext.calendar.Tabs)';

		if( $activeTab === 'view' ) {
			$tabs[] = array(
				'id'		=> 'view',
				'label'		=> 'Details'
			);
		}

		return TodoyuTabheadRenderer::renderTabs($name, $tabs, $jsHandler, $activeTab);
	}
}

?>