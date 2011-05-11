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
	 * Get reminder
	 *
	 * @param	Integer		$idReminder
	 * @return	TodoyuCalendarReminderPopup
	 */
	public static function getReminder($idReminder) {
		$idReminder	= intval($idReminder);

		return TodoyuRecordManager::getRecord('TodoyuCalendarReminderPopup', $idReminder);
	}



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
		$idReminder	= TodoyuCalendarReminderManager::getReminderIDByAssignment($idEvent, $idPerson);

		return self::getReminder($idReminder);
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
		$idPerson	= Todoyu::personid($idPerson);

		return $dateStart  - self::getDefaultAdvanceTime($idPerson);
	}



	/**
	 * Check whether popup reminders are activated in profile of current person, fallback: extconf
	 *
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function isActivatedForPerson($idPerson = 0) {
		return TodoyuCalendarReminderDefaultManager::isPopupDefaultActivationEnabled();
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
		$idPerson	= Todoyu::personid($idPerson);
		$reminder	= self::getReminderByAssignment($idEvent, $idPerson);

		if( $idEvent === 0 ) {
			return TodoyuCalendarReminderDefaultManager::getPopupDefaultAdvanceTime($idPerson);
		}

		return $reminder->getAdvanceTime();
	}



	/**
	 * Check whether given/current person can schedule a reminder for the event of the given ID
	 *
	 * @param	Integer		$idEvent
	 * @return	Boolean
	 */
	public static function isReminderAllowed($idEvent, $idPerson = 0) {
		if( ! Todoyu::allowed('calendar', 'reminders:popup') ) {
			return false;
		}

		if( ! TodoyuCalendarReminderManager::isPopupReminderEnabled() ) {
			return false;
		}

		if( $idEvent === 0 ) {
			return true;
		}

		return TodoyuCalendarReminderManager::isPersonAssigned($idEvent, $idPerson);
	}



	/**
	 * Get person's event reminder popups advance time from current person prefs, fallback: extconf
	 *
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public static function getDefaultAdvanceTime($idPerson = 0) {
		return TodoyuCalendarReminderDefaultManager::getDefaultAdvanceTime(self::REMINDERTYPE, $idPerson);
	}



	/**
	 * @param	Integer		$idEvent
	 * @return	Integer					Amount of seconds
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
	 * Add reminder JS init to page
	 */
	public static function addReminderJsInitToPage() {
		$upcomingEvents	= self::getUpcomingReminderEvents();
		$json			= json_encode($upcomingEvents);
		$jsInitCode		= 'Todoyu.Ext.calendar.ReminderPopup.init.bind(Todoyu.Ext.calendar.ReminderPopup, ' . $json . ')';

		TodoyuPage::addJsOnloadedFunction($jsInitCode, 200);
	}



	/**
	 * Get reminder popup settings of upcoming events of current person
	 *
	 * @return	Array
	 */
	public static function getUpcomingReminderEvents() {
			// Get upcoming events
		$dateStart	= NOW - Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_LOOKBACK'];
		$dateEnd	= NOW + Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_LOOKAHEAD'];
		$personIDs	= array(Todoyu::personid());
		$eventTypes	= Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_REMIND_POPUP'];
		$reminders	= array();

		$events	= TodoyuCalendarEventManager::getEventsInTimespan($dateStart, $dateEnd, $personIDs, $eventTypes);

		foreach($events as $idEvent => $eventData) {
				// Setup event reminder data / remove dismissed reminders from schedule
			if( ! self::isDismissed($idEvent) ) {
				$reminders[] = array(
					'id'			=> $idEvent,
					'dismissed'		=> 0,
					'time_popup'	=> self::getDateRemind($idEvent),
					'date_start'	=> $eventData['date_start']
				);
			}
		}

		return $reminders;
	}



	/**
	 * Get timestamp when to show reminder of given event (initially / again)
	 *
	 * @param	Integer		$idEvent
	 * @return	Integer					UNIX timestamp when to display the reminder popup
	 */
	public static function getDateRemind($idEvent) {
		$reminder	= self::getReminderByAssignment($idEvent, Todoyu::personid());

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
				. ' AND	`id_person`	= ' . Todoyu::personid();
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
				. ' AND	`id_person`	= ' . Todoyu::personid();
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
	 * Get context menu items for popup reminders
	 *
	 * @param	String		$idEvent
	 * @param array $items
	 * @return array
	 */
	public static function getContextMenuItems($idEvent, array $items) {
		$idEvent	= intval($idEvent);
		$event		= TodoyuCalendarEventManager::getEvent($idEvent);
		$allowed	= array();

			// Option: popup reminder
		if( $event->getStartDate() > NOW && self::isReminderAllowed($idEvent) ) {
			$options	= Todoyu::$CONFIG['EXT']['calendar']['ContextMenu']['reminderpopup'];

				// Set selected option CSS class
			$selectedTimeOptionKey	= self::getSelectedAdvanceTimeContextMenuOptionKey($idEvent);
			if( $selectedTimeOptionKey === false ) {
				$options['submenu'][0]['class'] .= ' selected';
			} elseif( key_exists($selectedTimeOptionKey, $options['submenu']) ) {
				$options['submenu'][$selectedTimeOptionKey]['class'] .= ' selected';
			}
				// Set options disabled which are in the past already
			$options['submenu']	= TodoyuCalendarReminderManager::disablePastTimeKeyOptions($options['submenu'], $idEvent);

			$allowed['reminderpopup'] = $options;
		}

		return array_merge_recursive($items, $allowed);
	}

}

?>