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
	 * Get email reminder of given event/person
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return	TodoyuCalendarReminderEmail
	 */
	public static function getReminder($idEvent, $idPerson = 0) {
		$idEvent	= intval($idEvent);
		$idPerson	= personid($idPerson);

		return new TodoyuCalendarReminderEmail($idEvent, $idPerson);
	}



	/**
	 * Get timestamp for email reminder of newly assigned event (advance-time from profile, fallback: extconf)
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public static function getNewEventMailTime($idEvent, $idPerson = 0) {
		$idEvent	= intval($idEvent);
		$idPerson	= personid($idPerson);

		$event		= TodoyuCalendarEventManager::getEvent($idEvent);
		$dateStart	= $event->getStartDate();
		$advanceTime= self::getDefaultAdvanceTime($idPerson);

		return $dateStart  - $advanceTime;
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

		return self::getReminder($idEvent, $idPerson)->getDateRemindEmail();
	}



	/**
	 * Check whether email reminders are activated in profile of current person, fallback: extconf
	 *
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function isActivatedForPerson($idPerson = 0) {
		$idPerson	= personid($idPerson);

		if( allowed('calendar', 'reminder:email') ) {
			if( TodoyuPreferenceManager::isPreferenceSet(EXTID_CALENDAR, 'is_reminderemailactive', 0, null, 0, $idPerson) ) {
					// Return pref. from profile
				return TodoyuCalendarPreferences::getPref('is_reminderemail_active', 0, 0, false, $idPerson) ? true : false;
			} else {
					// Return pref. from extconf
				return TodoyuSysmanagerExtConfManager::getExtConfValue('calendar', 'is_reminderemail_active');
			}
		}
			// No
		return false;
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

		return self::getReminder($idEvent, $idPerson)->getAdvanceTime();
	}



	/**
	 * Get current person's event reminder emails advance time from current person prefs, fallback: extconf
	 *
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public static function getDefaultAdvanceTime($idPerson = 0) {
		$idPerson	= personid($idPerson);

		if( TodoyuPreferenceManager::isPreferenceSet(EXTID_CALENDAR, 'reminderemail_advancetime', 0, null, 0, $idPerson) ) {
				// Return pref. from profile
			return intval(TodoyuCalendarPreferences::getPref('reminderemail_advancetime', 0, 0, false, $idPerson));
		}

			// Fallback: take preset from extconf
		return intval(TodoyuSysmanagerExtConfManager::getExtConfValue('calendar', 'reminderemail_advancetime'));
	}



	/**
	 * @static
	 * @param	Integer		$idEvent
	 * @return	String
	 */
	public static function getSelectedAdvanceTimeContextMenuOptionKey($idEvent) {
		$idEvent	= intval($idEvent);

			// Get time amount before the event when sending email is scheduled
		$advanceTime	= self::getAdvanceTime($idEvent);

		return '5m';
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

		$idEvent	= intval($idEvent);
		$idPerson	= personid($idPerson);

		return TodoyuCalendarEventManager::getEvent($idEvent)->isPersonAssigned($idPerson);
	}

}

?>