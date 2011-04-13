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
class TodoyuCalendarReminderHelper {

	/**
	 * Get prefix ('email' / 'popup') of given reminder type
	 *
	 * @param	Integer		$reminderType
	 * @return	String
	 */
	public static function getReminderTypePrefix($reminderType) {
		$reminderType	= intval($reminderType);

		return $reminderType === REMINDERTYPE_EMAIL ? 'email' : 'popup';
	}



	/**
	 * Get record ID of event-person MM relation of given event/person
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public static function getMMrelationRecordID($idEvent, $idPerson = 0) {
		$idEvent	= intval($idEvent);
		$idPerson	= personid($idPerson);

		$field	= 'id';
		$table	= 'ext_calendar_mm_event_person';

		$where	= '		id_event 	= ' . $idEvent
				. ' AND	id_person	= ' . $idPerson;

		$limit	= '1';

		$row	= Todoyu::db()->getColumn($field, $table, $where, '', '', $limit, 'id');
		return $row[0];
	}



	/**
	 * Get reminder object of given type, event and person
	 *
	 * @param	Integer		$reminderType
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return TodoyuCalendarReminderEmail|TodoyuCalendarReminderPopup
	 */
	public static function getReminder($reminderType, $idEvent, $idPerson = 0) {
		$reminderType	= intval($reminderType);
		$idPerson		= personid($idPerson);

		if( $reminderType == REMINDERTYPE_EMAIL ) {
			return TodoyuCalendarReminderEmailManager::getReminder($idEvent, $idPerson);
		} else {
			return TodoyuCalendarReminderPopupManager::getReminder($idEvent, $idPerson);
		}
	}



	/**
	 * Check whether email reminders of given type are activated in profile of current person, fallback: extconf
	 *
	 * @param	Integer		$reminderType
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function isReminderGenerallyActivated($reminderType, $idPerson = 0) {
		$idPerson	= personid($idPerson);
		$typePrefix	= self::getReminderTypePrefix($reminderType);

		if( allowed('calendar', 'reminder:' . $typePrefix) ) {
			$preference	= 'is_reminder' . $typePrefix . 'active';
			if( TodoyuPreferenceManager::isPreferenceSet(EXTID_CALENDAR, $preference, 0, null, 0, $idPerson) ) {
					// Return pref. from profile
				return TodoyuCalendarPreferences::getPref($preference, 0, 0, false, $idPerson) ? true : false;
			} else {
					// Return pref. from extconf
				return TodoyuSysmanagerExtConfManager::getExtConfValue('calendar', 'is_reminder' . $typePrefix . '_active') ? true : false;
			}
		}
			// No
		return false;
	}



	/**
	 * Check whether given/current person can schedule a reminder for the event of the given type / ID
	 *
	 * @param	Integer		$reminderType
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return
	 */
	public static function isEventSchedulable($reminderType, $idEvent, $idPerson = 0) {
		$idEvent	= intval($idEvent);
		$idPerson	= personid($idPerson);

		return TodoyuCalendarEventManager::getEvent($idEvent)->isPersonAssigned($idPerson);
	}



	/**
	 * Get default advance (time before event) reminding time of given reminder type and person
	 *
	 * @param	Integer		$reminderType
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public static function getDefaultAdvanceTime($reminderType, $idPerson = 0) {
		$idPerson	= personid($idPerson);
		$typePrefix	= self::getReminderTypePrefix($reminderType);

		if( TodoyuPreferenceManager::isPreferenceSet(EXTID_CALENDAR, 'reminder' . $typePrefix . '_advancetime', 0, null, 0, $idPerson) ) {
				// Return pref. from profile
			return intval(TodoyuCalendarPreferences::getPref('reminder' . $typePrefix . '_advancetime', 0, 0, false, $idPerson));
		}

			// Fallback: take preset from extconf
		return intval(TodoyuSysmanagerExtConfManager::getExtConfValue('calendar', 'reminder' . $typePrefix . '_advancetime'));
	}



	/**
	 * Update reminder activation (popup / mailing) time of given reminder
	 *
	 * @param	Integer		$reminderType
	 * @param	Integer		$timestamp
	 * @param	Integer		$idPerson
	 */
	public static function updateReminderTime($reminderType, $idEvent, $timestamp, $idPerson = 0) {
		$idEvent	= intval($idEvent);
		$timestamp	= intval($timestamp);
		$idPerson	= personid($idPerson);

		$reminder	= self::getReminder($reminderType, $idEvent, $idPerson);
		/** @var TodoyuCalendarEvent	$event */
		$event		= $reminder->getEvent();

		if( $event->isPersonAssigned($idPerson) ) {
			$table		= 'ext_calendar_mm_event_person';
			$idRecord	= $reminder->getID();
			$typePrefix	= self::getReminderTypePrefix($reminderType);

			$fieldValues	= array(
				'date_remind' . $typePrefix	=> $timestamp,
			);

			Todoyu::db()->updateRecord($table, $idRecord, $fieldValues);
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
					$subOptions[$secondsBefore]['class'] .= ' past disabled';
				}
			}
		}

		return $subOptions;
	}

}

?>