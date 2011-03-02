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
 * Event
 *
 * @package		Todoyu
 * @subpackage	Calendar
 *
 */
class TodoyuCalendarEvent extends TodoyuBaseObject {

	/**
	 * Initialize event
	 *
	 * @param	Integer		$idEvent
	 */
	public function __construct($idEvent) {
		parent::__construct($idEvent, 'ext_calendar_event');
	}



	/**
	 * Get start date
	 *
	 * @return	Integer
	 */
	public function getStartDate() {
		return $this->get('date_start');
	}



	/**
	 * Get end date of event
	 *
	 * @return	Integer
	 */
	public function getEndDate() {
		return $this->get('date_end');
	}



	/**
	 * Get duration of event
	 *
	 * @return	Integer
	 */
	public function getDuration() {
		return $this->getEndDate() - $this->getStartDate();
	}



	/**
	 * Get place of event
	 *
	 * @return	String
	 */
	public function getPlace() {
		return $this->get('place');
	}



	/**
	 * Get title of event
	 *
	 * @return	String
	 */
	public function getTitle() {
		return $this->get('title');
	}



	/**
	 * Get full label of event
	 *
	 * @param	Boolean		$withType
	 * @return	String
	 */
	public function getFullLabel($withType = true) {
		$label	= TodoyuTime::format($this->getStartDate(), 'DshortD2MlongY4') . ': ' . $this->getTitle();

		if( $withType ) {
			$label	.= ' (' . $this->getTypeLabel() . ')';
		}

		return $label;
	}



	/**
	 * Get event type (ID)
	 *
	 * @return	String
	 */
	public function getType() {
		return $this->get('eventtype');
	}



	/**
	 * Get type key
	 *
	 * @return	String
	 */
	public function getTypeKey() {
		return TodoyuCalendarEventTypeManager::getEventTypeKey($this->getType());
	}



	/**
	 * Get type label
	 *
	 * @return	String
	 */
	public function getTypeLabel() {
		return TodoyuCalendarEventTypeManager::getEventTypeLabel($this->getType(), true);
	}



	/**
	 * Get the IDs if the assigned persons
	 *
	 * @return	Array
	 */
	public function getAssignedPersonIDs() {
		$assignedPersons	= TodoyuCalendarEventManager::getAssignedPersonsOfEvent($this->id, false);

		return TodoyuArray::getColumn($assignedPersons, 'id_person');
	}



	/**
	 * Get data of the assigned persons
	 *
	 * @return	Array
	 */
	public function getAssignedPersonsData() {
		return TodoyuCalendarEventManager::getAssignedPersonsOfEvent($this->id, true);
	}



	/**
	 * Check whether a person is assigned
	 *
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public function isPersonAssigned($idPerson) {
		$idPerson	= intval($idPerson);
		$personIDs	= $this->getAssignedPersonIDs();

		return in_array($idPerson, $personIDs);
	}



	/**
	 * Check whether event start and end is on different days
	 *
	 * @return	Boolean
	 */
	public function isMultiDay() {
		return $this->isSingleDay() === false;
	}



	/**
	 * Check whether event start and end is on the same day
	 *
	 * @return	Boolean
	 */
	public function isSingleDay() {
		return date('Ymd', $this->getStartDate()) === date('Ymd', $this->getEndDate());
	}



	/**
	 * Check whether event is a full-day event
	 *
	 * @return	Boolean
	 */
	public function isDayevent() {
		return intval($this->data['is_dayevent']) === 1;
	}



	/**
	 * Check whether current person is assigned
	 *
	 * @return	Boolean
	 */
	public function isCurrentPersonAssigned() {
		return $this->isPersonAssigned(personid());
	}



	/**
	 * Check whether event is private
	 *
	 * @return	Boolean
	 */
	public function isPrivate() {
		return intval($this->data['is_private']) === 1;
	}



	/**
	 * Check whether event is overbookable (generally allowed or type not overbooking relevant)
	 *
	 * @return Boolean
	 */
	public function isOverbookable() {
		$overbookableTypes	= TodoyuArray::assure(Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_OVERBOOKABLE']);

		return ( ! $this->isDayevent() ) && ( ! in_array($this->get('eventtype'), $overbookableTypes) );
	}



	/**
	 * Load event foreign data (assigned persons)
	 */
	protected function loadForeignData() {
		if( ! isset($this->data['persons']) ) {
			$this->data['persons'] 	= $this->getAssignedPersonsData();
		}

		$emailPersons	= TodoyuMailManager::getEmailPersons(EXTID_CALENDAR, CALENDAR_TYPE_EVENT, $this->data['id']);
		$this->data['persons_email']	= $emailPersons;
	}



	/**
	 * Get template data
	 *
	 * @param	Boolean		$loadForeignData
	 * @param	Boolean		$loadCreatorPersonData
	 * @return	Array
	 */
	public function getTemplateData($loadForeignData = false, $loadCreatorPersonData = false) {
		if( $loadForeignData ) {
			$this->loadForeignData();
		}

		if( $loadCreatorPersonData ) {
			$this->data['person_create']	= TodoyuContactPersonManager::getPersonArray($this->data['id_person_create']);
		}

		return parent::getTemplateData();
	}

}

?>