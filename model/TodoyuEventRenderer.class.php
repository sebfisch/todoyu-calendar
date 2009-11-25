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
 * Event Renderer
 *
 * @package			Todoyu
 * @subpackage		Calendar
*/

class TodoyuEventRenderer {

	/**
	 * Render list of events to be displayed in events tab of portal
	 *
	 * @param	Array 	$events
	 * @param	Array 	$holidays
	 * @param	Array 	$birthdays
	 * @return	String
	 *
	 */
	public static function renderPortalTabEventsList(array $events, array $holidays, array $birthdays ) {
		$color = self::getEventColorData(userid());

		$data	= array(
			'events'						=> $events,
			'showHolidays'					=> $GLOBALS['CONFIG']['EXT']['portal']['tabcontentconfig']['calendar']['showHolidays'],
			'holidaysLookAheadWeeksAmount'	=> $GLOBALS['CONFIG']['EXT']['portal']['tabcontentconfig']['calendar']['holidaysLookAheadWeeks'],
			'holidays'						=> $holidays,
			'showBirthdays'					=> $GLOBALS['CONFIG']['EXT']['portal']['tabcontentconfig']['calendar']['showBirthdays'],
			'birthdaysLookAheadWeeksAmount'	=> $GLOBALS['CONFIG']['EXT']['portal']['tabcontentconfig']['calendar']['birthdaysLookAheadWeeks'],
			'birthdays'						=> $birthdays,
			'color'							=> $color[userid()]
		);

		return render( 'ext/calendar/view/tab-portal-eventslist.tmpl', $data );
	}



	/**
	 * Render create event form popup
	 *
	 * @param 	Array	$data
	 * @return 	String	Form
	 */
	public static function renderCreateQuickEvent($time, $isDayEvent = false) {
		$time		= intval($time);

		$xmlPath	= 'ext/calendar/config/form/quickevent.xml';
		$form 		= TodoyuFormManager::getForm($xmlPath);

		$dayRange	= TodoyuTime::getDayRange($time);

		if( $isDayEvent ) {
			$timeStart	= $dayRange['start'];
			$timeEnd	= $dayRange['end'];
		} else {
			$timeStart	= $time;
			$timeEnd	= $timeStart + 900;
		}

		$user	= TodoyuAuth::getUser()->getTemplateData();

		$formData	= array(
			'date_start' 	=> $timeStart,
			'date_end' 		=> $timeEnd,
			'is_dayevent'	=> $isDayEvent,
			'user'			=> array($user)
		);

		$form->setFormData($formData);
		$form->setUseRecordID(false);

		return $form->render();
	}



	/**
	 * Prepare event rendering data array
	 *
	 * @param	String	$calendarMode			day / week / month
	 * @param	Array	$data					event parameters
	 * @param	Array	$selectedUserIDs
	 * @return	Array
	 */
	public static function prepareEventRenderData($calendarMode = 'month', array $data, array $selectedUserIDs = array()) {
		$selectedUserIDs	= TodoyuArray::intval($selectedUserIDs);
		$assignedUsers 		= TodoyuEventManager::getAssignedUsersOfEvent( $data['id'] , true );

		$idCurrentUser		= userid();
//		$groupsCurrentUser	= TodoyuAuth::getGroupIDs();

		$idAssignedUser		= count($assignedUsers) == 1 ? $assignedUsers[0]['id_user'] : 0;

		$color = self::getEventColorData($idAssignedUser);

			// Build event render data
		$data	= array_merge($data, array(
			'calendarMode'			=> $calendarMode,
			'current_user'			=> $idCurrentUser,
			'assignedUsers'			=> $assignedUsers,
			'updateAllowed'			=> 		TodoyuAuth::isAdmin()
										||	$data['id_user_create'] == $idCurrentUser
								 		||	in_array($idCurrentUser, $assignedUsers) ? 1 : 0,
			'colors'				=> $color[$idAssignedUser],
		));

		if ($calendarMode == 'week') {
			$shownStartingDayNum	= TodoyuEventManager::calcEventStartingDayNumInWeek($data['date_start'], $data['tstamp_firstDay']);
			$shownEndingDayNum		= TodoyuEventManager::calcEventEndingDayNumInWeek($data['date_end'], $data['tstamp_lastDay']);

			$data['shownStartingDayNum']	= $shownStartingDayNum;
			$data['shownEndingDayNum']		= $shownEndingDayNum;
			$data['shownDaysDuration']		= $shownEndingDayNum - $shownStartingDayNum;
		}

		return $data;
	}



	/**
	 * Render event entry
	 *
	 * @param	Array	$data				Event details
	 * @param	String	$calendarMode		'month' / 'week' / 'day'
	 * @param	Array	$selectedUserIDs
	 * @return	String	Div of the event
	 */
	public static function renderEvent(array $event, $calendarMode = 'month', array $selectedUserIDs = array(), $selectedUserColors = array() ) {
		$selectedUserIDs= TodoyuArray::intval($selectedUserIDs);
		$event			= self::prepareEventRenderData($calendarMode, $event, $selectedUserIDs, $selectedUserColors);

		if( $calendarMode === 'list' )	{
			$color = self::getEventColorData(userid());
			$event['colors']		= $color[userid()];
			$event['currentUser']	= userid();

			$tmpl	= 'ext/calendar/view/event-listmode.tmpl';
		} else {
			$tmpl	= 'ext/calendar/view/event.tmpl';
		}

		return render($tmpl, $event);
	}



	/**
	 * Render day event (events that span a whole day or more than that)
	 *
	 * @param	Array	$data					Event details
	 * @return	String	Div of the day-event
	 * @param	Array	$selectedUserIDs
	 * @param	Array	$selectedUserColors
	 * @return	String
	 */
	public static function renderFulldayEvent($calendarMode = 'day', array $data = array(), array $selectedUserIDs) {
		$selectedUserIDs	= TodoyuArray::intval($selectedUserIDs);
		$data				= self::prepareEventRenderData($calendarMode, $data, $selectedUserIDs );

		return render('ext/calendar/view/event-fullday.tmpl', $data);
	}



	/**
	 * Get event rendering color data
	 *
	 * @param	Integer	$idAssignedUser
	 * @param	Array	$selectedUserIDs
	 * @param	Array $selectedUserColors
	 * @return	Array
	 */
	private function getEventColorData($idAssignedUser) {
		if ($idAssignedUser > 0) {
				//  Unique user assigned to event?
			$eventColorData	= TodoyuUserManager::getSelectedUsersColor(array($idAssignedUser));
		} else {
			// Multiple / no users assigned?
			$eventColorData = array(
				'0'	=> array(
					'id'		=> 'multiOrNone',
				)
			);
		}

		return $eventColorData;
	}



	/**
	 * Get height of  starting hour
	 *
	 * @param 	Integer	$dateStart	UNIX Timestamp of the starttime or endtime
	 * @return	Integer				Top-Y of starting hour
	 */
	public static function getTimeCoordinate($dateStart) {
		$dateStart		= intval($dateStart);
		$heightHour		= date('G', $dateStart) * CALENDAR_HEIGHT_HOUR;
		$heightMinute	= intval(date('i', $dateStart )) * CALENDAR_HEIGHT_MINUTE;

		return ceil($heightHour + $heightMinute);
	}



	/**
	 * Get height of an event entry
	 *
	 * @param 	Integer	topCoordinate	top coordinate (y) of the event entry
	 * @param 	Integer	$endtime		Endtime of the event
	 * @return	Integer					Height of the event
	 */
	public static function getEventHeight($dateView, $dateStart, $dateEnd) {
		$dateView	= intval($dateView);
		$dateStart	= intval($dateStart);
		$dateEnd	= intval($dateEnd);
		$viewRange	= TodoyuTime::getDayRange($dateView);

		$dateStart	= TodoyuDiv::intInRange($dateStart, $viewRange['start'], $viewRange['end']);
		$dateEnd	= TodoyuDiv::intInRange($dateEnd, $viewRange['start'], $viewRange['end']);

		$timeDiffHour	= ($dateEnd - $dateStart)/TodoyuTime::SECONDS_HOUR;

		return ceil($timeDiffHour * CALENDAR_HEIGHT_HOUR);
	}


	public static function renderEventViewTabs($idEvent) {
		$idEvent	= intval($idEvent);
		$activeTab	= 'detail';
		$tabsID		= 'calendar-tabs';
		$class		= 'tabs';
		$jsHandler	= 'Todoyu.Ext.calendar.Tabs.onSelect.bind(Todoyu.Ext.calendar.Tabs)';
		$tabs		= TodoyuCalendarManager::getCalendarTabsConfig();

		$event		= TodoyuEventManager::getEvent($idEvent);

		$detailTab	= array(
			'id'		=> 'detail',
			'class'		=> 'detail',
			'hasIcon'	=> 0,
			'label'		=> 'Detail: ' . $event->getTitle(),
			'htmlId'	=> 'calendar-tabhead-detail',
			'key'		=> 'detail',
			'classKey'	=> 'detail'
		);

		array_unshift($tabs, $detailTab);

		return TodoyuTabheadRenderer::renderTabs($tabsID, $class, $jsHandler, $tabs, $activeTab);
	}



	/**
	 *	Render event view
	 *
	 *	@param	Integer	$idEvent
	 *	@return	String
	 */
	public static function renderEventView($idEvent) {
		$idEvent	= intval($idEvent);
		$event		= TodoyuEventManager::getEvent($idEvent);

		$tmpl		= 'ext/calendar/view/event-view.tmpl';
		$data		= array(
			'event'			=> $event->getTemplateData(),
			'attendees'		=> TodoyuEventManager::getAssignedUsersOfEvent($idEvent, true),
			'user_create'	=> TodoyuUserManager::getUserArray($event['id_user_create']),
			'tabs'			=> self::renderEventViewTabs($idEvent)
		);

		return render($tmpl, $data);
	}

}

?>