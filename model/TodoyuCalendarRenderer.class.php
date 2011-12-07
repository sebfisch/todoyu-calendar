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
 * Calendar Renderer
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarRenderer {

	/**
	 * Extension key
	 *
	 * @var	String
	 */
	const EXTKEY = 'calendar';



	/**
	 * Render the whole calendar (header, tabs and the actual calendar grid)
	 *
	 * @param	String	$activeTab		Displayed tab
	 * @param	Array	$params			Request parameters sub functions
	 * @return	String	Code of the calendar
	 */
	public static function render($activeTab = '', array $params = array()) {
		$time	= TodoyuCalendarPanelWidgetCalendar::getDate();

			// Get tab from preferences if not set
		if( empty($activeTab) ) {
			$activeTab	= TodoyuCalendarPreferences::getActiveTab();
		}

		$tmpl	= 'ext/calendar/view/main.tmpl';
		$data	= array(
			'active'		=> $activeTab,
			'content'		=> self::renderCalendar($time, $activeTab, $params),
			'showCalendar'	=> in_array($activeTab, array('day', 'week', 'month'))
		);

			// If event-view is selected, set date and add it to data array
		if( $activeTab === 'view' ) {
			$event	= TodoyuCalendarEventManager::getEvent($params['event']);
			TodoyuCalendarPanelWidgetCalendar::saveDate($event->getStartDate());
			$data['date']	= $event->getStartDate();
		}

		return Todoyu::render($tmpl, $data);
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

				// Event single view
			case 'view':
				$idEvent	= intval($params['event']);
				return TodoyuCalendarEventRenderer::renderEventView($idEvent);
				break;

			case 'edit':
				$idEvent	= intval($params['event']);
				return TodoyuCalendarEventEditRenderer::renderEventForm($idEvent);
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

		$personColors	= TodoyuContactPersonManager::getSelectedPersonColor($persons);
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

		return Todoyu::render($tmpl, $data);
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

		$personColors	= TodoyuContactPersonManager::getSelectedPersonColor($persons);
		$eventTypes		= TodoyuCalendarManager::getSelectedEventTypes();

		$tmpl	= 'ext/calendar/view/calendar-week.tmpl';
		$data	= array(
			'timestamps'		=> TodoyuTime::getTimestampsForWeekdays($time),
			'fullDayView'		=> TodoyuCalendarPreferences::getFullDayView(),
			'timestamp'			=> $time,
			'timestamp_today'	=> TodoyuTime::getStartOfDay(NOW),
			'events'			=> self::preRenderEventsForWeek($dateStart, $eventTypes, $persons, $personColors),
			'dayEvents'			=> self::preRenderWeekDayEvents($dateStart, $dateEnd, $eventTypes, $persons),
			'birthdays'			=> in_array(EVENTTYPE_BIRTHDAY, $eventTypes) ? self::preRenderPersonBirthdays($dateStart, $dateEnd, CALENDAR_MODE_WEEK) : array(),
			'holidays'			=> TodoyuCalendarManager::getHolidays($dateStart, $dateEnd),
			'title'				=> TodoyuCalendarViewHelper::getCalendarTitle($dateStart, $dateEnd, CALENDAR_MODE_WEEK)
		);

		return Todoyu::render($tmpl, $data);
	}



	/**
	 * Render calendar view for month view
	 *
	 * @param	Integer		$activeDate		UNIX timestamp of selected date
	 * @return	String
	 */
	public static function renderCalendarMonth($activeDate) {
		$activeDate	= intval($activeDate);
		$persons	= TodoyuCalendarManager::getSelectedPersons();

		$monthRange	= TodoyuCalendarManager::getMonthDisplayRange($activeDate);
		$dateStart	= $monthRange['start'];
		$dateEnd	= $monthRange['end'];
		$timestamps	= TodoyuCalendarManager::getDayTimestampsForMonth($activeDate);

		$personColors	= TodoyuContactPersonManager::getSelectedPersonColor($persons);
		$eventTypes		= TodoyuCalendarManager::getSelectedEventTypes();

		$tmpl		= 'ext/calendar/view/calendar-month.tmpl';
		$data		= array(
			'timestamps'		=> $timestamps,
			'visibleWeeks'		=> todoyuCalendarManager::getVisibleWeeksAmount(sizeof($timestamps)),
			'timestamp'			=> $activeDate,
			'selMonth'			=> date('n', $dateStart + TodoyuTime::SECONDS_WEEK),
			'selYear'			=> date('Y', $dateStart + TodoyuTime::SECONDS_WEEK),
			'selMonthYear'		=> date('nY', $dateStart + TodoyuTime::SECONDS_WEEK),
			'timestamp_today'	=> TodoyuTime::getStartOfDay(NOW),
			'events'			=> self::preRenderEventsForMonth($dateStart, $eventTypes, $persons, $personColors, $dateEnd),
			'dayEvents'			=> self::preRenderDayevents(CALENDAR_MODE_MONTH, $dateStart, $dateEnd, $eventTypes, $persons),//, $amountDays),
			'birthdays'			=> in_array(EVENTTYPE_BIRTHDAY, $eventTypes) ? self::preRenderPersonBirthdays($dateStart, $dateEnd, CALENDAR_MODE_MONTH) : array(),
			'holidays'			=> TodoyuCalendarManager::getHolidays($dateStart, $dateEnd),
			'title'				=> TodoyuCalendarViewHelper::getCalendarTitle($dateStart, $dateEnd, CALENDAR_MODE_MONTH),
		);

		return Todoyu::render($tmpl, $data);
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
	 * Pre-render events for week view
	 *
	 * @param	Integer		$dateStart
	 * @param	Array		$eventTypes
	 * @param	Array		$persons
	 * @param	Array		$personColors
	 * @return	Array						pre-rendered events
	 */
	public static function preRenderEventsForWeek($dateStart, array $eventTypes, array $persons, array $personColors) {
		$weekRange	= TodoyuTime::getWeekRange($dateStart);

		return self::preRenderEventsDayAndWeek(CALENDAR_MODE_WEEK, $weekRange['start'], $weekRange['end'], $eventTypes, $persons, $personColors);
	}



	/**
	 * Render events for month view in array elements
	 *
	 * @param	Integer		$dateStart
	 * @param	Array		$eventTypes
	 * @param	Array		$persons
	 * @param	Array		$personColors
	 * @param	Integer		$monthEndAlternative
	 * @return	Array
	 */
	public static function preRenderEventsForMonth($dateStart, array $eventTypes, array $persons, array $personColors, $monthEndAlternative = 0) {
		$monthRange	= TodoyuCalendarManager::getMonthDisplayRange($dateStart);
		$dateStart	= $monthRange['start'];
		$dateEnd	= $monthEndAlternative > 0 ? $monthEndAlternative : $monthRange['end'];

		$renderedEvents	= array();

			// Get all events in current view
		$events		= TodoyuCalendarEventManager::getEventsInTimespan($dateStart, $dateEnd, $persons, $eventTypes, false);

			// Group the events by day
		$eventsByDay= TodoyuCalendarEventManager::groupEventsByDay($events, $dateStart, $dateEnd);

			// Render events array
		foreach($eventsByDay as $dateKey => $eventsOfDay) {
			foreach($eventsOfDay as $event) {
				$renderedEvents[$dateKey][] = TodoyuCalendarEventRenderer::renderEvent($event, 'month');
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
		$events		= TodoyuCalendarEventManager::getEventsInTimespan($dateStart, $dateEnd, $persons, $eventTypes, false);

			// Group the events by day
		$eventsByDay= TodoyuCalendarEventManager::groupEventsByDay($events, $dateStart, $dateEnd);
			// Add overlap information to events for each day
		$eventsByDay= TodoyuCalendarEventManager::addOverlapInformationToEvents($eventsByDay);

			// Render events array
		foreach($eventsByDay as $dateKey => $eventsOfDay) {
			$dayTime = mktime(0, 0, 0, substr($dateKey, 4, 2), substr($dateKey, 6, 2), substr($dateKey, 0, 4));

			foreach($eventsOfDay as $event) {
					// Set with and left position based on the overlapping information
				$event['width']	= round($eventFullWidth/$event['_numColumns'], 0);
				$event['left']	= round($eventFullWidth/$event['_numColumns'], 0) * $event['_indexColumn'];

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
		$events	= TodoyuCalendarEventManager::getEventsInTimespan($dateStart, $dateEnd, $persons, $eventTypes, true);
		$grouped= TodoyuCalendarEventManager::groupEventsByDay($events, $dateStart, $dateEnd);

		$dayEvents	= array();
		$rendered	= array();

		foreach($grouped as $dateKey => $eventsOfDay) {
			foreach($eventsOfDay as $event) {
				if( ! in_array($event['id'], $rendered) || $mode === CALENDAR_MODE_MONTH ) {
					$rendered[] = $event['id'];

					$dayEvents[$dateKey][]		= TodoyuCalendarEventRenderer::renderFulldayEvent($mode, $event);
				}
			}
		}

		return $dayEvents;
	}



	/**
	 * Render full-day events of given types and persons, laying within weeks of given timespan
	 *
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @param	Array 		$eventTypes
	 * @param	Array		$persons
	 * @return	Array
	 */
	public static function preRenderWeekDayEvents($dateStart, $dateEnd, array $eventTypes, array $persons) {
		$mapping	= TodoyuCalendarManager::getDayEventsWeekMapping($dateStart, $dateEnd, $eventTypes, $persons);

		foreach($mapping as $index => $dayEvents) {
			foreach($dayEvents as $eventIndex => $dayEvent) {
				if( is_array($dayEvent) ) {
					$mapping[$index][$eventIndex]['html'] = TodoyuCalendarEventRenderer::renderFulldayEvent(CALENDAR_MODE_WEEK, $dayEvent);
				}
			}
		}

		return $mapping;
	}



	/**
	 * Render all birthdays in the range
	 * Pre-rendered birthdays are stored as elements of their dateKey
	 * $birthdays[20091231][0] = array('id'=>)
	 * The birthday records are person records with the following special keys: age, year of birth
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

					$birthdaysByDay[$dateKey][$index] = Todoyu::render($tmpl, $birthday);
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
		return TodoyuPanelWidgetRenderer::renderPanelWidgets(self::EXTKEY);
	}



	/**
	 * Renders the calendar tabs (day, week, month)
	 *
	 * @param	String	$activeTab
	 * @return	String	HTML
	 */
	public static function renderTabs($activeTab = '') {
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