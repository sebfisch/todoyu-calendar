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
 * Various helper functions common for email and popup reminders
 *
 * @package			Todoyu
 * @subpackage		Calendar
 */
class TodoyuCalendarReminderManager {

	/**
	 * @var String		Default table for database requests
	 */
	const TABLE = 'ext_calendar_mm_event_person';



	/**
	 * Get reminder object to given event/person
	 *
	 * @param	Integer		$idReminder
	 * @return	TodoyuCalendarReminder
	 */
	public static function getReminder($idReminder) {
		$idReminder	= intval($idReminder);

		return TodoyuRecordManager::getRecord('TodoyuCalendarReminder', $idReminder);
	}



	/**
	 * Get reminder record data
	 *
	 * @param	Integer		$idReminder
	 * @return	Array
	 */
	private static function getReminderRecord($idReminder) {
		$idReminder	= intval($idReminder);

		return Todoyu::db()->getRecord(self::TABLE, $idReminder);
	}



	/**
	 * Get reminder object to given event/person
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return	TodoyuCalendarReminder
	 */
	public static function getReminderByAssignment($idEvent, $idPerson) {
		$idReminder	= self::getReminderIDByAssignment($idEvent, $idPerson);

		return self::getReminder($idReminder);
	}



	/**
	 * Get record ID of event-person MM relation of given event/person
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public static function getReminderIDByAssignment($idEvent, $idPerson) {
		$idEvent	= intval($idEvent);
		$idPerson	= Todoyu::personid($idPerson);

		return Todoyu::db()->getMMid('ext_calendar_mm_event_person', 'id_event', $idEvent, 'id_person', $idPerson);
	}



	/**
	 * Get prefix ('email' / 'popup') of given reminder type
	 *
	 * @param	Integer		$reminderType
	 * @return	String
	 */
	public static function getReminderTypePrefix($reminderType) {
		$reminderType	= intval($reminderType);

		return $reminderType === CALENDAR_TYPE_EVENTREMINDER_EMAIL ? 'email' : 'popup';
	}



	/**
	 * Get reminder object of given type, event and person
	 *
	 * @param	Integer		$reminderType
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return TodoyuCalendarReminderEmail|TodoyuCalendarReminderPopup
	 */
	public static function getReminderTypeByAssignment($reminderType, $idEvent, $idPerson = 0) {
		$reminderType	= intval($reminderType);
		$idPerson		= Todoyu::personid($idPerson);

		if( $reminderType == CALENDAR_TYPE_EVENTREMINDER_EMAIL ) {
			return TodoyuCalendarReminderEmailManager::getReminderByAssignment($idEvent, $idPerson);
		} else {
			return TodoyuCalendarReminderPopupManager::getReminderByAssignment($idEvent, $idPerson);
		}
	}



	/**
	 * Update event-person MM record of given ID
	 *
	 * @param	Integer		$idRecord
	 * @param	Array		$fieldValues
	 */
	public static function updateMMrecord($idRecord, array $fieldValues) {
		$table		= 'ext_calendar_mm_event_person';
		$idRecord	= intval($idRecord);

		Todoyu::db()->updateRecord($table, $idRecord, $fieldValues);
	}



	/**
	 * Check whether email reminders of given type are activated in profile of current person, fallback: extconf
	 *
	 * @param	Integer		$reminderType
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function isRemindertypeActivated($reminderType, $idPerson = 0) {
		$idPerson	= Todoyu::personid($idPerson);
		$typePrefix	= self::getReminderTypePrefix($reminderType);

		if( Todoyu::allowed('calendar', 'reminder:' . $typePrefix) ) {
			$preference	= 'is_reminder' . $typePrefix . 'active';
			if( TodoyuPreferenceManager::isPreferenceSet(EXTID_CALENDAR, $preference, 0, null, 0, $idPerson) ) {
					// Return pref. from profile
				return TodoyuCalendarPreferences::getPref($preference, 0, 0, false, $idPerson) ? true : false;
			} else {
					// Return pref. from extconf
				return TodoyuSysmanagerExtConfManager::getExtConfValue('calendar', 'is_reminder' . $typePrefix . '_active') ? true : false;
			}
		}

		return false;
	}



	/**
	 * Check whether given/current person can schedule a reminder for the event of the given type / ID
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return
	 */
	public static function isPersonAssigned($idEvent, $idPerson = 0) {
		$idEvent		= intval($idEvent);
		$idPerson		= Todoyu::personid($idPerson);

		return TodoyuCalendarEventManager::getEvent($idEvent)->isPersonAssigned($idPerson);
	}



	/**
	 * Check whether reminders with emails are enabled
	 *
	 * @return	Boolean
	 */
	public static function isEmailReminderEnabled() {
		$active	= TodoyuSysmanagerExtConfManager::getExtConfValue('calendar', 'is_reminderemail_active');

		return intval($active) === 1;
	}




	/**
	 * Check whether reminders with popups are enabled
	 *
	 * @return	Boolean
	 */
	public static function isPopupReminderEnabled() {
		$active	= TodoyuSysmanagerExtConfManager::getExtConfValue('calendar', 'is_reminderpopup_active');

		return intval($active) === 1;
	}



	/**
	 * Get default advance (time before event) reminding time of given reminder type and person
	 *
	 * @param	Integer		$reminderType
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public static function getDefaultAdvanceTime($reminderType, $idPerson = 0) {
		$reminderType	= intval($reminderType);
		$idPerson		= Todoyu::personid($idPerson);

		$typePrefix	= self::getReminderTypePrefix($reminderType);
		$preference	= 'reminder' . $typePrefix . '_advancetime';

		if( TodoyuPreferenceManager::isPreferenceSet(EXTID_CALENDAR, $preference, 0, null, 0, $idPerson) ) {
				// Return pref. from profile
			return intval(TodoyuCalendarPreferences::getPref($preference, 0, 0, false, $idPerson));
		}
			// Fallback: take preset from extconf
		return intval(TodoyuSysmanagerExtConfManager::getExtConfValue('calendar', $preference));
	}



	/**
	 * Deactivate (set time to 0) given reminder of given type, person and event
	 *
	 * @param	Integer		$reminderType
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 */
	public static function deactivateReminder($reminderType, $idEvent, $idPerson = 0) {
		$idEvent	= intval($idEvent);
		$idPerson	= Todoyu::personid($idPerson);

		self::updateReminderTime($reminderType, $idEvent, 0, $idPerson);
	}



	/**
	 * Update reminder activation (popup / mailing) time of given reminder
	 *
	 * @param	Integer		$reminderType
	 * @param	Integer		$dateRemind
	 * @param	Integer		$idPerson
	 */
	public static function updateReminderTime($reminderType, $idEvent, $dateRemind, $idPerson = 0) {
		$idEvent	= intval($idEvent);
		$dateRemind	= intval($dateRemind);
		$idPerson	= Todoyu::personid($idPerson);

		$reminder	= self::getReminderTypeByAssignment($reminderType, $idEvent, $idPerson);
		/** @var TodoyuCalendarEvent	$event */
		$event		= $reminder->getEvent();

		if( $event->isPersonAssigned($idPerson) ) {
			$idRecord	= $reminder->getID();
			$typePrefix	= self::getReminderTypePrefix($reminderType);

				// Update reminding time
			$fieldValues	= array(
				'date_remind' . $typePrefix	=> $dateRemind,
			);
			self::updateMMrecord($idRecord, $fieldValues);

				// Update dismission flag
			$isDismissed = $dateRemind == 0;
			self::updateReminderDismission($reminderType, $idRecord, $isDismissed);
		}
	}



	/**
	 * Set reminder dismissed/active
	 *
	 * @param	Integer		$reminderType
	 * @param	Integer		$idRecord
	 * @param	Boolean		$isDismissed
	 */
	public static function updateReminderDismission($reminderType, $idRecord, $isDismissed = false) {
		$idRecord		= intval($idRecord);
		$isDismissed	= $isDismissed ? 1 : 0;
		$dismissionField= $reminderType == CALENDAR_TYPE_EVENTREMINDER_EMAIL ? 'is_remindemailsent' : 'is_remindpopupdismissed';

		$fieldValues	= array(
			$dismissionField	=> $isDismissed
		);

		self::updateMMrecord($idRecord, $fieldValues);
	}



	/**
	 * Update scheduled reminders (of all assigned persons of event) relative to shifted time of event
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$offset
	 */
	public static function shiftRemindingTimes($idEvent, $offset) {
		$idEvent= intval($idEvent);
		$offset	= intval($offset);

		$personIDs	= TodoyuCalendarEventManager::getEvent($idEvent)->getAssignedPersonIDs();
		foreach($personIDs as $idPerson) {
			$reminder	= self::getReminderByAssignment($idEvent, $idPerson);

			$dateRemindEmail	= $reminder->getDateRemind(CALENDAR_TYPE_EVENTREMINDER_EMAIL);
			$dateRemindPopup	= $reminder->getDateRemind(CALENDAR_TYPE_EVENTREMINDER_POPUP);

			$fieldValues	= array();

			if( $dateRemindEmail > 0 ) {
				$fieldValues['date_remindemail']	= $dateRemindEmail + $offset;
			}

			if( $dateRemindEmail > 0 ) {
				$fieldValues['date_remindpopup']	= $dateRemindPopup + $offset;
			}

			if( ! empty($fieldValues) ) {
				self::updateMMrecord($reminder->getID(), $fieldValues);
			}
		}
	}



	/**
	 * Calculate time when of reminder activation from event data array
	 *
	 * @param	Integer		$reminderType
	 * @param	Array		$eventData
	 * @return	Integer
	 */
	public static function getRemindingTimeByEventData($reminderType, array $data) {
		$typePrefix	= self::getReminderTypePrefix($reminderType);

		if( $data['is_reminder' . $typePrefix . '_active'] ) {
			return $data['date_start'] - $data['reminder' . $typePrefix . '_advancetime'];
		}

		return 0;
	}



	/**
	 * Set options disabled which are in the past already
	 *
	 * @param	Array	$subOptions
	 * @param	Integer	$idEvent
	 * @return	Array
	 */
	public static function disablePastTimeKeyOptions(array $subOptions, $idEvent) {
		$idEvent		= intval($idEvent);
		$eventDateStart	= TodoyuCalendarEventManager::getEvent($idEvent)->getStartDate();

		foreach( $subOptions as $secondsBefore => $optionConfig ) {
			if( $secondsBefore > 0 ) {
				$timeScheduled	= $eventDateStart - $secondsBefore;
				if( $timeScheduled <= NOW ) {
					$subOptions[$secondsBefore]['class'] .= ' disabled';
				}
			}
		}

		return $subOptions;
	}

}

?>