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
 * todoyu Calendar's iCal Manager
 *
 * @package			Todoyu
 * @subpackage		Calendar
 */
class TodoyuCalendarIcalManager {

	/**
	 * Get personal calendar rendered in iCal format (.ics)
	 *
	 * @param	Integer		$idPersonOwner
	 * @return	String
	 */
	public static function getPersonalExport($idPersonOwner = 0) {
		$idPersonOwner = intval($idPersonOwner);

		$iCal	= new TodoyuIcal();

		$events	= self::getEventsOfPerson($idPersonOwner);

		foreach($events as $eventData) {
			$eventData['attendees']	= TodoyuCalendarEventManager::getAssignedPersonsOfEvent($eventData['id'], false);
			$iCal->addEvent($eventData);
		}

			// Return iCal formatted output
		return $iCal->render();
	}



	/**
	 * Get personal free/busy data rendered in iCal format (.ics)
	 *
	 * @param	Integer		$idPersonOwner
	 * @return	String
	 */
	public static function getFreeBusyExport($idPersonOwner = 0) {
		$idPersonOwner = intval($idPersonOwner);

		$iCal	= new TodoyuIcal();

		$events	= self::getEventsOfPerson($idPersonOwner);

		foreach($events as $eventData) {
			$eventData['attendees']	= TodoyuCalendarEventManager::getAssignedPersonsOfEvent($eventData['id'], false);
			$iCal->addFreebusy($eventData, true);
		}

			// Return iCal formatted output
		return $iCal->render();
	}




	/**
	 * Get all events (using given filtering)
	 *
	 * @param	Integer	$idPerson
	 * @param	Array	$eventTypes
	 * @param	Mixed	$dayEvents
	 * @param	String	$indexField
	 * @return	Array
	 */
	private static function getEventsOfPerson($idPerson, array $eventTypes = array(), $dayEvents = null, $indexField = 'id') {
		$idPerson	= intval($idPerson);

		$tables	= 	'ext_calendar_event				e,
					ext_calendar_mm_event_person	mmep';

		$fields	= '	e.*,
					mmep.id_person,
					e.date_end - e.date_start as duration';

		$where	= '		e.id		= mmep.id_event
					AND e.deleted	= 0';
		$group	= '';
		$order	= 'e.date_start, duration DESC';
		$limit	= '';

			// DayEvents: null = both, true = only, false = without
		if( ! is_null($dayEvents) ) {
			$where .= ' AND e.is_dayevent = ' . ( $dayEvents === true ? '1' : '0' );
		}

			// Limit to given event types
		if( sizeof($eventTypes) > 0 ) {
			$where .= ' AND e.eventtype IN(' . implode(',', $eventTypes) . ')';
		}

		$where	.= ' AND mmep.id_person = ' . $idPerson;

		return Todoyu::db()->getArray($fields, $tables, $where, $group, $order, $limit, $indexField);
	}

}

?>