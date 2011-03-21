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
 * Event Manager
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
	 * Get current person's reminder to given event
	 *
	 * @param	Integer		$idEvent
	 * @return	TodoyuCalendarReminder
	 */
	public static function getReminder($idEvent) {
		$idEvent	= intval($idEvent);

		return new TodoyuCalendarReminder($idEvent);
	}



	/**
	 * Get initialization javaScript of reminder to be added into page
	 */
	public static function getReminderJsPageInit() {
		$upcomingEvents	= self::getEvents();

		if( sizeof($upcomingEvents) > 0 && ! TodoyuRequest::isAjaxRequest() ) {
			$init	= 'Todoyu.Ext.calendar.Reminder.init.bind(Todoyu.Ext.calendar.Reminder, ' . json_encode($upcomingEvents) . ')';
		} else {
			$init	= false;
		}

		return $init;
	}



	/**
	 * Check whether currently logged-in person is configured to see reminders
	 *
	 * @return	Boolean
	 */
	public static function isPersonActivatedForReminders() {
		$personRoles	= TodoyuContactPersonManager::getRoleIDs(personid());
		$reminderRoles	= TodoyuArray::intExplode(',', TodoyuSysmanagerExtConfManager::getExtConfValue('calendar', 'reminderpopup_roles'));

		$personReminderRoles	= array_intersect($personRoles, $reminderRoles);

		return sizeof($personReminderRoles) > 0;
	}



	/**
	 * Get upcoming events to remind current person of
	 *
	 * @return	Array
	 */
	public static function getEvents() {
			// Get upcoming events
		$dateStart	= NOW - Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_LOOKBACK'];
		$dateEnd	= NOW + Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_LOOKAHEAD'];
		$personIDs	= array(personid());
		$eventTypes	= Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_REMIND_POPUP'];

		$events	= TodoyuCalendarEventManager::getEventsInTimespan($dateStart, $dateEnd, $personIDs, $eventTypes);

			// Add event foreign data and generic data of reminder
		foreach($events as $idEvent => $eventData) {
			if( self::isReminderDismissed($idEvent) ) {
					// Remove dismissed reminders from schedule
				unset($events[$idEvent]);
			} else {
					// Setup event reminder data
				$showTime	= self::getTimeUntilShow($idEvent);
				if( $showTime !== false ) {
					$event	= TodoyuCalendarEventManager::getEvent($idEvent);
//					$events[$idEvent]	= $event->getTemplateData(true, true);
					$events[$idEvent]['person_create']			= $event->getPerson('create')->getTemplateData();
					$events[$idEvent]['attendees']				= TodoyuCalendarEventManager::getAssignedPersonsOfEvent($idEvent, true);
					$events[$idEvent]['time_untilshowreminder']	=  $showTime;
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
	public static function getTimeUntilShow($idEvent) {
		$reminder	= self::getReminder($idEvent);

		if( $reminder->isDismissed() ) {
			$showTime	= false;
		} elseif( $reminder->isReschudeled() ) {
				// Get time until scheduled next popup
			$showTime	= $reminder->getDateRemindAgain() - NOW;
		} else {
				//	Calculate time until next popup from starting time of event
			$timeWarnBefore	= intval(TodoyuSysmanagerExtConfManager::getExtConfValue('calendar', 'reminderpopup_advancetime'));
			$event			= TodoyuCalendarEventManager::getEvent($idEvent);
			$dateStart		= $event->getStartDate();

			$showTime	= $dateStart - NOW - $timeWarnBefore;
		}

			// Missed reminders of events in the past? show immediately
		if( $showTime !== false && $showTime <= NOW ) {
			$showTime	= NOW + 30;
		}

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