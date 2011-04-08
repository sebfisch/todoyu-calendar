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
	 * Check whether email reminders are activated in profile of current person, fallback: extconf
	 *
	 * @return	Boolean
	 */
	public static function isActivatedForCurrentPerson() {
		if( allowed('calendar', 'reminder:email') ) {
			if( TodoyuPreferenceManager::isPreferenceSet(EXTID_CALENDAR, 'is_reminderemailactive', 0, null, 0, personid()) ) {
					// Return pref. from profile
				return TodoyuCalendarPreferences::getPref('is_reminderemail_active', 0, 0, false, personid()) ? true : false;
			} else {
					// Return pref. from extconf
				return TodoyuSysmanagerExtConfManager::getExtConfValue('calendar', 'is_reminderemail_active');
			}
		}
			// No
		return false;
	}



	/**
	 * Get current person's event reminder emails advance time from current person prefs, fallback: extconf
	 *
	 * @return	Integer
	 */
	public static function getCurrentPersonDefaultAdvanceTime() {
		if( TodoyuPreferenceManager::isPreferenceSet(EXTID_CALENDAR, 'reminderemail_advancetime', 0, null, 0, personid()) ) {
				// Return pref. from profile
			return intval(TodoyuCalendarPreferences::getPref('reminderemail_advancetime', 0, 0, false, personid()));
		}

			// Fallback: take preset from extconf
		return intval(TodoyuSysmanagerExtConfManager::getExtConfValue('calendar', 'reminderemail_advancetime'));
	}



	/**
	 * Get current person's event reminder emails advance time for given event
	 *
	 * @param	Integer		$idEvent
	 * @return	Integer
	 */
	public static function getCurrentPersonReminderAdvanceTime($idEvent) {
		$idEvent	= intval($idEvent);
// @todo	change this to be event specific!
		return self::getCurrentPersonDefaultAdvanceTime();
	}



	/**
	 * @static
	 * @param	Integer		$idEvent
	 * @return	String
	 */
	public static function getSelectedAdvanceTimeContextMenuOptionKey($idEvent) {
		$idEvent	= intval($idEvent);

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


	/**
	 * Get (next) reminder mailing time of given event
	 *
	 * @param	Integer		$idEvent
	 * @return	Boolean
	 */
	public static function getMailTime($idEvent) {
		if( ! allowed('calendar', 'reminders:email') ) {
			return false;
		}

		$idEvent	= intval($idEvent);
		$eventStart	= TodoyuCalendarEventManager::getEvent($idEvent)->getStartDate();

		if( $eventStart < NOW ) {
				// Mails are only send BEFORE events
			return false;
		}

		return $eventStart - self::getCurrentPersonReminderAdvanceTime($idEvent);
	}

}

?>