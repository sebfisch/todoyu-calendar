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
		$event		= TodoyuCalendarEventStaticManager::getEvent($idEvent);

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
	 * Get event mail subject label by operation ID (create, update, delete)
	 *
	 * @param	Integer		$operationID
	 * @return	String
	 */
	public static function getEventMailSubjectByOperationID($operationID) {
		$operationID	= intval($operationID);
		if( ! in_array($operationID, array(OPERATIONTYPE_RECORD_CREATE, OPERATIONTYPE_RECORD_DELETE, OPERATIONTYPE_RECORD_UPDATE)) ) {
			$operationID	= OPERATIONTYPE_RECORD_UPDATE;
		}

		$subjectKeys	= array(
			OPERATIONTYPE_RECORD_CREATE => 'create',
			OPERATIONTYPE_RECORD_DELETE	=> 'delete',
			OPERATIONTYPE_RECORD_UPDATE => 'update'
		);

		return Todoyu::Label('calendar.event.mail.popup.subject.' . $subjectKeys[$operationID]);
	}



	/**
	 * Get data array to render event email
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPersonMailTo
	 * @param	Boolean		$isSentBySystem
	 * @param	Integer		$idPersonSender
	 * @return	Array
	 */
	public static function getMailData($idEvent, $idPersonMailTo, $isSentBySystem = false, $idPersonSender = 0) {
		$idEvent		= intval($idEvent);
		$idPersonMailTo= intval($idPersonMailTo);
		$idPersonSender = Todoyu::personid($idPersonSender);

		$event			= TodoyuCalendarEventStaticManager::getEvent($idEvent, true);

		$data	= array(
			'event'			=> $event->getTemplateData(),
			'personReceive'	=> TodoyuContactPersonManager::getPerson($idPersonMailTo)->getTemplateData(),
			'personSend'	=> self::getPersonSendTemplateData($idPersonSender, $isSentBySystem),
			'personWrite'	=> self::getPersonWriteTemplateData($event),
			'attendees'		=> TodoyuCalendarEventStaticManager::getAssignedPersonsOfEvent($idEvent, true)
		);

		$urlParams	= array(
			'ext'	=> 'calendar',
			'event'	=> $idEvent,
			'tab'	=> 'view' //'week'
		);
		$data['eventlink'] = TodoyuString::buildUrl($urlParams, '', true);

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
	 * @param	TodoyuCalendarEventStatic		$event
	 * @return	Array
	 */
	public static function getPersonWriteTemplateData(TodoyuCalendarEventStatic $event) {
		$personWrite	= $event->getCreatePerson();

		if( $personWrite !== false ) {
			return $personWrite->getTemplateData();
		}

		 return array();
	}



	/**
	 * Get person IDs of participants who are being auto-notified about event changes/creations
	 *
	 * @param	Array	$participantIDs
	 * @return	Array|Integer[]
	 */
	public static function getAutoNotifiedPersonIDs($participantIDs = array()) {
		$autoMailPersonIDs	= array();

		if( sizeof($participantIDs) > 0 ) {
			$participantIDs		= TodoyuArray::intval($participantIDs);

				// Get preset roles
			$autoMailRoles	= TodoyuSysmanagerExtConfManager::getExtConfValue('calendar', 'autosendeventmail');

			if( ! empty($autoMailRoles) ) {
					// Get person IDs of roles
				$autoMailRoles	= TodoyuArray::intExplode(',', $autoMailRoles);
				foreach($autoMailRoles as $idRole) {
					$autoMailPersonIDs	= array_merge($autoMailPersonIDs, TodoyuRoleManager::getPersonIDs($idRole));
				}
				$autoMailPersonIDs	= TodoyuArray::intval($autoMailPersonIDs);

					// Reduce to event participants
				$autoMailPersonIDs	= array_intersect($autoMailPersonIDs, $participantIDs);

					// Sort persons alphabetically
				if( sizeof($autoMailPersonIDs) > 0 ) {
					$field		= 'id';
					$table		= TodoyuContactPersonManager::TABLE;
					$where		= 'id IN (' . implode(',', $autoMailPersonIDs) . ')';
					$group		= 'id';
					$orderBy	= 'lastname,firstname';

					$autoMailPersonIDs	= Todoyu::db()->getColumn($field, $table, $where, $group, $orderBy);
				}
			}
		}

		$autoMailPersonIDs	= TodoyuArray::removeByValue($autoMailPersonIDs, array(Todoyu::personid()));

		return $autoMailPersonIDs;
	}

}

?>