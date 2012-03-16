<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2012, snowflake productions GmbH, Switzerland
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
	 * Get IDs of users which are also assigned to the event (all except current user)
	 *
	 * @param	Integer		$idEvent
	 * @return	Integer[]
	 */
	public static function getOtherAssignedUserIDs($idEvent) {
		$idEvent	= intval($idEvent);

			// Get all assigned users
		$assignedPersonsWithEmail	= TodoyuCalendarEventStaticManager::getEmailReceivers($idEvent, false);
			// Remove current user
		unset($assignedPersonsWithEmail[Todoyu::personid()]);
			// Get user IDs
		return TodoyuArray::getColumn($assignedPersonsWithEmail, 'id_person');
	}



	/**
	 * Remove auto mail fieldset, if no users are receiving auto mail info
	 *
	 * @param	TodoyuForm		$form
	 * @param	Integer			$idEvent
	 * @param	Array			$params
	 * @return	TodoyuForm
	 */
	public static function hookCheckAutoMailFields(TodoyuForm $form, $idEvent, array $params) {
		$autoEmailPersons	= TodoyuCalendarEventMailManager::getAutoNotifiedPersonIDs($idEvent);

		if( sizeof($autoEmailPersons) === 0 ) {
			$form->removeFieldset('autoemail');
		}

		return $form;
	}





	/**
	 * Get person IDs of participants receiving auto-notification event emails
	 *
	 * @param	Integer		$idEvent
	 * @param	Boolean		$ignoreCurrentUser
	 * @return	Integer[]
	 */
	public static function getAutoNotifiedPersonIDs($idEvent, $ignoreCurrentUser = true) {
		$idEvent			= intval($idEvent);
		$event				= TodoyuCalendarEventStaticManager::getEvent($idEvent);
		$autoMailRoleIDs	= TodoyuCalendarManager::getAutoMailRoleIDs();
		$notifiedPersonIDs	= array();

		if( sizeof($autoMailRoleIDs) > 0 ) {
			$assignedPersons= $event->getAssignedPersons();

			foreach($assignedPersons as $assignedPerson) {
				if( $assignedPerson->hasAnyRole($autoMailRoleIDs) ) {
					$notifiedPersonIDs[] = $assignedPerson->getID();
				}
			}
		}

		if( $ignoreCurrentUser ) {
			$notifiedPersonIDs = TodoyuArray::removeByValue($notifiedPersonIDs, array(Todoyu::personid()));
		}

		return $notifiedPersonIDs;
	}



	/**
	 * Extract person IDs from list which are auto notified by mail
	 *
	 * @param	Array	$personIDs
	 * @param	Boolean	$ignoreCurrentUser
	 * @return	Integer[]
	 */
	public static function extractAutoNotifiedPersonIDs(array $personIDs, $ignoreCurrentUser = true) {
		$personIDs			= TodoyuArray::intval($personIDs, true, true);
		$notifiedPersonIDs	= array();
		$autoMailRoleIDs	= TodoyuCalendarManager::getAutoMailRoleIDs();

		foreach($personIDs as $idPerson) {
			$person	= TodoyuContactPersonManager::getPerson($idPerson);

			if( $person->hasAnyRole($autoMailRoleIDs) ) {
				$notifiedPersonIDs[] = $idPerson;
			}
		}

		if( $ignoreCurrentUser ) {
			$notifiedPersonIDs = TodoyuArray::removeByValue($notifiedPersonIDs, array(Todoyu::personid()));
		}

		return $notifiedPersonIDs;
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
	 * @param	String		$operation
	 * @param	Boolean		$isSeriesAction
	 * @return	String
	 */
	public static function getEventMailSubject($operation, $isSeriesAction) {
		$operation	= trim($operation);

		return Todoyu::Label('calendar.event.mail.popup.subject.' . $operation);
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
		$idPersonMailTo	= intval($idPersonMailTo);
		$idPersonSender	= Todoyu::personid($idPersonSender);

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
		$data['eventlink']	= TodoyuString::buildUrl($urlParams, '', true);

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
		$personWrite	= $event->getPersonCreate();

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
	public static function getAutoNotifiedPersonIDsOLD($participantIDs = array()) {
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
					$where		= Todoyu::db()->buildInArrayQuery($autoMailPersonIDs);
					$group		= 'id';
					$orderBy	= 'lastname,firstname';

					$autoMailPersonIDs	= Todoyu::db()->getColumn($field, $table, $where, $group, $orderBy);
				}
			}
		}

		$autoMailPersonIDs	= TodoyuArray::removeByValue($autoMailPersonIDs, array(Todoyu::personid()));

		return $autoMailPersonIDs;
	}



	/**
	 * Hook for event moving
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 */
	public static function hookEventMoved($idEvent, $dateStart, $dateEnd) {
		self::sendAutoInfoMails($idEvent, array('new'=>false));
	}



	/**
	 * Hook for event saving. Send auto info mails to special group users
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$options
	 */
	public static function hookEventSaved($idEvent, array $options = array()) {
		$options['operation'] = $options['new'] ? 'create' : 'update';

		if( !$options['batch'] ) {
			self::sendAutoInfoMails($idEvent, $options);
		}
	}



	/**
	 * Hook for event delete
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$options
	 */
	public static function hookEventDeleted($idEvent, array $options = array()) {
		$options['operation'] = 'delete';

			// Don't send mails on batch delete
		if( !$options['batch'] ) {
			self::sendAutoInfoMails($idEvent, $options);
		}
	}



	/**
	 * Send info mails to all assigned users of the event which are in the specified groups
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$options
	 * @return	Integer[]
	 */
	public static function sendAutoInfoMails($idEvent, array $options = array()) {
		$autoMailUserIDs = TodoyuCalendarEventMailManager::getAutoNotifiedPersonIDs($idEvent, true);

		if( sizeof($autoMailUserIDs) > 0 ) {
			self::sendEvent($idEvent, $autoMailUserIDs, $options);
		}

		return $autoMailUserIDs;
	}



	/**
	 * Event save hook. Send emails
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$personIDs
	 * @param	Array		$options
	 * @return	Boolean
	 */
	public static function sendEvent($idEvent, $personIDs, array $options = array()) {
		$personIDs	= array_unique(TodoyuArray::intval($personIDs, true, true));
		$sent		= false;

		if( sizeof($personIDs) > 0 ) {
			$sent	= self::sendEmails($idEvent, $personIDs, $options);
			if( $sent ) {
				self::saveMailsSent($idEvent, $personIDs);
			}
		}

		return $sent;
	}



	/**
	 * Send event information email to the persons
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$personIDs
	 * @param	Array		$options
	 * @return	Boolean
	 */
	public static function sendEmails($idEvent, array $personIDs, array $options = array()) {
		$idEvent	= intval($idEvent);
		$personIDs	= TodoyuArray::intval($personIDs, true, true);

		$succeeded	= true;
		foreach($personIDs as $idPerson) {
			$result	= self::sendInfoMail($idEvent, $idPerson, $options);

			if( !$result ) {
				$succeeded	= false;
			}
		}

		return $succeeded;
	}



	/**
	 * Send an event email to a person
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @param	Array		$options
	 * @return	Boolean		Success
	 */
	public static function sendInfoMail($idEvent, $idPerson, array $options = array()) {
		$idEvent	= intval($idEvent);
		$idPerson	= intval($idPerson);
		$mail		= new TodoyuCalendarEventInfoEmail($idEvent, $idPerson, $options);
		$status		= $mail->send();

		//TodoyuDebug::printInFirebug($idPerson, 'send mail for ' . $idEvent . ' to');

		TodoyuHookManager::callHook('calendar', 'email.info', array($idEvent, $idPerson, $options, $status));

		return $status;
	}

}

?>