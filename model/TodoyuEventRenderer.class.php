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
	 * @param	Integer		$calendarMode			CALENDAR_MODE_DAY / ..WEEK / ..MONTH
	 * @param	Array		$data					event parameters
	 * @return	Array
	 */
	public static function prepareEventRenderData($mode = CALENDAR_MODE_MONTH, array $data) {
		$idEvent			= intval($data['id']);
		$assignedPersons	= TodoyuEventManager::getAssignedPersonsOfEvent($idEvent, true );
		$idAssignedPerson	= count($assignedPersons) == 1 ? $assignedPersons[0]['id_person'] : 0;

		$color = self::getEventColorData($idAssignedPerson);

		$data['calendarMode']	= TodoyuCalendarManager::getModeName($mode);
		$data['assignedPersons']= $assignedPersons;
		$data['color']			= $color[$idAssignedPerson];
		$data['eventtypeKey']	= TodoyuEventTypeManager::getEventTypeKey($data['eventtype']);


		if ( $mode == CALENDAR_MODE_WEEK ) {
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
	 * @param	Integer		$calendarMode		CALENDAR_MODE_MONTH / ..WEEK / ..DAY
	 * @return	String
	 */
	public static function renderEvent(array $event, $mode = CALENDAR_MODE_MONTH) {
		$tmpl	= 'ext/calendar/view/event.tmpl';
		$data	= self::prepareEventRenderData($mode, $event);

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
	 * @param	Integer		$calendarMode
	 * @param	Array		$data
	 * @return	String
	 */
	public static function renderFulldayEvent($mode = CALENDAR_MODE_DAY, array $data = array()) {
		$tmpl	= $mode === CALENDAR_MODE_DAY ? 'ext/calendar/view/event-dayevent-day.tmpl' : 'ext/calendar/view/event-dayevent-week.tmpl';
		$data	= self::prepareEventRenderData($mode, $data);

		$idAssignedPerson	= intval($data['assignedPersons'][0]['id']);
		$color				= self::getEventColorData($idAssignedPerson);
		$data['color']		= $color[$idAssignedPerson];

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

		$dateStart	= TodoyuNumeric::intInRange($dateStart, $viewRange['start'], $viewRange['end']);
		$dateEnd	= TodoyuNumeric::intInRange($dateEnd, $viewRange['start'], $viewRange['end']);

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