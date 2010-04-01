<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
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
class TodoyuEvent extends TodoyuBaseObject {

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
		return TodoyuEventTypeManager::getEventTypeKey($this->getType());
	}



	/**
	 * Get type label
	 *
	 * @return	String
	 */
	public function getTypeLabel() {
		return TodoyuEventTypeManager::getEventTypeLabel($this->getType(), true);
	}



	/**
	 * Get the IDs if the assigned persons
	 *
	 * @return	Array
	 */
	public function getAssignedPersonIDs() {
		$assignedPersons	= TodoyuEventManager::getAssignedPersonsOfEvent($this->id, false);

		return TodoyuArray::getColumn($assignedPersons, 'id_person');
	}



	/**
	 * Get data of the assigned persons
	 *
	 * @return	Array
	 */
	public function getAssignedPersonsData() {
		return TodoyuEventManager::getAssignedPersonsOfEvent($this->id, true);
	}



	/**
	 * Check if a person is assigned
	 *
	 * @param	Integer		$idPerson
	 * @return	Bool
	 */
	public function isPersonAssigned($idPerson) {
		$idPerson	= intval($idPerson);
		$personIDs	= $this->getAssignedPersonIDs();

		return in_array($idPerson, $personIDs);
	}



	/**
	 * Check if event start and end is on different days
	 *
	 * @return	Boolean
	 */
	public function isMultiDay() {
		return $this->isSingleDay() === false;
	}



	/**
	 * Check if event start and end is on the same day
	 *
	 * @return	Boolean
	 */
	public function isSingleDay() {
		return date('Ymd', $this->getStartDate()) === date('Ymd', $this->getEndDate());
	}



	/**
	 * Check if current person is assigned
	 *
	 * @return	Boolean
	 */
	public function isCurrentPersonAssigned() {
		return $this->isPersonAssigned(personid());
	}



	/**
	 * Load event foreign data (assigned persons)
	 */
	protected function loadForeignData() {
		if( ! isset($this->data['persons']) ) {
			$this->data['persons'] 	= $this->getAssignedPersonsData();
		}
	}



	/**
	 * Get template data
	 *
	 * @param	Boolean		$loadForeignData
	 * @return	Array
	 */
	public function getTemplateData($loadForeignData = false) {
		if( $loadForeignData ) {
			$this->loadForeignData();
		}

		return parent::getTemplateData();
	}

}

?>