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

	// Include mailer library
require_once( PATH_LIB . '/php/phpmailer/class.phpmailer-lite.php' );

/**
 * Send event mails
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarEventMailer {

	/**
	 * Send event information email to the persons
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$personIDs
	 * @param	Integer		$operationID	was has been done- create, update, delete?
	 */
	public static function sendEmails($idEvent, array $personIDs, $operationID) {
		$idEvent	= intval($idEvent);
		$personIDs	= TodoyuArray::intval($personIDs, true, true);

		$succeeded	= true;
		foreach($personIDs as $idPerson) {
			$result = self::sendMail($idEvent, $idPerson, false, true, $operationID);

			if( $result === false ) {
				$succeeded	= false;
			}
		}

		return $succeeded;
	}



	/**
	 * Send an event email to a person
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @param	Boolean		$setSenderFromPersonMail
	 * @param	Boolean		$hideEmails					Show event authors email addresses in message?
 	 * @param	Integer		$operationID				What's done- create, update, delete?
	 * @return	Boolean		Success
	 */
	public static function sendMail($idEvent, $idPerson, $setSenderFromPersonMail = false, $hideEmails = true, $operationID) {
		$idEvent	= intval($idEvent);
		$idPerson	= intval($idPerson);
		$operationID= intval($operationID);

		$event	= TodoyuCalendarEventManager::getEvent($idEvent);

		if( $event->isDeleted() ) {
			$operationID	= OPERATIONTYPE_RECORD_DELETE;
		}

//		$personWrite	= $event->getCreatePerson();
		$person			= TodoyuContactPersonManager::getPerson($idPerson);
		$eventTitle		= $event->getTitle();

			// Get mailer
		$mailer	= TodoyuMailManager::getPHPMailerLite(true);

			// Set "from" (sender) name and email address
		$fromName		= Todoyu::person()->getFullName() . ' (todoyu)';
		$fromAddress	= $setSenderFromPersonMail ? Todoyu::person()->getEmail() : Todoyu::$CONFIG['SYSTEM']['email'];
		$mailer->SetFrom($fromAddress, $fromName);

			// Set "replyTo", "subject"
		$mailer->AddReplyTo(Todoyu::person()->getEmail(), Todoyu::person()->getFullName());
		$subject		= self::getSubjectLabelByOperation($operationID) . ': ' . $eventTitle;
		$mailer->Subject	= $subject;

			// Add message body as HTML and plain text
		$htmlBody	= self::getMailContent($idEvent, $idPerson, $hideEmails, true, $operationID);
		$textBody	= self::getMailContent($idEvent, $idPerson, $hideEmails, false, $operationID);

		$mailer->MsgHTML($htmlBody, PATH_EXT_COMMENT);
		$mailer->AltBody	= $textBody;

			// Add "to" address (recipient)
		$mailer->AddAddress($person->getEmail(), $person->getFullName());

//	@todo	verify
//		if( DIR_SEP !== '\\' ) {
//				// Non-Windows (e.g Linux)
//			$mail->AddAddress($person->getEmail(), $person->getFullName());
//		} else {
//				// Windows
//			$mail->AddAddress($person->getEmail(), '');
//		}

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
	 * Get email subject label of given operation on event (create, update, delete)
	 *
	 * @param	Integer		$operationID
	 * @return	String
	 */
	public static function getSubjectLabelByOperation($operationID) {
		switch( $operationID ) {
			case OPERATIONTYPE_RECORD_CREATE:
				$subject	= Label('calendar.event.mail.title.create');
				break;
			case OPERATIONTYPE_RECORD_UPDATE:
				$subject	= Label('calendar.event.mail.title.update');
				break;
			case OPERATIONTYPE_RECORD_DELETE: default:
				$subject	= Label('calendar.event.mail.title.deleted');
				break;
		}

		return $subject;
	}



	/**
	 * Get data array to render email
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return	Array
	 */
	private static function getMailData($idEvent, $idPerson) {
		return TodoyuCalendarEventMailManager::getMailData($idEvent, $idPerson);
	}



	/**
	 * Get filename of email template to current mode (text/HTML) and event operation
	 *
	 * @param	Integer		$operationID
	 * @param	Boolean		$modeHTML
	 * @return	String|Boolean
	 */
	public static function getMailTemplateName($operationID, $modeHTML = false) {
		$path	= 'ext/calendar/view/emails/';

		switch($operationID) {
			case OPERATIONTYPE_RECORD_CREATE:
				$tmpl	= $path . 'event-new';
				break;
			case OPERATIONTYPE_RECORD_DELETE:
				$tmpl	= $path . 'event-deleted';
				break;
			case OPERATIONTYPE_RECORD_UPDATE:
				$tmpl	= $path . 'event-update';
				break;
			default:
				Todoyu::log('Mail template missing because of wrong operation ID: ' . $operationID, TodoyuLogger::LEVEL_ERROR);
				$tmpl	= false;
				break;
		}

		return $tmpl . ( $modeHTML ? '-html' : '-text' ) . '.tmpl';
	}



	/**
	 * Render content for HTML mail
	 *
	 * @param	Integer		$idEvent		Event to send
	 * @param	Integer		$idPerson		Person to send the email to
	 * @param	Boolean		$hideEmails
	 * @param	Integer		$operationID	what's been done? (create, update, delete)
	 * @return	String|Boolean
	 */
	private static function getMailContent($idEvent, $idPerson, $hideEmails = true, $modeHTML = true, $operationID) {
		$idEvent	= intval($idEvent);
		$idPerson	= intval($idPerson);

		$tmpl	= self::getMailTemplateName($operationID, $modeHTML);

		if($tmpl !== false) {
			$data				= self::getMailData($idEvent, $idPerson);
			$data['hideEmails']	= $hideEmails;

			return render($tmpl, $data);
		}

		return false;
	}

}
?>