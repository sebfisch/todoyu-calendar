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
 * Event
 *
 * @package		Todoyu
 * @subpackage	Calendar
 *
 */
class TodoyuReminder extends TodoyuBaseObject {

	/**
	 * Initialize reminder (based on event's person assignment)
	 *
	 * @param	Integer		$idReminder
	 */
	public function __construct($idEvent) {
		$idEvent	= intval($idEvent);
		$idReminder	= self::getReminderID($idEvent);

		parent::__construct($idReminder, 'ext_calendar_mm_event_person');
	}



	/**
	 * Get ID of reminder to given event of given/current person
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	private static function getReminderID($idEvent, $idPerson = 0) {
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
		return ( $this->getDateRemindAgain() > 0 );
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
		$idEvent= $this->data['id_event'];
		$event	= TodoyuEventManager::getEvent($idEvent);

		return $event->isDayevent();
	}



	/**
	 * Check whether event of reminder is private
	 *
	 * @return	Boolean
	 */
	public function isPrivate() {
		$idEvent= $this->data['id_event'];
		$event	= TodoyuEventManager::getEvent($idEvent);

		return $event->isPrivate();
	}

}

?>