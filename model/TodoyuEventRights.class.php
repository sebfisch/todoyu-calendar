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
 * Event rights functions
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuEventRights {

	/**
	 * Deny access
	 * Shortcut for calendar
	 *
	 * @param	String		$right		Denied right
	 */
	private static function deny($right) {
		TodoyuRightsManager::deny('calendar', $right);
	}



	/**
	 * Check if a person is allowed to see an event
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isSeeAllowed($idEvent) {
		$idEvent= intval($idEvent);

		$event				= TodoyuEventManager::getEvent($idEvent);
		$idCreator			= $event->getPerson('create');
		$isPrivate			= $event->data['is_private'] === '1';
		$assignedPersons	= $event->getAssignedPersonIDs();

		$idPerson	= personid();

			// Admin sees all events.
		if ( TodoyuAuth::isAdmin() ) {
			return true;
		}
			// Person is assigned to event
		if ( in_array($idPerson, $assignedPersons) ) {
			return true;
		}
			// Person can see all events and event is not private,
		if ( allowed('calendar', 'event:seeAll') && ! $isPrivate ) {
			return true;
		}

		return false;
	}



	/**
	 * Check if person is allowed to add new events
	 *
	 * @return	Boolean
	 */
	public static function isAddAllowed() {
		return allowed('calendar', 'event:add');
	}



	/**
	 * Check if person can edit an event
	 * Check if person has edit rights and is assigned if neccessary
	 *
	 * @param	Integer		$idEvent
	 * @return	Boolean
	 */
	public static function isEditAllowed($idEvent) {
		$idEvent	= intval($idEvent);

		$event				= TodoyuEventManager::getEvent($idEvent);
		$idCreator			= $event->getPerson('create');
		$isPrivate			= $event->data['is_private'] === '1';
		$assignedPersons	= $event->getAssignedPersonIDs();

		$idPerson	= personid();

			// Admin sees all events.
		if ( TodoyuAuth::isAdmin() ) {
			return true;
		}
			// Person is assigned to event and has right to edit events it's assigned to
		if ( allowed('calendar', 'event:editAndDeleteAssigned')  && in_array($idPerson, $assignedPersons) ) {
			return true;
		}
			// Person can edit all events and event is not private,
		if ( allowed('calendar', 'event:editAndDeleteAll') && ! $isPrivate ) {
			return true;
		}

		return false;
	}



	/**
	 * Check whether person is allowed to see birthdays listing in portal (admin or internal)
	 *
	 *  @return	Boolean
	 */
	public static function isAllowedSeeBirthdaysInPortal() {
		if ( TodoyuAuth::isAdmin() ) {
			return true;
		}

		$person	= TodoyuPersonManager::getPerson(personid());

		return $person->isInternal();
	}



	/**
	 * Restrict access to persons who are allowed to see the event
	 *
	 * @param	Integer		$idEvent
	 */
	public static function restrictSee($idEvent) {
		if( ! self::isSeeAllowed($idEvent) ) {
			self::deny('event:seeAll');
		}
	}



	/**
	 * Restrict access to persons who are allowed to add events
	 */
	public static function restrictAdd() {
		if( ! self::isAddAllowed() ) {
			self::deny('event:add');
		}
	}



	/**
	 * Restrict access to persons who are allowed to edit events
	 *
	 * @param	Integer		$idTask
	 */
	public static function restrictEdit($idEvent) {
		if( ! self::isEditAllowed($idEvent) ) {
			self::deny('event:editAndDeleteAssigned');
		}
	}

}
?>