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
	 * Render create event form popup
	 *
	 * @param 	Array	$data
	 * @return 	String	Form
	 */
	public static function renderCreateQuickEvent($time = 0, $isDayEvent = false) {
		$time		= intval($time);

		if( $time === 0 ) {
			$time = time();
		}

			// Get form object
		$form	= TodoyuEventManager::getQuickCreateForm();

			// Set event start and ending timestamps
		if( $isDayEvent ) {
			$dayRange	= TodoyuTime::getDayRange($time);

			$timeStart	= $dayRange['start'];
			$timeEnd	= $dayRange['end'];
		} else {
			$timeStart	= $time;
			$timeEnd	= $timeStart + 900;
		}

			// Get person data
		$person	= TodoyuAuth::getPerson()->getTemplateData();

			// Set form data
		$formData	= array(
			'date_start' 	=> $timeStart,
			'date_end' 		=> $timeEnd,
			'is_dayevent'	=> $isDayEvent,
			'persons'		=> array($person)
		);

		$form->setFormData($formData);
		$form->setUseRecordID(false);

		return $form->render();
	}



	/**
	 * Prepare event rendering data array
	 *
	 * @todo	This functions seems wrong (only one person per event?)
	 * @param	String		$calendarMode			day / week / month
	 * @param	Array		$data					event parameters
	 * @return	Array
	 */
	public static function prepareEventRenderData($calendarMode = 'month', array $data) {
		$idEvent			= intval($data['id']);
		$assignedPersons	= TodoyuEventManager::getAssignedPersonsOfEvent($idEvent, true );
		$idAssignedPerson	= count($assignedPersons) == 1 ? $assignedPersons[0]['id_person'] : 0;

		$color = self::getEventColorData($idAssignedPerson);

		$data['calendarMode']	= $calendarMode;
		$data['assignedPersons']= $assignedPersons;
		$data['color']			= $color[$idAssignedPerson];
		$data['eventtypeKey']	= TodoyuEventTypeManager::getEventTypeKey($data['eventtype']);


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
	 * @param	Array		$event				Event details
	 * @param	String		$calendarMode		'month' / 'week' / 'day'
	 * @return	String
	 */
	public static function renderEvent(array $event, $calendarMode = 'month') {
		$tmpl	= 'ext/calendar/view/event.tmpl';
		$data	= self::prepareEventRenderData($calendarMode, $event);

		TodoyuDebug::printInFirebug($data);

		return render($tmpl, $data);
	}



	/**
	 *
	 * @param	Integer		$idEvent
	 */
	public static function renderEventDetailsInList($idEvent) {
		$idEvent= intval($idEvent);
		$event	= TodoyuEventManager::getEvent($idEvent);
		$colors = self::getEventColorData(personid());

		$eventData	= $event->getTemplateData(true);
		$eventData	= self::prepareEventRenderData('list', $eventData);

		$tmpl	= 'ext/calendar/view/event-listmode.tmpl';
		$data	= array(
			'event'			=> $eventData,
			'color'			=> $colors[personid()],
			'attendees'		=> TodoyuEventManager::getAssignedPersonsOfEvent($idEvent, true),
			'person_create'	=> $event->getPerson('create')->getTemplateData()
		);

		return render($tmpl, $data);
	}



	/**
	 * Render day event (events that span a whole day or more than that)
	 *
	 * @param	String		$calendarMode
	 * @param	Array		$data
	 * @return	String
	 */
	public static function renderFulldayEvent($calendarMode = 'day', array $data = array()) {
		$tmpl	= 'ext/calendar/view/event-fullday.tmpl';
		$data	= self::prepareEventRenderData($calendarMode, $data);

		return render($tmpl, $data);
	}



	/**
	 * Get event rendering color data
	 *
	 * @param	Integer		$idAssignedPerson
	 * @return	Array
	 */
	public static function getEventColorData($idAssignedPerson) {
		if( $idAssignedPerson > 0 ) {
				//  Unique person assigned to event?
			$eventColorData	= TodoyuPersonManager::getSelectedPersonColor(array($idAssignedPerson));
		} else {
			// Multiple / no person assigned?
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



	/**
	 * Render event tabs, including detail viewing tab
	 *
	 * @param	Integer	$idEvent
	 * @return	String
	 */
	public static function renderEventViewTabs($idEvent) {
		$idEvent	= intval($idEvent);
		$activeTab	= 'detail';
		$name		= 'calendar';
		$jsHandler	= 'Todoyu.Ext.calendar.Tabs.onSelect.bind(Todoyu.Ext.calendar.Tabs)';
		$tabs		= TodoyuCalendarManager::getCalendarTabsConfig();

		$event		= TodoyuEventManager::getEvent($idEvent);

		$detailTab	= array(
			'id'		=> 'detail',
			'label'		=> 'Detail: ' . $event->getTitle()
		);

		array_unshift($tabs, $detailTab);

		return TodoyuTabheadRenderer::renderTabs($name, $tabs, $jsHandler, $activeTab);
	}



	/**
	 * Render event view
	 *
	 * @param	Integer	$idEvent
	 * @return	String
	 */
	public static function renderEventView($idEvent) {
		$idEvent	= intval($idEvent);
		$event		= TodoyuEventManager::getEvent($idEvent);

		$tmpl		= 'ext/calendar/view/event-view.tmpl';
		$data		= array(
			'event'			=> $event->getTemplateData(),
			'attendees'		=> TodoyuEventManager::getAssignedPersonsOfEvent($idEvent, true),
			'person_create'	=> TodoyuPersonManager::getPersonArray($event['id_person_create']),
			'tabs'			=> self::renderEventViewTabs($idEvent)
		);

		return render($tmpl, $data);
	}

}

?>