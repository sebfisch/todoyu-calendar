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
	 * @param	String		$hash
	 * @param	Integer		$idPersonOwner
	 * @return	Void|String
	 */
	public static function getPersonalExport($hash, $idPersonOwner = 0) {
		$idPersonOwner	= intval($idPersonOwner);
		$personOwner	= TodoyuContactPersonManager::getPerson($idPersonOwner);

		$name		= 'todoyu personal calendar ' . $personOwner->getShortname();
		$description= 'Appointments data of ' . $personOwner->getFullName();

		$iCal	= TodoyuIcalManager::getIcal($hash, $name, $description);

			// Add events data (as vevent components)
		$fromTimestamp	= NOW - TodoyuTime::SECONDS_DAY * Todoyu::$CONFIG['EXT']['calendar']['icalScopeStartWeeksInPast'];
		$events			= self::getEventsOfPerson($idPersonOwner, $fromTimestamp);

		foreach($events as $eventData) {
			$eventData['attendees']	= TodoyuCalendarEventManager::getAssignedPersonsOfEvent($eventData['id'], false);
			$iCal->addEvent($eventData);
		}

			// Send file or return iCal formatted output
		$sendFile	= TodoyuRequest::getParam('download', true);
		if( $sendFile === 1 ) {
			$iCal->send();
		} else {
			return $iCal->render();
		}
	}



	/**
	 * Get/send personal free/busy data rendered in iCal format (.ics)
	 *
	 * @param	String		$hash
	 * @param	Integer		$idPersonOwner
	 * @return	Void|String
	 */
	public static function getFreeBusyExport($hash, $idPersonOwner = 0) {
		$idPersonOwner	= intval($idPersonOwner);
		$personOwner	= TodoyuContactPersonManager::getPerson($idPersonOwner);

		$name		= 'todoyu freebusy calendar ' . $personOwner->getShortname();
		$description= 'Freebusy data of ' . $personOwner->getFullName();

		$iCal	= TodoyuIcalManager::getIcal($hash, $name, $description);

			// Add events data (as vfreebusy components)
		$fromTimestamp	= NOW - TodoyuTime::SECONDS_DAY * Todoyu::$CONFIG['EXT']['calendar']['icalScopeStartWeeksInPast'];
		$events			= self::getEventsOfPerson($idPersonOwner, $fromTimestamp);

		foreach($events as $eventData) {
			$eventData['attendees']	= TodoyuCalendarEventManager::getAssignedPersonsOfEvent($eventData['id'], false);
			$iCal->addFreebusy($eventData, true);
		}

			// Send file or return iCal formatted output
		$sendFile	= TodoyuRequest::getParam('download', true);
		if( $sendFile === 1 ) {
			$iCal->send();
		} else {
			return $iCal->render();
		}
	}



	/**
	 * Get all events (using given filtering)
	 *
	 * @param	Integer	$idPerson
	 * @param	Integer	$from				Timestamp from when the earliest
	 * @param	Array	$eventTypes
	 * @param	Mixed	$dayEvents
	 * @param	String	$indexField
	 * @return	Array
	 */
	private static function getEventsOfPerson($idPerson, $from = 0, array $eventTypes = array(), $dayEvents = null, $indexField = 'id') {
		$idPerson	= intval($idPerson);
		$from		= intval($from);

		$tables	= 	'ext_calendar_event				e,
					ext_calendar_mm_event_person	mmep';

		$fields	= '	e.*,
					mmep.id_person,
					e.date_end - e.date_start as duration';

		$where	= '		e.id		= mmep.id_event
					AND e.deleted	= 0';

		if( $from != 0 ) {
			$where	.= ' AND e.date_start > ' . $from;
		}

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