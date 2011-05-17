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

			// Set form data
		$formData	= array(
			'date_start' 	=> $timeStart,
			'date_end' 		=> $timeEnd,
			'is_dayevent'	=> $isDayEvent,
			'persons'		=> array(TodoyuAuth::getPerson()->getTemplateData())
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
		$assignedPersons	= TodoyuCalendarEventManager::getAssignedPersonsOfEvent($idEvent, true, true);
		$idAssignedPerson	= count($assignedPersons) == 1 ? $assignedPersons[key($assignedPersons)]['id_person'] : 0;

		$data['calendarMode']	= TodoyuCalendarManager::getModeName($mode);
		$data['assignedPersons']= $assignedPersons;
		$data['timeStart']		= TodoyuCalendarEventManager::getEvent($idEvent)->getStartTime();
		$data['color']			= self::getEventColorData($idAssignedPerson);
		$data['eventtypeKey']	= TodoyuCalendarEventTypeManager::getEventTypeKey($data['eventtype']);

		$assignedPersonIDs = array_keys($assignedPersons);

			// Hide visible data if event is private and current user not assigned
		if( intval($data['is_private']) === 1 && ! in_array(Todoyu::personid(), $assignedPersonIDs) ) {
			$data = self::hidePrivateData($data);
		}

		if( TodoyuCalendarEventRights::isEditAllowed($idEvent) === false) {
			$data['class'] .= ' noAccess';
		}

		return $data;
	}



	/**
	 * Hide private data out from event attributes
	 *
	 * @param	Array	$data
	 * @return	Array
	 */
	private static function hidePrivateData(array $data) {
		$data['title']			= '<' . Todoyu::Label('calendar.event.privateEvent.info') . '>';
		$data['description']	= '';

		return $data;
	}



	/**
	 * Render event entry as calendar item
	 *
	 * @param	Array		$event				Event details
	 * @param	Integer		$calendarMode		CALENDAR_MODE_MONTH / ..WEEK / ..DAY
	 * @return	String
	 */
	public static function renderEvent(array $event, $mode = CALENDAR_MODE_MONTH) {
		$tmpl	= 'ext/calendar/view/event.tmpl';
		$data	= self::prepareEventRenderData($mode, $event);

		return Todoyu::render($tmpl, $data);
	}



	/**
	 * Render event details view for display inside expanded event in list mode
	 *
	 * @param	Integer		$idEvent
	 */
	public static function renderEventDetailsInList($idEvent) {
		$idEvent	= intval($idEvent);
		$event		= TodoyuCalendarEventManager::getEvent($idEvent);
		$eventData	= $event->getTemplateData(true, false, true);
		$eventData	= self::prepareEventRenderData('list', $eventData);

		$eventData['person_create']	= $event->getCreatePerson()->getTemplateData();
		$eventData['persons']		= TodoyuCalendarEventManager::getAssignedPersonsOfEvent($idEvent, true, true);

		$tmpl	= 'ext/calendar/view/event-listmode.tmpl';
		$data	= array(
			'event'	=> $eventData,
			'color'	=> self::getEventColorData(Todoyu::personid())
		);

		return Todoyu::render($tmpl, $data);
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

		$assignedPersons	= $data['assignedPersons'];
		if( count($assignedPersons) > 1 ) {
			$idAssignedPerson = 0;
		} else {
			reset($assignedPersons);
			$personAssigned		= current($assignedPersons);
			$idAssignedPerson	= $personAssigned['id_person'];
		}
		$data['color']		= self::getEventColorData($idAssignedPerson);

		return Todoyu::render($tmpl, $data);
	}



	/**
	 * Get event rendering color data
	 *
	 * @param	Integer		$idPerson
	 * @return	Array
	 * @todo	cleanup	- change parameter from idPerson to assigned persons array or id of event
	 */
	public static function getEventColorData($idPerson) {
		$idPerson	= intval($idPerson);

		if( $idPerson === 0 ) {
			return array(
				'id' => 'multiOrNone'
			);
		} else {
			$personColors	= TodoyuContactPersonManager::getSelectedPersonColor(array($idPerson));

			return $personColors[$idPerson];
		}
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

		$timeDiffHour	= ($dateEnd - $dateStart) / TodoyuTime::SECONDS_HOUR;

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
		$event		= TodoyuCalendarEventManager::getEvent($idEvent);

		$tmpl	= 'ext/calendar/view/event-view.tmpl';
		$data	= array(
			'event'	=> $event->getTemplateData(true, true, true),
			'tabs'	=> self::renderEventViewTabs($idEvent)
		);

		return Todoyu::render($tmpl, $data);
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
		$xmlPath= 'ext/calendar/config/form/event-reminder.xml';
		$form	= TodoyuFormManager::getForm($xmlPath, $idEvent);
		$form->setFormData(array('id_event' => $idEvent));
		$buttonsForm= $form->render();

		$data	= array(
			'event'				=> $event->getTemplateData(true, true),
			'buttonsFieldset'	=> $buttonsForm
		);

		$tmpl	= 'ext/calendar/view/event-reminder.tmpl';

		return Todoyu::render($tmpl, $data);
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
		$xmlPath= 'ext/calendar/config/form/event-mailing.xml';
		$form	= TodoyuFormManager::getForm($xmlPath, 0, array(), array('#id_event#'=> $idEvent));

			// Have all email persons but user himself preselected
		$emailPersonIDs	= array_keys(TodoyuCalendarEventManager::getEmailReceivers($idEvent, false));
		$emailPersonIDs	= TodoyuArray::removeByValue($emailPersonIDs, array(Todoyu::personid()), false);

			// Set mail form data
		$form->setFormData(array(
			'id_event' 			=> $idEvent,
			'emailreceivers'	=> $emailPersonIDs,
		));

			// Set the appropriate subject (created, updated, deleted)
		switch($operationID) {
			case OPERATIONTYPE_RECORD_CREATE:
				$subject	= Todoyu::Label('calendar.event.mail.popup.subject.create');
				break;
			case OPERATIONTYPE_RECORD_DELETE:
				$subject	= Todoyu::Label('calendar.event.mail.popup.subject.delete');

					// Remove "don't ask again" button in form of deleted events
				$form->getFieldset('buttons')->removeField('dontaskagain');
				break;
			case OPERATIONTYPE_RECORD_UPDATE:
			default:
				$subject	= Todoyu::Label('calendar.event.mail.popup.subject.update');
				break;
		}

			// Render popup content
		$data	= array(
			'subject'		=> $subject,
			'event'			=> $event->getTemplateData(true, true),
			'mailingForm'	=> $form->render()
		);

		$tmpl	= 'ext/calendar/view/event-mailing.tmpl';

		return Todoyu::render($tmpl, $data);
	}

}

?>