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
 * Event Info Mail
 *
 * @package		Todoyu
 * @subpackage	Calendar
 *
 */
class TodoyuCalendarEventInfoEmail extends TodoyuMail {

	/**
	 * Sent event
	 *
	 * @var	TodoyuCalendarEvent
	 */
	private $event;

	/**
	 * Receiver person
	 *
	 * @var	TodoyuContactPerson
	 */
	private $person;

	/**
	 * Type of action while email was sent
	 *
	 * @var	Integer
	 */
	private $actionType;


	public function __construct($idEvent, $idPerson, $actionType, array $config = array()) {
		parent::__construct($config);

		$this->event		= TodoyuCalendarEventManager::getEvent($idEvent);
		$this->person		= TodoyuContactPersonManager::getPerson($idPerson);
		$this->actionType	= intval($actionType);

		$this->init();
	}



	/**
	 * Initialize info email with correct data
	 */
	private function init() {
		$this->addReceiver($this->person->getID());
		$this->setSender(TodoyuAuth::getPersonID());
		$this->setTypeSubject();

		$this->setHeadlineByType();

		Todoyu::setEnvironmentForPerson($this->person->getID());

		$this->setHtmlContent($this->getContent(true));
		$this->setTextContent($this->getContent(false));

		Todoyu::resetEnvironment();
	}



	/**
	 * Set headline by type
	 */
	private function setHeadlineByType() {
		$headline	= '';

		switch($this->actionType) {
			case OPERATIONTYPE_RECORD_CREATE:
				$headline	= 'calendar.event.mail.title.create';
				break;
			case OPERATIONTYPE_RECORD_DELETE:
				$headline	= 'calendar.event.mail.title.deleted';
				break;
			case OPERATIONTYPE_RECORD_UPDATE:
				$headline	= 'calendar.event.mail.title.update';
				break;
		}

		$this->setHeadline($headline);
	}



	/**
	 * Set mail subject according to the action type
	 */
	private function setTypeSubject() {
		switch( $this->actionType ) {
			case OPERATIONTYPE_RECORD_CREATE:
				$prefix	= Todoyu::Label('calendar.event.mail.title.create');
				break;
			case OPERATIONTYPE_RECORD_UPDATE:
				$prefix	= Todoyu::Label('calendar.event.mail.title.update');
				break;
			case OPERATIONTYPE_RECORD_DELETE: default:
				$prefix	= Todoyu::Label('calendar.event.mail.title.deleted');
				break;
			default:
				$prefix	= 'Unknown Action';
		}

		$subject	= $prefix . ': ' . $this->event->getTitle() . ' - ' . $this->event->getDurationString();

		$this->setSubject($subject);
	}



	/**
	 * Get content for email
	 *
	 * @param	Boolean		$asHtml
	 * @return	String|Boolean
	 */
	private function getContent($asHtml = false) {
		$tmpl	= $this->getTemplate($asHtml);
		$data	= $this->getData();

		$data['hideEmails']	= true;
		$data['colors']		= TodoyuCalendarEventManager::getEventTypeColors();

		return Todoyu::render($tmpl, $data);
	}



	/**
	 * Get filename of email template to current mode (text/HTML) and event operation
	 *
	 * @param	Boolean		$asHtml
	 * @return	String|Boolean
	 */
	private function getTemplate($asHtml = false) {
		$basePath	= 'ext/calendar/view/emails/';
		$postFix	= $asHtml ? 'html' : 'text';

		switch($this->actionType) {
			case OPERATIONTYPE_RECORD_CREATE:
				$fileType	= 'event-new';
				break;
			case OPERATIONTYPE_RECORD_DELETE:
				$fileType	= 'event-deleted';
				break;
			case OPERATIONTYPE_RECORD_UPDATE:
				$fileType	= 'event-update';
				break;
			default:
				TodoyuLogger::logError('Mail template missing because of wrong operation ID: ' . $this->actionType);
				$fileType	= false;
				break;
		}

		if( $fileType === false ) {
			return false;
		}

		return TodoyuFileManager::pathAbsolute($basePath . $fileType . '-' . $postFix . '.tmpl');
	}



	/**
	 * Get data for email
	 *
	 * @return	Array
	 */
	private function getData() {
		return TodoyuCalendarEventMailManager::getMailData($this->event->getID(), $this->person->getID(), true);
	}

}

?>