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
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 */
	public function __construct($idEvent, $idPerson = 0) {
		$idEvent	= intval($idEvent);
		$idPerson	= personid($idPerson);

		if( ! TodoyuCalendarEventManager::getEvent($idEvent)->isPersonAssigned($idPerson) ) {
				// Given person not assigned? Prevent reminder construction
			Todoyu::log('Instantiating reminder failed because person ' . $idPerson . ' is not assigned to event ' . $idEvent, TodoyuLogger::LEVEL_ERROR);
			return false;
		}

		$idReminder	= self::getReminderIDbyAssignment($idEvent, $idPerson);

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
	 * Get scheduled email reminder time
	 *
	 * @param	Integer		$reminderType
	 * @return	Integer
	 */
	public function getDateRemind($reminderType = REMINDERTYPE_EMAIL) {
		$typePrefix	= TodoyuCalendarReminderManager::getReminderTypePrefix($reminderType);

		return intval($this->get('date_remind' . $typePrefix));
	}



	/**
	 * Get amount of time before event when given reminder type is scheduled
	 *
	 * @return	Boolean|Integer
	 */
	public function getAdvanceTime($reminderType = REMINDERTYPE_EMAIL) {
		$dateRemind	= $this->getDateRemind($reminderType);

		return $dateRemind > 0 ? ($this->getEventStartDate() - $dateRemind) : false;
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
	 * @return	String
	 */
	public function isDisabled($reminderType = REMINDERTYPE_EMAIL) {
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