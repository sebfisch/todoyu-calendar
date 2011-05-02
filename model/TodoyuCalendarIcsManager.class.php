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
 * ICS Manager
 *
 * @package			Todoyu
 * @subpackage		Calendar
 */
class TodoyuCalendarIcsManager {

	/**
	 * Get personal calendar rendered in ICS format
	 *
	 * @return	String
	 */
	public static function getPersonalIcsExport($idPersonOwner = 0) {
			// Get vCalendar instance
		$vCal	= self::initVcalendar();

			// Get all events
		$events	= self::getEventsOfPerson($idPersonOwner);

			// Add events to calendar
		foreach($events as $eventData) {
			$eventData['attendees']	= TodoyuCalendarEventManager::getAssignedPersonsOfEvent($eventData['id'], false);

			$vCal	 = self::addEventToVcalendar($vCal, $eventData);
		}

			// Return ICS output
		return $vCal->createCalendar();
	}



	/**
	 * Get personal free/busy data rendered in ICS format
	 *
	 * @return	String
	 */
	public static function getFreeBusyIcsExport() {
		return "freebusy..";
	}




	/**
	 * Get all events
	 *
	 * @param	Array	$persons
	 * @param	Array	$eventTypes
	 * @param	Mixed	$dayEvents
	 * @param	String	$indexField
	 * @return	Array
	 */
	public static function getEventsOfPerson($idPerson, array $eventTypes = array(), $dayEvents = null, $indexField = 'id') {
		$idPerson	= intval($idPerson);

		$tables	= 	'ext_calendar_event				e,
					ext_calendar_mm_event_person	mmep';

		$fields	= '	e.*,
					mmep.id_person,
					mmep.is_acknowledged,
					mmep.is_updated,
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



	/**
	 * Initialize vCalendar
	 *
	 * @return	vcalendar
	 */
	public static function initVcalendar() {
		require_once( PATH_LIB . DIR_SEP . 'php' . DIR_SEP . 'iCalcreator' . DIR_SEP . 'iCalcreator.class.php' );

			// Set unique ID
		$config = array( 'unique_id' => Todoyu::$CONFIG['SYSTEM']['todoyuURL'] );

			// Create new calendar instance
		$vCal = new vcalendar( $config );

		$vCal->setProperty( 'method', 'PUBLISH' );
		$vCal->setProperty( "x-wr-calname", 'todoyu calendar' );
		$vCal->setProperty( "X-WR-CALDESC", 'todoyu Calendar' );
		$vCal->setProperty( "X-WR-TIMEZONE", Todoyu::$CONFIG['SYSTEM']['timezone'] );

		return $vCal;
	}



	/**
	 * Add given event to vCalendar
	 *
	 * @param	vcalendar	$vCal
	 * @param 	Array		$eventData
	 * @return	vcalendar
	 */
	public static function addEventToVcalendar(vcalendar $vCal, array $eventData) {
			// Create an event calendar component
		$vEvent = & $vCal->newComponent( 'vevent' );
		
			// Set props
		$vEvent->setProperty( 'dtstart', array(
			'year'	=> date('Y', $eventData['date_start']),
			'month'	=> date('n', $eventData['date_start']),
			'day'	=> date('j', $eventData['date_start']),
			'hour'	=> date('G', $eventData['date_start']),
			'min'	=> date('i', $eventData['date_start']),
			'sec'	=> date('s', $eventData['date_start']),
		));

		$vEvent->setProperty( 'dtend', array(
			'year'	=> date('Y', $eventData['date_end']),
			'month'	=> date('n', $eventData['date_end']),
			'day'	=> date('j', $eventData['date_end']),
			'hour'	=> date('G', $eventData['date_end']),
			'min'	=> date('i', $eventData['date_end']),
			'sec'	=> date('s', $eventData['date_end']),
		));

		$vEvent->setProperty( 'LOCATION',		$eventData['place']);
		$vEvent->setProperty( 'summary',		$eventData['title']);
		$vEvent->setProperty( 'description',	$eventData['description']);
//		$vEvent->setProperty( 'comment',		'');
		
			// Add attendees
		foreach($eventData['attendees'] as $personAttend) {
			$person	= TodoyuContactPersonManager::getPerson($personAttend['id_person']);
			$vEvent->setProperty( 'attendee', $person->getEmail());
		}

		return $vCal;
	}

}

?>