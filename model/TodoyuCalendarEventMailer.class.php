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
	 * @return	Boolean
	 */
	public static function sendEmails($idEvent, array $personIDs, $operationID) {
		$idEvent	= intval($idEvent);
		$personIDs	= TodoyuArray::intval($personIDs, true, true);

		$succeeded	= true;
		foreach($personIDs as $idPerson) {
			$result = self::sendInfoMail($idEvent, $idPerson, $operationID);

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
	 * @param	Integer		$operationID
	 * @return	Boolean		Success
	 */
	public static function sendInfoMail($idEvent, $idPerson, $operationID = OPERATIONTYPE_RECORD_CREATE) {
		$idEvent	= intval($idEvent);
		$idPerson	= intval($idPerson);
		$operationID= intval($operationID);
		$event		= TodoyuCalendarEventStaticManager::getEvent($idEvent);

		if( $event->isDeleted() ) {
			$operationID	= OPERATIONTYPE_RECORD_DELETE;
		}

		$mail	= new TodoyuCalendarEventInfoEmail($idEvent, $idPerson, $operationID);

		TodoyuHookManager::callHook('calendar', 'email.info', array($idEvent, $idPerson, $operationID));

		return $mail->send();
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
				$subject	= Todoyu::Label('calendar.event.mail.title.create');
				break;
			case OPERATIONTYPE_RECORD_UPDATE:
				$subject	= Todoyu::Label('calendar.event.mail.title.update');
				break;
			case OPERATIONTYPE_RECORD_DELETE: default:
				$subject	= Todoyu::Label('calendar.event.mail.title.deleted');
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
				TodoyuLogger::logError('Mail template missing because of wrong operation ID: ' . $operationID);
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
	 * @param	Boolean		$modeHTML
	 * @param	Integer		$operationID	what's been done? (create, update, delete)
	 * @return	String|Boolean
	 */
	private static function getMailContent($idEvent, $idPerson, $hideEmails = true, $modeHTML = true, $operationID) {
		$idEvent	= intval($idEvent);
		$idPerson	= intval($idPerson);

		$tmpl	= self::getMailTemplateName($operationID, $modeHTML);

		if( $tmpl !== false ) {
			$data				= self::getMailData($idEvent, $idPerson);
			$data['hideEmails']	= $hideEmails;

			return Todoyu::render($tmpl, $data);
		}

		return false;
	}

}
?>