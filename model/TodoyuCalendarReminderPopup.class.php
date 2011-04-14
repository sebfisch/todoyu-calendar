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
//class TodoyuCalendarReminderPopup extends TodoyuBaseObject {
class TodoyuCalendarReminderPopup extends TodoyuCalendarReminder {

	/**
	 * Initialize reminder (based on event's person assignment)
	 *
	 * @param	Integer		$idReminder
	 */
	public function __construct($idEvent, $idPerson = 0) {
		parent::__construct($idEvent, $idPerson);
	}



	/**
	 * Get timestamp when to the reminder popup
	 *
	 * @return	Integer|Boolean
	 */
	public function getTimePopup() {
		if( $this->isEventPassed() || $this->isDismissed() ) {
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
	 * Get amount of time before event when to send reminder email
	 *
	 * @return	Boolean|Integer
	 */
	public function getAdvanceTime() {
		return parent::getAdvanceTime(REMINDERTYPE_POPUP);
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
	 * Get dismission state
	 *
	 * @return	String
	 */
	public function isDismissed() {
		return $this->get('is_reminderdismissed') ? true : false;
	}

}

?>