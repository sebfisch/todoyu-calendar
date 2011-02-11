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
 * Manage event mail DB logs
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuEventMailManager {

	/**
	 * @var	String		Default table for database requests
	 */
	const TABLE = 'ext_calendar_mm_event_personemail';



	/**
	 * Check whether mail popup is to be shown (not disabled, at least one other attendee person has email)
	 *
	 * @param	Integer		$idEvent
	 * @return	Boolean
	 */
	public static function isMailPopupToBeShown($idEvent) {
		$idEvent	= intval($idEvent);

		$prefName		= 'is_mailpopupdeactivated';
		$isDeactivated	= TodoyuCalendarPreferences::getPref($prefName, 0, 0, false, personid());

		if( $isDeactivated ) {
			return false;
		}

		return TodoyuEventManager::hasAnyEventPersonAnEmailAddress($idEvent, array(personid()));
	}



	/**
	 * Save log record about persons the given mail has been sent to
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$personIDs			Persons the event has been sent to
	 */
	public static function saveMailsSent($idEvent, array $personIDs = array() ) {
		$idEvent		= intval($idEvent);
		$personIDs		= TodoyuArray::intval($personIDs);

		foreach($personIDs as $idPerson) {
			self::addMailSent($idEvent, $idPerson);
		}
	}



	/**
	 * log sent event email of given event to given person
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 */
	public static function addMailSent($idEvent, $idPerson) {
		$idEvent	= intval($idEvent);
		$idPerson	= intval($idPerson);

		$data	= array(
			'id_person_create'	=> personid(),
			'date_create'		=> NOW,
			'id_event'			=> $idEvent,
			'id_person_email'	=> $idPerson,
		);

		TodoyuRecordManager::addRecord(self::TABLE, $data);
	}



	/**
	 * Get persons the given event has been sent to by email
	 *
	 * @param	Integer		$idEvent
	 * @return	Array
	 */
	public static function getEmailPersons($idEvent) {
		$idEvent	= intval($idEvent);

		$fields	= '	p.id,
					p.username,
					p.email,
					p.firstname,
					p.lastname,
					e.date_create';
		$tables	= '	ext_contact_person p,
					ext_event_mm_event_personemail e';
		$where	= '		e.id_event 		= ' . $idEvent .
				  ' AND	e.id_person_email	= p.id
					AND	p.deleted			= 0';
		$group	= '	p.id';
		$order	= '	p.lastname,
					p.firstname';
		$indexField	= 'id';

		return Todoyu::db()->getArray($fields, $tables, $where, $group, $order, '', $indexField);
	}

}

?>