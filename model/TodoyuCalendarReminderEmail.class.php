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
		return parent::getAdvanceTime(CALENDAR_TYPE_EVENTREMINDER_EMAIL);
	}



	/**
	 * Check whether email reminding for this event/person is disabled
	 *
	 * @return	Boolean
	 */
	public function isDisabled() {
		return parent::isDisabled(CALENDAR_TYPE_EVENTREMINDER_EMAIL);
	}



	/**
	 * Send reminder as email
	 *
	 * @return	Boolean
	 */
	public function sendAsEmail() {
		$event		= $this->getEvent();
		$person		= $this->getPersonAssigned();

			// Don't send when event or person's email is missing
		if( $event->isDeleted() || ! $person->hasEmail() ) {
			return false;
		}

			// Setup mail data
		$subject	= Label('calendar.reminder.email.subject') . ': ' . $event->getTitle();
		$htmlBody	= $this->getMailContent(false, true);
		$textBody	= $this->getMailContent(false, false);

		$mail		= new TodoyuCalendarReminderMail();
		$mail->setSubject($subject);
		$mail->addReceiver($person->getID());
		$mail->setHtmlContent($htmlBody);
		$mail->setTextContent($textBody);

		$sendStatus	= $mail->Send();
//		echo "SEND EMAIL TO: " . $person->getEmail() . "\n";

		if( $sendStatus ) {
			$this->saveAsSent();
		}

		return $sendStatus;
	}



	/**
	 * Set "is_sent" flag of reminder true, store
	 *
	 */
	public function saveAsSent() {
		$idReminder	= $this->getID();
		$idPerson	= $this->getPersonAssignedID();

			// Set "is_sent"-flag in ext_calendar_mm_event_person
		TodoyuCalendarReminderManager::updateMMrecord($idReminder, array(
			'is_remindemailsent'	=> 1
		));

			// Save log record about sent mail
		TodoyuMailManager::saveMailsSent(EXTID_CALENDAR, CALENDAR_TYPE_EVENTREMINDER_EMAIL, $idReminder, array($idPerson));
	}



	/**
	 * Render content for HTML/plaintext mail
	 *
	 * @param	Integer		$idEvent		Event to send
	 * @param	Integer		$idPersonMailTo
	 * @param	Boolean		$hideEmails
	 * @param	Boolean		$asHTML
	 * @return	String
	 */
	private function getMailContent($hideEmails = true, $asHTML = true) {
		$data				= TodoyuCalendarEventMailManager::getMailData($this->getEventID(), $this->getPersonAssignedID(), true);
		$data['hideEmails']	= $hideEmails;

			// Switch to locale of email receiver person
		$locale	= $this->getPersonAssigned()->getLocale();
		TodoyuLabelManager::setLocale($locale);

		return $this->renderEmail($data, $asHTML);
	}



	/**
	 * Render email with template
	 *
	 * @param	Array		$data
	 * @param	Boolean		$asHTML
	 * @return	String
	 */
	private function renderEmail(array $data, $asHTML = true) {
		$tmpl	= $this->getTemplate($asHTML);

		return render($tmpl, $data);
	}



	/**
	 * Get filename of event reminder email template to current mode (text/HTML)
	 *
	 * @param	Boolean		$asHTML
	 * @return	String
	 */
	private function getTemplate($asHTML = true) {
		$type	= $asHTML ? 'html' : 'text';

		 return 'ext/calendar/view/emails/event-reminder-' . $type . '.tmpl';
	}

}

?>