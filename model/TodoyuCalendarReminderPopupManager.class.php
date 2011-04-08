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
 * Event Reminder Popups Manager
 *
 * @package			Todoyu
 * @subpackage		Calendar
 */
class TodoyuCalendarReminderPopupManager {

	/**
	 * @var String		Default table for database requests
	 */
	const TABLE = 'ext_calendar_mm_event_person';



	/**
	 * Get current person's reminder to given event
	 *
	 * @param	Integer		$idEvent
	 * @return	TodoyuCalendarReminderPopup
	 */
	public static function getReminder($idEvent) {
		$idEvent	= intval($idEvent);

		return new TodoyuCalendarReminderPopup($idEvent);
	}



	/**
	 * Check whether popup reminders are activated in profile of current person, fallback: extconf
	 *
	 * @return	Boolean
	 */
	public static function isActivatedForCurrentPerson() {
		if( allowed('calendar', 'reminder:popup') ) {
			if( TodoyuPreferenceManager::isPreferenceSet(EXTID_CALENDAR, 'is_reminderpopupactive', 0, null, 0, personid()) ) {
					// Return pref. from profile
				return TodoyuCalendarPreferences::getPref('is_reminderpopupactive', 0, 0, false, personid()) ? true : false;
			} else {
					// Return pref. from extconf
				return TodoyuSysmanagerExtConfManager::getExtConfValue('calendar', 'is_reminderpopup_active') ? true : false;
			}
		}
			// No
		return false;
	}



	/**
	 * Check whether given/current person can schedule a reminder for the event of the given ID
	 *
	 * @param	Integer		$idEvent
	 * @return	Boolean
	 */
	public static function isEventSchedulable($idEvent, $idPerson = 0) {
		if( ! allowed('calendar', 'reminders:popup') ) {
			return false;
		}

		$idEvent	= intval($idEvent);
		$idPerson	= personid($idPerson);

		return TodoyuCalendarEventManager::getEvent($idEvent)->isPersonAssigned($idPerson);
	}



	/**
	 * Get current person's event reminder popups advance time from current person prefs, fallback: extconf
	 *
	 * @return	Integer
	 */
	public static function getCurrentPersonDefaultAdvanceTime() {
		if( TodoyuPreferenceManager::isPreferenceSet(EXTID_CALENDAR, 'reminderpopup_advancetime', 0, null, 0, personid()) ) {
				// Return pref. from profile
			return intval(TodoyuCalendarPreferences::getPref('reminderpopup_advancetime', 0, 0, false, personid()));
		}

			// Fallback: take preset from extconf
		return intval(TodoyuSysmanagerExtConfManager::getExtConfValue('calendar', 'reminderpopup_advancetime'));
	}



	/**
	 * Get initialization javaScript of reminder to be added into page
	 */
	public static function getReminderJsPageInit() {
		$init	= false;

		if( allowed('calendar', 'reminders:popup') ) {
			$upcomingEvents	= self::getEvents();

			if( sizeof($upcomingEvents) > 0 && ! TodoyuRequest::isAjaxRequest() ) {
				$init	= 'Todoyu.Ext.calendar.Reminder.init.bind(Todoyu.Ext.calendar.Reminder, ' . json_encode($upcomingEvents) . ')';
			}
		}

		return $init;
	}



	/**
	 * Get reminder popup settings of upcoming events of current person
	 *
	 * @return	Array
	 */
	public static function getEvents() {
		$events	= array();

		if( allowed('calendar', 'reminders:popup') ) {
				// Get upcoming events
			$dateStart	= NOW - Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_LOOKBACK'];
			$dateEnd	= NOW + Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_LOOKAHEAD'];
			$personIDs	= array(personid());
			$eventTypes	= Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_REMIND_POPUP'];

			$events	= TodoyuCalendarEventManager::getEventsInTimespan($dateStart, $dateEnd, $personIDs, $eventTypes);

			foreach($events as $idEvent => $eventData) {
				if( false && self::isReminderDismissed($idEvent) ) {
						// Remove dismissed reminders from schedule
					unset($events[$idEvent]);
				} else {
						// Setup event reminder data
					$showTime	= self::getPopupTime($idEvent);
					if( $showTime !== false ) {
						$events[$idEvent]	= array(
							'id'				=> $idEvent,
							'dismissed'			=> 0,
							'time_popup'		=>  $showTime,
							'event.date_start'	=> $eventData['date_start'],
						);
					}
				}
			}
		}

		return $events;
	}



	/**
	 * Get timestamp when to show reminder of given event (initially / again)
	 *
	 * @param	Integer		$idEvent
	 * @return	Integer
	 */
	public static function getPopupTime($idEvent) {
		$showTime	= self::getReminder($idEvent)->getShowTime();

			// Missed reminders of events in the past? show immediately
/** @todo	decide whether to add this as an option to extconf? */
//		if( $showTime !== false && $showTime <= NOW ) {
//			$showTime	= NOW + 30;
//		}

		return $showTime;
	}



	/**
	 * Check whether the given person's reminder of the given event is dismissed already
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function isReminderDismissed($idEvent, $idPerson = 0) {
		$idEvent	= intval($idEvent);
		$idPerson	= intval($idPerson);

		return self::getReminder($idEvent, $idPerson)->isDismissed();
	}



	/**
	 * Get URL of sound to be played with given event's reminder popup
	 *
	 * @param	Integer		$idEvent
	 * @return	String
	 */
	public static function getSoundFilename($idEvent) {
		$idEvent	= intval($idEvent);

		$pathDefaultFile= 'ext/calendar/asset/audio/reminder.wav';
		$pathFile		= TodoyuHookManager::callHookDataModifier('calendar', 'getReminderSoundFilename', $pathDefaultFile, array('event'	=> $idEvent));

		return TodoyuFileManager::pathWeb($pathFile, true);
	}



	/**
	 * Set given reminder dismissed
	 *
	 * @param	Integer		$idEvent
	 */
	public static function setReminderDismissed($idEvent) {
		$idEvent	= intval($idEvent);

		$table	= self::TABLE;
		$where	= '		`id_event`	= ' . $idEvent
				. ' AND	`id_person`	= ' . personid();
		$update	= array(
			'is_reminderdismissed'	=> 1
		);

		return Todoyu::db()->doUpdate($table, $where, $update) === 1;
	}



	/**
	 * Update timestamp when to show given reminder again
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$timeShowAgain
	 */
	public static function rescheduleReminder($idEvent, $timeShowAgain) {
		$idEvent		= intval($idEvent);
		$timeShowAgain	= intval($timeShowAgain);

		$table	= self::TABLE;
		$where	= '		`id_event`	= ' . $idEvent
				. ' AND	`id_person`	= ' . personid();
		$update	= array(
			'date_remindagain'	=> $timeShowAgain
		);

		return Todoyu::db()->doUpdate($table, $where, $update) === 1;
	}



	/**
	 * Check whether the audio reminder is enabled (play sound)
	 *
	 * @return	Boolean
	 */
	public static function isAudioReminderEnabled() {
		return TodoyuSysmanagerExtConfManager::getExtConfValue('calendar', 'audioreminder_active') ? true : false;
	}

}

?>