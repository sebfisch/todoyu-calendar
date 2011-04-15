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
 * Event email reminder cronjob
 *
 * @package		Todoyu
 * @subpackage	Calendar
 *
 */
class TodoyuCalendarJobReminderEmail extends TodoyuSchedulerJob {

	/**
	 * Executed from TodoyuScheduler: send scheduled event reminder emails
	 */
	public function execute() {
			// Get unsent emails with scheduled timestamp <= NOW
		$reminderIDs	= $this->getUnsentDueReminderIDs();

			// Send emails
		foreach($reminderIDs as $idReminder) {
			$reminder	= TodoyuCalendarReminderManager::getReminder($idReminder);
			$idPerson	= $reminder->getPersonAssignedID();

				// Only send to persons having the right to use email reminders
			if( TodoyuCalendarReminderEmailManager::isActivatedForPerson($idPerson) ) {
				self::sendMail($idReminder);
			}
		}
	}



	/**
	 * Get records of unsent reminders (from ext_calendar_mm_event_person) which are due 
	 *
	 * @return	Array
	 */
	private static function getUnsentDueReminderIDs() {
		$field	= 'id';
		$table	= 'ext_calendar_mm_event_person';
		$where	= '		is_remindemailsent	= 0'
				. ' AND	date_remindemail	> 0'
				. ' AND	date_remindemail	<= ' . NOW;

		return Todoyu::db()->getColumn($field, $table, $where, $field, $field);
	}



	/**
	 * Send an event email to a person
	 *
	 * @param	Integer		$idReminder
	 * @return	Boolean		Success
	 */
	private static function sendMail($idReminder) {
		$idReminder	= intval($idReminder);

		$reminder	= TodoyuCalendarReminderManager::getReminder($idReminder);
		$event		= $reminder->getEvent();
		$person		= $reminder->getPersonAssigned();
		$email		= $person->getEmail();

			// Don't (try) sending when event or person's email is missing
		if( $event->isDeleted() || empty($email) ) {
			return false;
		}

		$idEvent	= $reminder->getEventID();
		$idPerson	= $reminder->getPersonAssignedID();

			// Setup mail data
		$mailSubject	= Label('calendar.reminder.email.subject') . ': ' . $event->getTitle();
		$fromAddress	= Todoyu::$CONFIG['SYSTEM']['email'];
		$fromName		= Todoyu::$CONFIG['SYSTEM']['name'];
		$toAddress		= $email;
		$toName			= $person->getFullName();
		$htmlBody		= self::getMailContent($idEvent, $idPerson, false, true);
		$textBody		= self::getMailContent($idEvent, $idPerson, false, false);

		$baseURL	= PATH_EXT_CALENDAR;

			// Send mail
		$sendStatus	= TodoyuMailManager::sendMail($mailSubject, $fromAddress, $fromName, $toAddress, $toName, $htmlBody, $textBody, $baseURL, true);

		return $sendStatus;
	}



	/**
	 * Render content for HTML/plaintext mail
	 *
	 * @param	Integer		$idEvent		Event to send
	 * @param	Integer		$idPersonMailTo
	 * @param	Boolean		$hideEmails
	 * @param	Boolean		$modeHTML
	 * @return	String
	 */
	private static function getMailContent($idEvent, $idPersonMailTo, $hideEmails = true, $modeHTML = true) {
		$idEvent	= intval($idEvent);
		$idPersonMailTo	= intval($idPersonMailTo);

		$tmpl				= self::getMailTemplateName($modeHTML);
		$data				= TodoyuCalendarEventMailManager::getMailData($idEvent, $idPersonMailTo, true);
		$data['hideEmails']	= $hideEmails;

			// Switch to locale of email receiver person
		$locale	= TodoyuContactPersonManager::getPerson($idPersonMailTo)->getLocale();
		TodoyuLabelManager::setLocale($locale);

			// Render
		$content	= render($tmpl, $data);

		return $content;
	}



	/**
	 * Get filename of event reminder email template to current mode (text/HTML)
	 *
	 * @param	Boolean		$modeHTML
	 * @return	String
	 */
	public static function getMailTemplateName($modeHTML = false) {
		$path	= 'ext/calendar/view/emails/event-reminder';
		$path  .= $modeHTML ? '-html' : '-text';

		return $path . '.tmpl';
	}

}

?>