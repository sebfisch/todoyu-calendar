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
 * Event Reminder
 *
 * @package		Todoyu
 * @subpackage	Calendar
 *
 */
class TodoyuCalendarReminderPopup extends TodoyuBaseObject {

	/**
	 * Initialize reminder (based on event's person assignment)
	 *
	 * @param	Integer		$idReminder
	 */
	public function __construct($idEvent, $idPerson = 0) {
		$idEvent	= intval($idEvent);
		$idReminder	= self::getIDreminder($idEvent);

		parent::__construct($idReminder, 'ext_calendar_mm_event_person');
	}



	/**
	 * Get ID of event of reminder
	 *
	 * @return	Integer
	 */
	public function getEventID() {
		return intval($this->data['id_event']);
	}



	/**
	 * Get event of reminder
	 *
	 * @return TodoyuCalendarEvent
	 */
	public function getEvent() {
		return TodoyuCalendarEventManager::getEvent($this->getEventID());
	}



	/**
	 * Get ID of reminder (is ID of event_person MM record) to given event of given/current person
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	private static function getIDreminder($idEvent, $idPerson = 0) {
		$idEvent	= intval($idEvent);
		$idPerson	= personid($idPerson);

		$field	= 'id';
		$table	= 'ext_calendar_mm_event_person';

		$where	= '		id_event 	= ' . $idEvent
				.=' AND	id_person	= ' . $idPerson;

		$limit	= '1';

		$row	= Todoyu::db()->getColumn($field, $table, $where, '', '', $limit, 'id');
		return $row[0];
	}



	/**
	 * Get starting time of event of reminder
	 *
	 * @return	Integer
	 */
	public function getEventStartDate() {
		return $this->getEvent()->getStartDate();
	}



	/**
	 * Get timestamp when to the reminder popup
	 *
	 * @return	Integer|Boolean
	 */
	public function getShowTime() {
		if( $this->isPassed() || $this->isDismissed() ) {
			return false;
		}

		if( $this->isReschudeled() ) {
				// Get time until scheduled next popup time
			$showTime	= $this->getDateRemindAgain();
		} else {
				//	Calculate time until next popup from starting time of event
			$timeWarnBefore	= intval(TodoyuSysmanagerExtConfManager::getExtConfValue('calendar', 'reminderpopup_advancetime'));

			$eventStartTime		= $this->getEventStartDate();
			$showTime		= $eventStartTime - $timeWarnBefore;
		}

		return $showTime;
	}



	/**
	 * Get scheduled next reminding time
	 *
	 * @return	String
	 */
	public function getDateRemindAgain() {
		return $this->get('date_remindagain');
	}



	/**
	 * Check whether the reminder has been re-scheduled to be shown at a later time
	 *
	 * @return	Boolean
	 */
	public function isReschudeled() {
		return $this->getDateRemindAgain() > 0;
	}



	/**
	 * Is event in past already?
	 *
	 * @return	String
	 */
	public function isPassed() {
		return $this->getEventStartDate() < NOW;
	}



	/**
	 * Get dismission state
	 *
	 * @return	String
	 */
	public function isDismissed() {
		return $this->get('is_reminderdismissed') ? true : false;
	}



	/**
	 * Check whether event of reminder is a full-day event
	 *
	 * @return	Boolean
	 */
	public function isDayevent() {
		return $this->getEvent()->isDayevent();
	}



	/**
	 * Check whether event of reminder is private
	 *
	 * @return	Boolean
	 */
	public function isPrivate() {
		return $this->getEvent()->isPrivate();
	}

}

?>