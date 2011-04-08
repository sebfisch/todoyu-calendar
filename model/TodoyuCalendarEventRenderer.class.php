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
 * Event Renderer
 *
 * @package			Todoyu
 * @subpackage		Calendar
*/
class TodoyuCalendarEventRenderer {

	/**
	 * Render create event form popup
	 *
	 * @param	Array	$data
	 * @return	String	Form
	 */
	public static function renderCreateQuickEvent($time = 0, $isDayEvent = false) {
		$time	= intval($time);
		$time	= TodoyuTime::getRoundedTime($time, 15);

					// Get form object
		$form	= TodoyuCalendarEventManager::getQuickCreateForm();

			// Set event start and ending timestamps
		if( $isDayEvent ) {
			$dayRange	= TodoyuTime::getDayRange($time);

			$timeStart	= $dayRange['start'];
			$timeEnd	= $dayRange['end'];
		} else {
			$timeStart	= $time;
			$timeEnd	= $timeStart + TodoyuTime::SECONDS_MIN * 30;
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
		$assignedPersons	= TodoyuCalendarEventManager::getAssignedPersonsOfEvent($idEvent, true );
		$idAssignedPerson	= count($assignedPersons) == 1 ? $assignedPersons[0]['id_person'] : 0;

		$color = self::getEventColorData($idAssignedPerson);

		$data['calendarMode']	= TodoyuCalendarManager::getModeName($mode);
		$data['assignedPersons']= $assignedPersons;
		$data['color']			= $color[$idAssignedPerson];
		$data['eventtypeKey']	= TodoyuCalendarEventTypeManager::getEventTypeKey($data['eventtype']);


		$assignedPersons = TodoyuArray::getColumn($data['assignedPersons'], 'id');

			// Hide visible data if event is private and current user not assigned
		if( intval($data['is_private']) === 1 && ! in_array(personid(), $assignedPersons) ) {
			$data = self::hidePrivateData($data);
		}

		if( TodoyuCalendarEventRights::isEditAllowed($idEvent) === false) {
			$data['class'] .= ' noAccess';
		}

		return $data;
	}



	/**
	 * Hide private data
	 *
	 * @param	Array	$data
	 * @return	Array
	 */
	private static function hidePrivateData(array $data) {
		$data['title']			= '<' . Label('calendar.event.privateEvent.info') . '>';
		$data['description']	= '';

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
		$event	= TodoyuCalendarEventManager::getEvent($idEvent);
		$colors = self::getEventColorData(personid());

		$eventData	= $event->getTemplateData(true);
		$eventData	= self::prepareEventRenderData('list', $eventData);

		$eventData['person_create']	= $event->getPerson('create')->getTemplateData();
		$eventData['persons']		= TodoyuCalendarEventManager::getAssignedPersonsOfEvent($idEvent, true);

		$tmpl	= 'ext/calendar/view/event-listmode.tmpl';
		$data	= array(
			'event'			=> $eventData,
			'color'			=> $colors[personid()]
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
			$eventColorData	= TodoyuContactPersonManager::getSelectedPersonColor(array($idAssignedPerson));
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
	 * @param	Integer	$dateStart	UNIX Timestamp of the starttime or endtime
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
	 * @param	Integer	topCoordinate	top coordinate (y) of the event entry
	 * @param	Integer	$endtime		Endtime of the event
	 * @return	Integer					Height of the event
	 */



	/**
	 * Get height of an event in day or week view
	 * An event is at least 20px height (to stay visible) except you set $real true
	 *
	 * @param	Integer		$dateView
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @param	Boolean		$real
	 * @return	Integer
	 */
	public static function getEventHeight($dateView, $dateStart, $dateEnd, $real = false) {
		$dateView	= intval($dateView);
		$dateStart	= intval($dateStart);
		$dateEnd	= intval($dateEnd);
		$viewRange	= TodoyuTime::getDayRange($dateView);

		$dateStart	= TodoyuNumeric::intInRange($dateStart, $viewRange['start'], $viewRange['end']);
		$dateEnd	= TodoyuNumeric::intInRange($dateEnd, $viewRange['start'], $viewRange['end']);

		$timeDiffHour	= ($dateEnd - $dateStart)/TodoyuTime::SECONDS_HOUR;

		$height		= ceil($timeDiffHour * CALENDAR_HEIGHT_HOUR);

		if( $real !== true ) {
				// Make sure an event is at least 20px height
			$minHeight	= intval((CALENDAR_EVENT_MIN_DURATION/3600) * CALENDAR_HEIGHT_HOUR);
			$height	= TodoyuNumeric::intInRange($height, $minHeight);
		}

		return $height;
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

		$event		= TodoyuCalendarEventManager::getEvent($idEvent);

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

		$eventData	= TodoyuCalendarEventManager::getEvent($idEvent)->getTemplateData(true, true);

			// Add scheduled reminder timestamps
		if( TodoyuCalendarReminderPopupManager::isEventSchedulable($idEvent) ) {
			$eventData['timeReminderPopup']	= TodoyuCalendarReminderPopupManager::getPopupTime($idEvent);
		}
		if( TodoyuCalendarReminderEmailManager::isEventSchedulable($idEvent) ) {
			$eventData['timeReminderEmail']	= TodoyuCalendarReminderEmailManager::getMailTime($idEvent);
		}

		$tmpl		= 'ext/calendar/view/event-view.tmpl';

		$data		= array(
			'event'		=> $eventData,
			'tabs'		=> self::renderEventViewTabs($idEvent)
		);

		return render($tmpl, $data);
	}



	/**
	 * Render content of event reminder popup
	 *
	 * @param	Integer		$idEvent
	 * @return	String
	 */
	public static function renderEventReminder($idEvent) {
		$idEvent= intval($idEvent);
		$event	= TodoyuCalendarEventManager::getEvent($idEvent);

			// Construct form object for inline form
		$xmlPath	= 'ext/calendar/config/form/event-reminder.xml';
		$form		= TodoyuFormManager::getForm($xmlPath, $idEvent);
		$form->setFormData(array('id_event' => $idEvent));
		$buttonsForm= $form->render();

		$data	= array(
			'event'				=> $event->getTemplateData(true, true),
			'buttonsFieldset'	=> $buttonsForm
		);

		$tmpl	= 'ext/calendar/view/event-reminder.tmpl';

		return render($tmpl, $data);
	}



	/**
	 * Render content of event mailing popup
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$operationID
	 * @return	String
	 */
	public static function renderEventMailPopup($idEvent, $operationID = OPERATIONTYPE_RECORD_UPDATE) {
		$idEvent= intval($idEvent);
		$event	= TodoyuCalendarEventManager::getEvent($idEvent);

			// Construct form object for inline form
		$xmlPath	= 'ext/calendar/config/form/event-mailing.xml';
		$form		= TodoyuFormManager::getForm($xmlPath, 0, array(), array('#id_event#'=> $idEvent));

			// Have all email persons but user himself preselected
		$emailPersonIDs	= array_keys(TodoyuCalendarEventManager::getEmailReceivers($idEvent, false));
		$emailPersonIDs	= TodoyuArray::removeByValue($emailPersonIDs, array(personid()), false);

			// Set mail form data
		$form->setFormData(array(
			'id_event' 			=> $idEvent,
			'emailreceivers'	=> $emailPersonIDs,
		));

			// Set the appropriate subject (created, updated, deleted)
		switch($operationID) {
			case OPERATIONTYPE_RECORD_CREATE:
				$subject	= Label('calendar.event.mail.popup.subject.create');
				break;
			case OPERATIONTYPE_RECORD_DELETE:
				$subject	= Label('calendar.event.mail.popup.subject.delete');

					// Remove "don't ask again" button in form of deleted events
				$form->getFieldset('buttons')->removeField('dontaskagain');
				break;
			case OPERATIONTYPE_RECORD_UPDATE:
			default:
				$subject	= Label('calendar.event.mail.popup.subject.update');
				break;
		}

			// Render popup content
		$data	= array(
			'subject'		=> $subject,
			'event'			=> $event->getTemplateData(true, true),
			'mailingForm'	=> $form->render()
		);

		$tmpl	= 'ext/calendar/view/event-mailing.tmpl';

		return render($tmpl, $data);
	}

}

?>