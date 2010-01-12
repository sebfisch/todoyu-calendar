<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 snowflake productions gmbh
*  All rights reserved
*
*  This script is part of the todoyu project.
*  The todoyu project is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License, version 2,
*  (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html) as published by
*  the Free Software Foundation;
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

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
	 * Check if event start and end is on different days
	 *
	 * @return	Bool
	 */
	public function isMultiDay() {
		return $this->isSingleDay() === false;
	}



	/**
	 * Check if event start and end is on the same day
	 *
	 * @return	Bool
	 */
	public function isSingleDay() {
		return date('Ymd', $this->getStartDate()) === date('Ymd', $this->getEndDate());
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
	 * Get the IDs if the assigned users
	 *
	 * @return	Array
	 */
	public function getAssignedUserIDs() {
		$assignedUsers	= TodoyuEventManager::getAssignedUsersOfEvent($this->getID(), false);

		return TodoyuArray::getColumn($assignedUsers, 'id_user');
	}



	/**
	 * Get data of the assigned users
	 *
	 * @return	Array
	 */
	public function getAssignedUserData() {
		return TodoyuEventManager::getAssignedUsersOfEvent($this->getID(), true);
	}



	/**
	 * Check if a user is assigned
	 *
	 * @param	Integer		$idUser
	 * @return	Bool
	 */
	public function isUserAssigned($idUser) {
		$idUser	= intval($idUser);
		$userIDs= $this->getAssignedUserIDs();

		return in_array($idUser, $userIDs);
	}



	/**
	 * Check if current user is assigned
	 *
	 * @return	Bool
	 */
	public function isCurrentUserAssigned() {
		return $this->isUserAssigned(userid());
	}



	/**
	 * Load event foreign data (assigned users)
	 *
	 */
	protected function loadForeignData()	{
		if( ! is_array($this->data['user']) ) {
			$this->data['user'] = $this->getAssignedUserData();
		}
	}



	/**
	 *	Get template data
	 *
	 *	@param	Boolean	$loadForeignData
	 *	@return	Array
	 */
	public function getTemplateData($loadForeignData = false) {
		if( $loadForeignData ) {
			$this->loadForeignData();
		}

		return parent::getTemplateData();
	}

}

?>