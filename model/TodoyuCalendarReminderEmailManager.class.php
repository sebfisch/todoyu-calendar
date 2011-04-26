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
 * Event Reminder Email Manager
 *
 * @package			Todoyu
 * @subpackage		Calendar
 */
class TodoyuCalendarReminderEmailManager {

	/**
	 * @var String		Default table for database requests
	 */
	const TABLE = 'ext_calendar_mm_event_person';

	/**
	 * @var	String		Type of reminder
	 */
	const REMINDERTYPE = CALENDAR_TYPE_EVENTREMINDER_EMAIL;



	/**
	 * Get email reminder of given event/person
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return	TodoyuCalendarReminderEmail
	 */
	public static function getReminderByAssignment($idEvent, $idPerson = 0) {
		$idEvent	= intval($idEvent);
		$idPerson	= intval($idPerson);

		return new TodoyuCalendarReminderEmail($idEvent, $idPerson);
	}



	/**
	 * Update email reminder sending time of given event/person
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$timeEmail
	 * @param	Integer		$idPerson
	 */
	public static function updateReminderTime($idEvent, $timeEmail, $idPerson = 0) {
		TodoyuCalendarReminderManager::updateReminderTime(self::REMINDERTYPE, $idEvent, $timeEmail, $idPerson);
	}



	/**
	 * Update email reminder scheduling of given event from given form data
	 *
	 * @param	Array	$data
	 * @param	Integer	$idPerson
	 */
	public static function updateReminderTimeFromEventData(array $data, $idPerson = 0) {
		$idPerson	= personid($idPerson);
		$idEvent	= intval($data['id']);

		$timeRemind	= TodoyuCalendarReminderManager::getRemindingTimeByEventData(self::REMINDERTYPE, $data);

		self::updateReminderTime($idEvent, $timeRemind, $idPerson);
	}



	/**
	 * Get timestamp for email reminder of newly assigned event (advance-time from profile, fallback: extconf)
	 *
	 * @param	Integer		$dateStart
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public static function getNewEventMailTime($dateStart, $idPerson = 0) {
		$dateStart	= intval($dateStart);
		$idPerson	= personid($idPerson);

		return $dateStart  - self::getDefaultAdvanceTime($idPerson);
	}



	/**
	 * Get scheduled reminder mailing time of given event
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function getReminderMailTime($idEvent, $idPerson = 0) {
		if( ! allowed('calendar', 'reminders:email') ) {
			return false;
		}

		$idEvent	= intval($idEvent);
		$idPerson	= personid($idPerson);

		return self::getReminderByAssignment($idEvent, $idPerson)->getDateRemindEmail();
	}



	/**
	 * Check whether email reminders are activated in profile of current person, fallback: extconf
	 *
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function isActivatedForPerson($idPerson = 0) {
		return TodoyuCalendarReminderManager::isRemindertypeActivated(self::REMINDERTYPE, $idPerson);
	}



	/**
	 * Get amount of time before event when to send reminder email
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return	Integer					Amount of seconds
	 */
	public static function getAdvanceTime($idEvent, $idPerson = 0) {
		$idEvent	= intval($idEvent);
		$idPerson	= personid($idPerson);

		return self::getReminderByAssignment($idEvent, $idPerson)->getAdvanceTime();
	}



	/**
	 * Get current person's event reminder emails advance time from current person prefs, fallback: extconf
	 *
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public static function getDefaultAdvanceTime($idPerson = 0) {
		return TodoyuCalendarReminderManager::getDefaultAdvanceTime(self::REMINDERTYPE, $idPerson);
	}



	/**
	 * Get key of context menu sub-option of selected advance time
	 *
	 * @param	Integer				$idEvent
	 * @return	Integer|Boolean
	 */
	public static function getSelectedAdvanceTimeContextMenuOptionKey($idEvent) {
		$idEvent	= intval($idEvent);

		$scheduledTime	= self::getReminderMailTime($idEvent);
		if( $scheduledTime == 0 ) {
			return false;
		}

		return self::getAdvanceTime($idEvent);
	}



	/**
	 * Check whether given/current person can schedule a reminder for the event of the given ID
	 *
	 * @param	Integer		$idEvent
	 * @return	Boolean
	 */
	public static function isEventSchedulable($idEvent, $idPerson = 0) {
		if( ! allowed('calendar', 'reminders:email') ) {
			return false;
		}

		if( ! TodoyuCalendarReminderManager::isEmailReminderEnabled() ) {
			return false;
		}

		return TodoyuCalendarReminderManager::isEventSchedulable(self::REMINDERTYPE, $idEvent, $idPerson);
	}



	/**
	 * Get reminder context menu options (hilite selected, deactivate past options)
	 *
	 * @param	Integer	$idEvent
	 * @param	Array	$options
	 * @return	Array
	 */
	public static function getContextMenuItems($idEvent) {
		$idEvent	= intval($idEvent);
		$options	= Todoyu::$CONFIG['EXT']['calendar']['ContextMenu']['Event']['reminderemail'];

			// Set selected option CSS class
		$selectedTimeOptionKey	= self::getSelectedAdvanceTimeContextMenuOptionKey($idEvent);
		if( $selectedTimeOptionKey === false ) {
			$options['submenu'][0]['class'] .= ' selected';
		} elseif( key_exists($selectedTimeOptionKey, $options['submenu']) ) {
			$options['submenu'][$selectedTimeOptionKey]['class'] .= ' selected';
		}
			// Set options disabled which are in the past already
		$options['submenu']	= TodoyuCalendarReminderManager::disablePastTimeKeyOptions($options['submenu'], $idEvent);

		return $options;
	}

}

?>