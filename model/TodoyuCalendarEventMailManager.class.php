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
 * Manage event mail DB logs
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarEventMailManager {

	/**
	 * Check whether mail popup is to be shown (not disabled, at least one other attendee person has email)
	 *
	 * @param	Integer		$idEvent
	 * @return	Boolean
	 */
	public static function isMailPopupToBeShown($idEvent) {
		$isPopupDisabled	= TodoyuCalendarPreferences::isMailPopupDisabled();

		if( $isPopupDisabled ) {
			return false;
		}

		$idEvent	= intval($idEvent);
		$event		= TodoyuCalendarEventManager::getEvent($idEvent);

		if( ! $event->areOtherPersonsAssigned() ) {
			return false;
		}

		return $event->hasAnyAssignedPersonAnEmailAddress();
	}



	/**
	 * Save log record about persons the given mail has been sent to
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$personIDs			Persons the event has been sent to
	 */
	public static function saveMailsSent($idEvent, array $personIDs = array() ) {
		TodoyuMailManager::saveMailsSent(EXTID_CALENDAR, CALENDAR_TYPE_EVENT, $idEvent, $personIDs);
	}



	/**
	 * log sent event email of given event to given person
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 */
	public static function addMailSent($idEvent, $idPerson) {
		TodoyuMailManager::addMailSent(EXTID_CALENDAR, CALENDAR_TYPE_EVENT, $idEvent, $idPerson);
	}



	/**
	 * Get persons the given event has been sent to by email
	 *
	 * @param	Integer		$idEvent
	 * @return	Array
	 */
	public static function getEmailPersons($idEvent) {
		return TodoyuMailManager::getEmailPersons(EXTID_CALENDAR, CALENDAR_TYPE_EVENT, $idEvent);
	}



	/**
	 * Get data array to render email
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPersonMailtTo
	 * @param	Boolean		$isSentBySystem
	 * @param	Integer		$idPersonSender
	 * @return	Array
	 */
	public static function getMailData($idEvent, $idPersonMailtTo, $isSentBySystem = false, $idPersonSender = 0) {
		$idEvent		= intval($idEvent);
		$idPersonMailtTo= intval($idPersonMailtTo);
		$idPersonSender = Todoyu::personid($idPersonSender);

		$event			= TodoyuCalendarEventManager::getEvent($idEvent, true);

		$data	= array(
			'event'			=> $event->getTemplateData(),
			'personReceive'	=> TodoyuContactPersonManager::getPerson($idPersonMailtTo)->getTemplateData(),
			'personSend'	=> self::getPersonSendTemplateData($idPersonSender, $isSentBySystem),
			'personWrite'	=> self::getPersonWriteTemplateData($event),
			'attendees'		=> TodoyuCalendarEventManager::getAssignedPersonsOfEvent($idEvent, true)
		);

		$urlParams	= array(
			'ext'	=> 'calendar',
			'event'	=> $idEvent,
			'tab'	=> 'week'
		);
		$data['eventlink'] = TodoyuString::buildUrl($urlParams, 'event-' . $idEvent, true);

		return $data;
	}



	/**
	 * Get event email sender person template data
	 *
	 * @param	Integer		$idPersonSender
	 * @param	Boolean		$isSentBySystem			Automatically sent, not by a person?
	 * @return	Array
	 */
	public static function getPersonSendTemplateData($idPersonSender, $isSentBySystem = false) {
		if( $isSentBySystem ) {
			return array(
				'firstname'	=> Todoyu::$CONFIG['SYSTEM']['name']
			);
		}

		return TodoyuAuth::getPerson($idPersonSender)->getTemplateData();
	}



	/**
	 * Get event email sender person template data
	 *
	 * @param	TodoyuCalendarEvent		$event
	 * @return	Array
	 */
	public static function getPersonWriteTemplateData(TodoyuCalendarEvent $event) {
		$personWrite	= $event->getCreatePerson();

		if( $personWrite !== false ) {
			return $personWrite->getTemplateData();
		}

		 return array();
	}

}

?>