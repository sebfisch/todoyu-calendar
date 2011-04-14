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
class TodoyuCalendarReminderEmail extends TodoyuCalendarReminder {

	/**
	 * Initialize reminder (based on event's person assignment)
	 *
	 * @param	Integer		$idReminder
	 */
	public function __construct($idEvent, $idPerson = 0) {
		parent::__construct($idEvent, $idPerson);
	}



	/**
	 * Get scheduled email reminder time
	 *
	 * @return	Integer
	 */
	public function getDateRemindEmail() {
		return intval($this->get('date_remindemail'));
	}



	/**
	 * Get amount of time before event when to send reminder email
	 *
	 * @return	Boolean|Integer
	 */
	public function getAdvanceTime() {
		return parent::getAdvanceTime(REMINDERTYPE_EMAIL);
	}



	/**
	 * Check whether email reminding for this event/person is disabled
	 *
	 * @return	String
	 */
	public function isDisabled() {
		return parent::isDisabled(REMINDERTYPE_EMAIL);
	}

}

?>