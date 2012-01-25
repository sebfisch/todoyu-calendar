<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2012, snowflake productions GmbH, Switzerland
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
	 * @var	TodoyuCalendarEventStatic
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
	 * @var	String
	 */
	private $operation;


	/**
	 * Initialize
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @param	String		$operation
	 * @param	Array		$config
	 */
	public function __construct($idEvent, $idPerson, $operation, array $config = array()) {
		parent::__construct($config);

		$this->event		= TodoyuCalendarEventStaticManager::getEvent($idEvent);
		$this->person		= TodoyuContactPersonManager::getPerson($idPerson);
		$this->operation	= trim($operation);

		$this->init();
	}


	/**
	 * Get person
	 *
	 * @return	TodoyuContactPerson
	 */
	public function getPerson() {
		return $this->person;
	}



	/**
	 * Get event
	 *
	 * @return	TodoyuCalendarEventStatic
	 */
	public function getEvent() {
		return $this->event;
	}



	/**
	 * Get operation key
	 *
	 * @return	String
	 */
	public function getOperation() {
		return $this->operation;
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

		switch( $this->getOperation() ) {
			case 'create':
				$headline	= 'calendar.event.mail.title.create';
				break;
			case 'delete':
				$headline	= 'calendar.event.mail.title.deleted';
				break;
			case 'update':
				$headline	= 'calendar.event.mail.title.update';
				break;
		}

		$this->setHeadline($headline);
	}



	/**
	 * Set mail subject according to the action type
	 */
	private function setTypeSubject() {
		switch( $this->getOperation() ) {
			case 'create':
				$prefix	= Todoyu::Label('calendar.event.mail.title.create');
				break;
			case 'update':
				$prefix	= Todoyu::Label('calendar.event.mail.title.update');
				break;
			case 'delete':
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
		$data['colors']		= TodoyuCalendarEventStaticManager::getEventTypeColors();

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

		switch( $this->getOperation() ) {
			case 'create':
				$fileType	= 'event-new';
				break;
			case 'delete':
				$fileType	= 'event-deleted';
				break;
			case 'update':
				$fileType	= 'event-update';
				break;
			default:
				TodoyuLogger::logError('Mail template missing because of wrong operation ID: ' . $this->getOperation());
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