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
	 * Get start time (at start day)
	 *
	 * @return	Integer
	 */
	public function getStartTime() {
		return TodoyuTime::getTimeOfDay($this->get('date_start'));
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
	 * Get duration of event in seconds
	 *
	 * @return	Integer
	 */
	public function getDuration() {
		return $this->getEndDate() - $this->getStartDate();
	}



	/**
	 * Get amount of hours of event duration
	 *
	 * @param	Integer		$precision
	 * @return	Integer
	 */
	public function getDurationHours($precision = 1) {
		return round($this->getDuration() / TodoyuTime::SECONDS_HOUR, $precision);
	}



	/**
	 * Get amount of minutes of event duration
	 *
	 * @param	Integer		$precision
	 * @return	Integer
	 */
	public function getDurationMinutes($precision = 0) {
		return round(($this->getEndDate() - $this->getStartDate()) / TodoyuTime::SECONDS_MIN, $precision);
	}



	/**
	 * Get duration as string
	 *
	 * @return String
	 */
	public function getDurationString() {
		return TodoyuString::getRangeString($this->getStartDate(), $this->getEndDate(), true);
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
	 * @return	Integer
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
	 * Get the IDs if assigned persons of event
	 *
	 * @return	Array
	 */
	public function getAssignedPersonIDs() {
		$assignedPersons	= TodoyuCalendarEventManager::getAssignedPersonsOfEvent($this->getID(), false);

		return TodoyuArray::getColumn($assignedPersons, 'id_person');
	}



	/**
	 * Get data of the assigned persons
	 *
	 * @param	Boolean		$getRemindersData
	 * @return	Array
	 */
	public function getAssignedPersonsData($getRemindersData = false) {
		return TodoyuCalendarEventManager::getAssignedPersonsOfEvent($this->getID(), true, $getRemindersData);
	}



	/**
	 * Get assignment for person
	 *
	 * @param	Integer		$idPerson
	 * @return	TodoyuCalendarEventAssignment
	 */
	public function getAssignment($idPerson) {
		$idPerson	= intval($idPerson);

		return TodoyuCalendarEventAssignmentManager::getAssignmentByEventPerson($this->getID(), $idPerson);
	}



	/**
	 * Get reminder for person
	 *
	 * @param	Integer		$idPerson
	 * @return	TodoyuCalendarReminder
	 */
	public function getReminder($idPerson = 0) {
		$idPerson	= Todoyu::personid($idPerson);

		return TodoyuCalendarReminderManager::getReminderByAssignment($this->getID(), $idPerson);
	}



	/**
	 * Get popup reminder
	 *
	 * @param	Integer		$idPerson
	 * @return	TodoyuCalendarReminderPopup
	 */
	public function getReminderPopup($idPerson = 0) {
		$idPerson	= Todoyu::personid($idPerson);

		return TodoyuCalendarReminderPopupManager::getReminderByAssignment($this->getID(), $idPerson);
	}


	public function getReminderEmail($idPerson = 0) {
		$idPerson	= Todoyu::personid($idPerson);

		return TodoyuCalendarReminderEmailManager::getReminderByAssignment($this->getID(), $idPerson);
	}



	/**
	 * Get reminder time for email
	 *
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public function getReminderTimeEmail($idPerson = 0) {
		return $this->getReminderTime('email', $idPerson);
	}



	/**
	 * Get reminder time for popup
	 *
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public function getReminderTimePopup($idPerson = 0) {
		return $this->getReminderTime('popup', $idPerson);
	}



	/**
	 * Get reminder time for type
	 *
	 * @param	String		$type
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	private function getReminderTime($type, $idPerson = 0) {
		$idPerson		= Todoyu::personid($idPerson);
		$assignedPersons= $this->getAssignedPersonsData(true);

		if( array_key_exists($idPerson, $assignedPersons) ) {
			$key	= 'date_remind' . $type;
			return intval($assignedPersons[$idPerson][$key]);
		} else {
			return 0;
		}
	}



	/**
	 * Get reminder advance time for email (in seconds)
	 *
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public function getReminderAdvanceTimeEmail($idPerson = 0) {
		return $this->getReminderAdvanceTime('email', $idPerson);
	}



	/**
	 * Get reminder advance time for popup (in seconds)
	 *
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public function getReminderAdvanceTimePopup($idPerson = 0) {
		return $this->getReminderAdvanceTime('popup', $idPerson);
	}



	/**
	 * Get reminder advance time for type
	 *
	 * @param	String		$type
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	private function getReminderAdvanceTime($type, $idPerson = 0) {
		$idPerson	= Todoyu::personid($idPerson);
		$remindTime	= $this->getReminderTime($type, $idPerson);

		if( $remindTime === 0 ) {
			return 0;
		} else {
			return $this->getStartDate() - $remindTime;
		}
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
		return $this->isPersonAssigned(Todoyu::personid());
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
	 * Load event foreign data
	 *
	 * @param	Boolean	$getRemindersData
	 */
	protected function loadForeignData($getRemindersData = false) {
			// Add assigned persons of event
		if( ! isset($this->data['persons']) ) {
			$this->data['persons'] 	= $this->getAssignedPersonsData($getRemindersData);
		}

			// Add email receivers infos
		$emailPersons	= TodoyuMailManager::getEmailPersons(EXTID_CALENDAR, CALENDAR_TYPE_EVENT, $this->data['id']);
		$this->data['persons_email']	= $emailPersons;
	}



	/**
	 * Get template data
	 *
	 * @param	Boolean		$loadForeignData
	 * @param	Boolean		$loadCreatorPersonData
	 * @param	Boolean		$loadRemindersData
	 * @return	Array
	 */
	public function getTemplateData($loadForeignData = false, $loadCreatorPersonData = false, $loadRemindersData = false) {
		if( $loadForeignData ) {
			$this->loadForeignData($loadRemindersData);
		}

		if( $loadCreatorPersonData ) {
			$this->data['person_create']	= TodoyuContactPersonManager::getPersonArray($this->data['id_person_create']);
		}

			// Add calculated event duration in hours, amount of intersected days
		$this->data['duration']	= array(
			'seconds'			=> $this->getDuration(),
			'minutes'			=> $this->getDurationMinutes(),
			'hours'				=> $this->getDurationHours(),
			'daysIntersected'	=> TodoyuCalendarEventManager::getAmountDaysIntersected($this->getID())
		);

		$this->data['durationString']	= $this->getDurationString();

		return parent::getTemplateData();
	}



	/**
	 * Check whether other persons than the current are assigned to the event
	 *
	 * @return	Boolean
	 */
	public function areOtherPersonsAssigned() {
		$assignedPersonIDs	= $this->getAssignedPersonIDs();
		$others				= array_diff($assignedPersonIDs, array(Todoyu::personid()));

		return sizeof($others) > 0;
	}



	/**
	 * Check whether any of the assigned person has an email address
	 *
	 * @return	Boolean
	 */
	public function hasAnyAssignedPersonAnEmailAddress() {
		$personsData	= $this->getAssignedPersonsData(false);

		foreach($personsData as $personData) {
			if( trim($personData['email']) !== '' ) {
				return true;
			}
		}

		return false;
	}

}

?>