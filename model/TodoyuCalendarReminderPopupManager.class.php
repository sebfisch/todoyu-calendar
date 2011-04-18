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
	 * @var	String		Type of reminder
	 */
	const REMINDERTYPE = CALENDAR_TYPE_EVENTREMINDER_POPUP;



	/**
	 * Get person's reminder to given event
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return	TodoyuCalendarReminderPopup
	 */
	public static function getReminderByAssignment($idEvent, $idPerson = 0) {
		$idEvent	= intval($idEvent);
		$idPerson	= intval($idPerson);

		return new TodoyuCalendarReminderPopup($idEvent, $idPerson);
	}



	/**
	 * Update popup reminder scheduling of given event from given form data
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
	 * Update scheduled popup reminder display time of given event/person
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$timePopup
	 * @param	Integer		$idPerson
	 */
	public static function updateReminderTime($idEvent, $timePopup, $idPerson = 0) {
		TodoyuCalendarReminderManager::updateReminderTime(self::REMINDERTYPE, $idEvent, $timePopup, $idPerson);
	}



	/**
	 * Get timestamp for popup reminder of newly assigned event (advance-time from profile, fallback: extconf)
	 *
	 * @param	Integer		$dateStart
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public static function getNewEventPopupTime($dateStart, $idPerson = 0) {
		$dateStart	= intval($dateStart);
		$idPerson	= personid($idPerson);

		return $dateStart  - self::getDefaultAdvanceTime($idPerson);
	}



	/**
	 * Check whether popup reminders are activated in profile of current person, fallback: extconf
	 *
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function isActivatedForPerson($idPerson = 0) {
		return TodoyuCalendarReminderManager::isRemindertypeActivated(self::REMINDERTYPE, $idPerson);
	}



	/**
	 * Get amount of time before event when to show reminder popup
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
	 * Check whether given/current person can schedule a reminder for the event of the given ID
	 *
	 * @param	Integer		$idEvent
	 * @return	Boolean
	 */
	public static function isEventSchedulable($idEvent, $idPerson = 0) {
		if( ! allowed('calendar', 'reminders:popup') ) {
			return false;
		}

		return TodoyuCalendarReminderManager::isEventSchedulable(self::REMINDERTYPE, $idEvent, $idPerson);
	}



	/**
	 * Get person's event reminder popups advance time from current person prefs, fallback: extconf
	 *
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public static function getDefaultAdvanceTime($idPerson = 0) {
		return TodoyuCalendarReminderManager::getDefaultAdvanceTime(self::REMINDERTYPE, $idPerson);
	}



	/**
	 * @static
	 * @param	Integer		$idEvent
	 * @return	String
	 */
	public static function getSelectedAdvanceTimeContextMenuOptionKey($idEvent) {
		$idEvent	= intval($idEvent);

		$scheduledTime	= self::getDateRemind($idEvent);
		if( $scheduledTime == 0 ) {
			return false;
		}

		return self::getAdvanceTime($idEvent);
	}



	/**
	 * Get initialization javaScript of reminder to be added into page
	 */
	public static function getReminderJsPageInit() {
		$init	= false;

		if( allowed('calendar', 'reminders:popup') ) {
			$upcomingEvents	= self::getUpcomingReminderEvents();

			if( sizeof($upcomingEvents) > 0 && ! TodoyuRequest::isAjaxRequest() ) {
				$init	= 'Todoyu.Ext.calendar.ReminderPopup.init.bind(Todoyu.Ext.calendar.ReminderPopup, ' . json_encode($upcomingEvents) . ')';
			}
		}

		return $init;
	}



	/**
	 * Get reminder popup settings of upcoming events of current person
	 *
	 * @return	Array
	 */
	public static function getUpcomingReminderEvents() {
		$events	= array();

		if( allowed('calendar', 'reminders:popup') ) {
				// Get upcoming events
			$dateStart	= NOW - Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_LOOKBACK'];
			$dateEnd	= NOW + Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_LOOKAHEAD'];
			$personIDs	= array(personid());
			$eventTypes	= Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_REMIND_POPUP'];

			$events	= TodoyuCalendarEventManager::getEventsInTimespan($dateStart, $dateEnd, $personIDs, $eventTypes);

			foreach($events as $idEvent => $eventData) {
					// Setup event reminder data / remove dismissed reminders from schedule
				if( self::isDismissed($idEvent) ) {
					unset($events[$idEvent]);
				} else {
					$events[$idEvent]	= array(
						'id'				=> $idEvent,
						'dismissed'			=> 0,
						'time_popup'		=> self::getDateRemind($idEvent),
						'event.date_start'	=> $eventData['date_start'],
					);
				}
			}
		}

		return $events;
	}



	/**
	 * Get timestamp when to show reminder of given event (initially / again)
	 *
	 * @param	Integer		$idEvent
	 * @return	Integer					UNIX timestamp when to display the reminder popup
	 */
	public static function getDateRemind($idEvent) {
		$reminder	= self::getReminderByAssignment($idEvent);

		return $reminder->getDateRemind();
	}



	/**
	 * Check whether the given person's reminder of the given event is dismissed already
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function isDismissed($idEvent, $idPerson = 0) {
		$idEvent	= intval($idEvent);
		$idPerson	= intval($idPerson);

		return self::getReminderByAssignment($idEvent, $idPerson)->isDismissed();
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
			'is_remindpopupdismissed'	=> 1
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
			'is_remindpopupdismissed'	=> 0,
			'date_remindpopup'			=> $timeShowAgain
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



	/**
	 * Get reminder context menu options (hilite selected, deactivate past options)
	 *
	 * @param	Integer	$idEvent
	 * @param	Array	$options
	 * @return	Array
	 */
	public static function getContextMenuItems($idEvent) {
		$idEvent	= intval($idEvent);
		$options	= Todoyu::$CONFIG['EXT']['calendar']['ContextMenu']['Event']['reminderpopup'];

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