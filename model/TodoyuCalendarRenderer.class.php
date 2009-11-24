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
 * Calendar Renderer
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

class TodoyuCalendarRenderer {

	/**
	 * Render the whole calendar (full page, including header, tabs and the actual calendar)
	 *
	 * @param	String	$activeTab
	 * @return	String	Code of the calendar
	 */
	public static function render($activeTab = '', array $params = array()) {
		$currentDate= TodoyuPanelWidgetCalendar::getDate(EXTID_CALENDAR);

		if ($activeTab == '') {
			$activeTab	= TodoyuCalendarPreferences::getActiveTab();
		}

		$tmpl	= 'ext/calendar/view/main.tmpl';
		$data	= array(
			'tabs'			=> self::renderTabs($activeTab),
			'active'		=> $activeTab,
			'content'		=> self::renderCalendar($currentDate, $activeTab, $params),
			'showCalendar'	=> $activeTab === 'day' || $activeTab === 'week' || $activeTab === 'month'
		);

		if( $activeTab === 'view' ) {
			$event	= TodoyuEventManager::getEvent($params['event']);
			$data['date']	= $event->getStartDate();
		}

		return render($tmpl, $data);
	}



	/**
	 * Render content of the calendar modes (day / week / month)
	 *
	 * @param	Integer		$currentDate
	 * @param	String		$activeTab
	 * @return	String
	 */
	public static function renderCalendar($currentDate, $activeTab, array $params = array()) {
		$currentDate	= intval($currentDate);

		switch( $activeTab ) {
			case 'day':
				return self::renderCalendarDay($currentDate);
				break;

			case 'week':
				return self::renderCalendarWeek($currentDate);
				break;

			case 'month':
				return self::renderCalendarMonth($currentDate);
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
	 * @param	Integer		$currentDate
	 * @return	String
	 */
	public static function renderCalendarDay($currentDate) {
		$currentDate= intval($currentDate);
		$users		= TodoyuCalendarManager::getSelectedUsers();
		$dayRange	= TodoyuTime::getDayRange($currentDate);
		$dateStart	= $dayRange['start'];
		$dateEnd	= $dayRange['end'];

		$userColors	= TodoyuUserManager::getSelectedUsersColor($users);
		$eventTypes	= TodoyuCalendarManager::getSelectedEventTypes();

		$tmpl		= 'ext/calendar/view/calendar-day.tmpl';
		$data		= array(
			'timestamp'		=> $currentDate,
			'fullDayView'	=> TodoyuCalendarPreferences::getFullDayView(),
			'dateKey'		=> date('Ymd', $dateStart),
			'events'		=> self::preRenderEventsForDay($dateStart, $eventTypes, $users, $userColors),
			'dayEvents'		=> self::preRenderDayevents('day', $dateStart, $dateEnd, $eventTypes, $users),
			'birthdays'		=> in_array(EVENTTYPE_BIRTHDAY, $eventTypes) ? self::preRenderCalendarBirthdays('day', $dateStart, $dateEnd) : '',
			'holidays'		=> TodoyuCalendarManager::getHolidays($dateStart, $dateEnd),
			'title'			=> TodoyuCalendarViewHelper::getCalendarTitle('day', $dateStart, $dateEnd)
		);

		return render($tmpl, $data);
	}



	/**
	 * Render calendar view for week view
	 *
	 * @param	Integer		$currentDate
	 * @return	String
	 */
	public static function renderCalendarWeek($currentDate) {
			// Get display infos
		$currentDate= intval($currentDate);
		$users		= TodoyuCalendarManager::getSelectedUsers();
		$weekRange	= TodoyuTime::getWeekRange($currentDate);
		$dateStart	= $weekRange['start'];
		$dateEnd	= $weekRange['end'];

		$userColors	= TodoyuUserManager::getSelectedUsersColor($users);
		$eventTypes	= TodoyuCalendarManager::getSelectedEventTypes();

		$tmpl		= 'ext/calendar/view/calendar-week.tmpl';
		$data		= array(
			'timestamps'		=> TodoyuTime::getDayTimesOfWeek($currentDate),
			'fullDayView'		=> TodoyuCalendarPreferences::getFullDayView(),
			'timestamp'			=> $currentDate,
			'timestamp_today'	=> TodoyuTime::getStartOfDay(NOW),
			'events'			=> self::preRenderEventsDayAndWeek('week', $dateStart, $dateEnd, $eventTypes, $users, $userColors),
			'dayEvents'			=> self::preRenderDayevents('week', $dateStart, $dateEnd, $eventTypes, $users),
			'birthdays'			=> in_array(EVENTTYPE_BIRTHDAY, $eventTypes) ? self::preRenderCalendarBirthdays('week', $dateStart, $dateEnd) : array(),
			'holidays'			=> TodoyuCalendarManager::getHolidays($dateStart, $dateEnd),
			'title'				=> TodoyuCalendarViewHelper::getCalendarTitle('week', $dateStart, $dateEnd)
		);

		return render($tmpl, $data);
	}



	/**
	 * Render calendar view for month view
	 *
	 * @param	Integer		$currentDate
	 * @return	String
	 */
	public static function renderCalendarMonth($selectedDate) {
		$selectedDate	= intval($selectedDate);
		$users			= TodoyuCalendarManager::getSelectedUsers();

		$monthRange	= TodoyuCalendarManager::getMonthDisplayRange($selectedDate);

		$dateStart	= $monthRange['start'];
		$dateEnd	= $monthRange['end'];

		$userColors	= TodoyuUserManager::getSelectedUsersColor($users);
		$eventTypes	= TodoyuCalendarManager::getSelectedEventTypes();

		$tmpl		= 'ext/calendar/view/calendar-month.tmpl';

		$data		= array(
			'timestamps'		=> TodoyuCalendarManager::getShownDaysTimestampsOfMonthView($selectedDate),
			'timestamp'			=> $selectedDate,
			'month'				=> date('n', $dateStart + TodoyuTime::SECONDS_WEEK),
			'timestamp_today'	=> TodoyuTime::getStartOfDay(NOW),
			'events'			=> self::preRenderEventsForMonth($dateStart, $eventTypes, $users, $userColors),
			'dayEvents'			=> self::preRenderDayevents('month', $dateStart, $dateEnd, $eventTypes, $users),//, $amountDays),
			'birthdays'			=> in_array(EVENTTYPE_BIRTHDAY, $eventTypes) ? self::preRenderCalendarBirthdays('month', $dateStart, $dateEnd) : array(),
			'holidays'			=> TodoyuCalendarManager::getHolidays($dateStart, $dateEnd),
			'title'				=> TodoyuCalendarViewHelper::getCalendarTitle('month', $dateStart, $dateEnd)
		);

		return render($tmpl, $data);
	}



	/**
	 * Render events for day view in array elements
	 *
	 * @param	Integer		$dateStart
	 * @param	Array		$eventTypes
	 * @param	Array		$users
	 * @param	Array		$userColors
	 * @return	Array
	 */
	public static function preRenderEventsForDay($dateStart, array $eventTypes, array $users, array $userColors) {
		$dayRange	= TodoyuTime::getDayRange($dateStart);
		$dateKey	= date('Ymd', $dayRange['start']);
		$events		= self::preRenderEventsDayAndWeek('day', $dayRange['start'], $dayRange['end'], $eventTypes, $users, $userColors);

		return $events[$dateKey];
	}



	/**
	 * Render event for week view in array elements
	 *
	 * @param	Integer		$dateStart
	 * @param	Array		$eventTypes
	 * @param	Array		$users
	 * @param	Array		$userColors
	 * @return	Array
	 */
	public static function preRenderEventsForWeek($dateStart, array $eventTypes, array $users, array $userColors) {
		$weekRange	= TodoyuTime::getWeekRange($dateStart);

		return self::preRenderEventsDayAndWeek('week', $weekRange['start'], $weekRange['end'], $eventTypes, $users, $userColors);
	}



	/**
	 * Render events for month view in array elements
	 *
	 * @param	Integer		$dateStart
	 * @param	Array		$eventTypes
	 * @param	Array		$users
	 * @param	Array		$userColors
	 * @return	Array
	 */
	public static function preRenderEventsForMonth($dateStart, array $eventTypes, array $users, array $userColors) {
		$monthRange	= TodoyuCalendarManager::getMonthDisplayRange($dateStart);
		$dateStart	= $monthRange['start'];
		$dateEnd	= $monthRange['end'];

		$renderedEvents	= array();

			// Get all events in current view
		$events		= TodoyuEventManager::getEventsInTimespan($dateStart, $dateEnd, $users, $eventTypes);

			// Group the events by day
		$eventsByDay= TodoyuEventManager::groupEventsByDay($events, $dateStart, $dateEnd);
			// Add overlap informations to events for each day
		$eventsByDay= TodoyuEventManager::addOverlapInformationToEvents($eventsByDay);

			// Render events array
		foreach($eventsByDay as $dateKey => $eventsOfDay) {
//			$dayTime = mktime(0, 0, 0, substr($dateKey, 4, 2), substr($dateKey, 6, 2), substr($dateKey, 0, 4) );
			foreach($eventsOfDay as $event) {
				$renderedEvents[$dateKey][] = TodoyuEventRenderer::renderEvent($event, 'month', $users, $userColors);
			}
		}

		return $renderedEvents;
	}



	/**
	 * Render events for day or week view in array elements
	 *
	 * @param	String		$mode
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @param	Array		$eventTypes
	 * @param	Array		$users
	 * @param	Array		$userColors
	 * @return	Array
	 */
	public static function preRenderEventsDayAndWeek($mode, $dateStart, $dateEnd, array $eventTypes, array $users, array $userColors) {
		$dateStart		= intval($dateStart);
		$dateEnd		= intval($dateEnd);
		$renderedEvents	= array();
		$eventFullWidth	= $mode === 'day' ? CALENDAR_DAY_EVENT_WIDTH : CALENDAR_WEEK_EVENT_WIDTH;

			// Get all events in current view
		$events		= TodoyuEventManager::getEventsInTimespan($dateStart, $dateEnd, $users, $eventTypes, false );
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
					$event['width']	= $eventFullWidth / ($event['_overlapNum'] );
					$event['left']	= $event['width'] * $event['_overlapIndex'];
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
				$renderedEvents[$dateKey][] = TodoyuEventRenderer::renderEvent($event, $mode, $users, $userColors);
			}
		}

		return $renderedEvents;
	}



	/**
	 * Render full day events for day, week or month
	 *
	 * @param	String		$mode
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd	last shown date in current calendar view
	 * @param	Array		$eventTypes
	 * @param	Array		$users
	 * @return	Array
	 */
	public function preRenderDayevents($mode, $dateStart, $dateEnd, array $eventTypes, array $users) {
		$events	= TodoyuEventManager::getEventsInTimespan($dateStart, $dateEnd, $users, $eventTypes, true);
		$grouped= TodoyuEventManager::groupEventsByDay($events, $dateStart, $dateEnd);

		$dayEvents	= array();
		$rendered	= array();

		foreach($grouped as $dateKey => $eventsOfDay) {
			foreach($eventsOfDay as $event) {
				if( ! in_array($event['id'], $rendered) || $mode === 'month' ) {
					$rendered[] = $event['id'];

					$event['tstamp_lastDay']	= $dateEnd;
					$dayEvents[$dateKey][]		= TodoyuEventRenderer::renderFulldayEvent($mode, $event, $users);
				}
			}
		}

		return $dayEvents;
	}



	/**
	 * (Pre-)Render birthdays  of current month / week / day.
	 * -These are birthdays from person-data, NOT regular event records
	 *
	 * @param	Integer	$tstampFirstDay		UNIX timestamp 1st day of timespan
	 * @param	Integer	$tstampLastDay		UNIX timestamp last day of timespan
	 * @return	Array
	 */
	public static function preRenderCalendarBirthdays($calendarMode = 'month', $tstampFirstDay, $tstampLastDay) {
		$birthdaysUngrouped   = TodoyuUserManager::getUsersByBirthdayInTimespan($tstampFirstDay, $tstampLastDay);

			// Recompile birthdays into an array of days with their birthdays pre-rendered
		$birthdays		= array();
		for($tstamp = $tstampFirstDay; $tstamp <= $tstampLastDay; $tstamp += 86400) {
			$birthdaysPerDay = '';
			$dateMD = date('m-d', $tstamp);
			foreach($birthdaysUngrouped as $key => $bdayData) {
				if ($bdayData['birthday_md'] == $dateMD) {
					$bdayData['calendarMode']	= $calendarMode;
					$bdayData['id_user_create']	= -1;
					$bdayData['date_start']		= $tstamp;
					$bdayData['title']			= $bdayData['firstname'] . ' ' . $bdayData['lastname'];

					$birthdaysPerDay	.= render('ext/calendar/view/birthday.tmpl', $bdayData);
					unset($birthdaysUngrouped[$key]);
				}
			}
			$birthdays[ date('Ymd', $tstamp) ]	= $birthdaysPerDay;
		}

		return $calendarMode == 'day' ? implode('', $birthdays) : $birthdays;
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
	public function renderTabs($activeTab = null)	{
		if( is_null($activeTab) ) {
			$activeTab = TodoyuCalendarPreferences::getActiveTab();
		}

		$tabs		= TodoyuCalendarManager::getCalendarTabsConfig();
		$tabsID		= 'calendar-tabs';
		$class		= 'tabs';
		$jsHandler	= 'Todoyu.Ext.calendar.Tabs.onSelect.bind(Todoyu.Ext.calendar.Tabs)';

		if( $activeTab === 'view' ) {
			$tabs[] = array(
				'id'		=> 'view',
				'class'		=> 'view',
				'hasIcon'	=> true,
				'label'		=> 'Details',
				'htmlId'	=> 'calendar-tabhead-view',
				'key'		=> 'view',
				'classKey'	=> 'view'
			);
		}

		return TodoyuTabheadRenderer::renderTabs($tabsID, $class, $jsHandler, $tabs, $activeTab);
	}
}

?>