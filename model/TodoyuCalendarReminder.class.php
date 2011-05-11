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
class TodoyuCalendarReminder extends TodoyuBaseObject {

	/**
	 * Initialize reminder (based on event's person assignment)
	 *
	 * @param	Integer		$idReminder
	 */
	public function __construct($idReminder) {
		parent::__construct($idReminder, 'ext_calendar_mm_event_person');
	}



	/**
	 * Get ID of reminder (is ID of event_person MM record) to given event of given/person
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	private static function getReminderIDbyAssignment($idEvent, $idPerson = 0) {
		return TodoyuCalendarReminderManager::getReminderIDbyAssignment($idEvent, $idPerson);
	}



	/**
	 * Get ID of person to be reminded (= person assigned to event of reminder)
	 *
	 * @return	Integer
	 */
	public function getPersonAssignedID() {
		return $this->data['id_person'];
	}



	/**
	 * Get (object of) person to be reminded (=person assigned to event of reminder)
	 *
	 * @return	TodoyuContactPerson
	 */
	public function getPersonAssigned() {
		return TodoyuContactPersonManager::getPerson($this->getPersonAssignedID());
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
	 * Get starting time of event of reminder
	 *
	 * @return	Integer
	 */
	public function getEventStartDate() {
		return $this->getEvent()->getStartDate();
	}



	/**
	 * Get scheduled reminder time
	 *
	 * @param	Integer		$reminderType
	 * @return	Integer
	 */
	public function getDateRemind($reminderType = CALENDAR_TYPE_EVENTREMINDER_EMAIL) {
		$typePrefix	= TodoyuCalendarReminderManager::getReminderTypePrefix($reminderType);

		return intval($this->get('date_remind' . $typePrefix));
	}


	public function getDateRemindEmail() {
		return $this->getDateRemind(CALENDAR_TYPE_EVENTREMINDER_EMAIL);
	}

	public function getDateRemindPopup() {
		return $this->getDateRemind(CALENDAR_TYPE_EVENTREMINDER_POPUP);
	}



	/**
	 * Get amount of time before event when given reminder type is scheduled
	 *
	 * @return	Integer|Boolean
	 */
	public function getAdvanceTime($reminderType = CALENDAR_TYPE_EVENTREMINDER_EMAIL) {
		$dateRemind	= $this->getDateRemind($reminderType);

		if( $dateRemind > 0 ) {
			return $this->getEventStartDate() - $dateRemind;
		} else {
			return false;
		}
	}



	/**
	 * Is event in past already?
	 *
	 * @return	String
	 */
	public function isEventPassed() {
		return $this->getEventStartDate() < NOW;
	}



	/**
	 * Check whether email reminding for this event/person is disabled
	 *
	 * @return	Boolean
	 */
	protected function isDisabled($reminderType) {
		$typePrefix = TodoyuCalendarReminderManager::getReminderTypePrefix($reminderType);

		return $this->get('date_remind' . $typePrefix) === 0;
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