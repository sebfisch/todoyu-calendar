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
		$idPerson	= $reminder->getPersonAssignedID();
		$person		= $reminder->getPersonAssigned();
		$email		= $person->getEmail();

			// Don't (try) sending when event or person's email is missing 
		if( $event->isDeleted() || empty($email) ) {
			return false;
		}

			// Get mailer config
		$mailer	= TodoyuMailManager::getPHPMailerLite(true);

			// Set "from" name and email address from system config
		$mailer->SetFrom(Todoyu::$CONFIG['SYSTEM']['name'], Todoyu::$CONFIG['SYSTEM']['email']);

			// Set "subject"
		$mailer->Subject	= Label('calendar.reminder.email.subject') . ': ' . $event->getTitle();

			// Add message body as HTML and plain text
		$htmlBody	= self::getMailContent($idReminder, $idPerson, false, true);
		$textBody	= self::getMailContent($idReminder, $idPerson, false, false);

		$mailer->MsgHTML($htmlBody, PATH_EXT_COMMENT);
		$mailer->AltBody	= $textBody;

			// Add "to" address (recipient)
		$mailer->AddAddress($email, $person->getFullName());

		try {
			$sendStatus	= $mailer->Send();
		} catch(phpmailerException $e) {
			Todoyu::log($e->getMessage(), TodoyuLogger::LEVEL_ERROR);
		} catch(Exception $e) {
			Todoyu::log($e->getMessage(), TodoyuLogger::LEVEL_ERROR);
		}

		return $sendStatus;
	}


	/**
	 * Render content for HTML mail
	 *
	 * @param	Integer		$idEvent		Event to send
	 * @param	Integer		$idPerson		Person to send the email to
	 * @param	Boolean		$hideEmails
	 * @param	Boolean		$modeHTML
	 * @return	String
	 */
	private static function getMailContent($idEvent, $idPerson, $hideEmails = true, $modeHTML = true) {
		$idEvent	= intval($idEvent);
		$idPerson	= intval($idPerson);

		$tmpl				= self::getMailTemplateName($modeHTML);
		$data				= TodoyuCalendarEventMailManager::getMailData($idEvent, $idPerson);
		$data['hideEmails']	= $hideEmails;

		return render($tmpl, $data);
	}



	/**
	 * Get filename of event reminder email template to current mode (text/HTML)
	 *
	 * @param	Boolean		$modeHTML
	 * @return	String
	 */
	public static function getMailTemplateName($modeHTML = false) {
		$path	= 'ext/calendar/view/emails/event-reminder';

		return $path . ( $modeHTML ? '-html' : '-text' ) . '.tmpl';
	}
}

?>