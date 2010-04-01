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
 * Event Manager
 *
 * @package			Todoyu
 * @subpackage		Calendar
 */

class TodoyuEventManager {

	/**
	 * Default table for database requests
	 */
	const TABLE = 'ext_calendar_event';



	/**
	 * Get form object form quick create
	 *
	 * @param	Integer		$idEvent
	 * @return
	 */
	public static function getQuickCreateForm($idEvent = 0) {
		$idEvent	= intval($idEvent);

			// Create form object
		$xmlPath	= 'ext/calendar/config/form/event.xml';
		$form		= TodoyuFormManager::getForm($xmlPath, $idEvent);

			// Adjust form to needs of quick creation wizard
		$form->setAttribute('action', '?ext=calendar&amp;controller=quickcreateevent');
		$form->setAttribute('onsubmit', 'return false');
		$form->getFieldset('buttons')->getField('save')->setAttribute('onclick', 'Todoyu.Ext.calendar.QuickCreateEvent.save(this.form)');
		$form->getFieldset('buttons')->getField('cancel')->setAttribute('onclick', 'Todoyu.Popup.close(\'quickcreate\')');

		return $form;
	}



	/**
	 * Get event object
	 *
	 * @param	Integer		$idEvent
	 * @return	TodoyuEvent
	 */
	public static function getEvent($idEvent) {
		return TodoyuRecordManager::getRecord('TodoyuEvent', $idEvent);
	}



	/**
	 * Get event record from database
	 *
	 * @param	Integer		$idEvent
	 * @return	Array
	 */
	public static function getEventRecord($idEvent) {
		$idEvent = intval($idEvent);

		return Todoyu::db()->getRecord(self::TABLE, $idEvent);
	}



	/**
	 * Get all events within given timestamps
	 *
	 * @param	Integer		$dateStart		timestamp at beginning of timespan
	 * @param	Integer		$dateEnd		timestamp at end of timespan	(optionally 0, will be set to 5 years after today than)
	 * @param	Array		$persons
	 * @param	Array		$eventTypes
	 * @param	Mixed		$dayEvents		null = both types, true = only dayevents, false = only non-dayevents
	 * @param	String		$indexField
	 * @return	Array
	 */
	public static function getEventsInTimespan($dateStart, $dateEnd, array $persons = array(), array $eventTypes = array(), $dayEvents = null, $indexField = 'id') {
		$dateStart	= intval($dateStart);
		$dateEnd	= intval($dateEnd);
		$persons	= TodoyuArray::intval($persons, true, true);

		$fields	= '	e.*,
					mmeu.id_person,
					mmeu.is_acknowledged,
					e.date_end - e.date_start as duration';

		$tables	= 	self::TABLE  . ' e,	ext_calendar_mm_event_person mmeu';

		$where	= '		e.id		= mmeu.id_event
					AND e.deleted	= 0
					AND (	e.date_start BETWEEN ' . $dateStart . ' AND ' . $dateEnd . ' OR
							e.date_end BETWEEN ' . $dateStart . ' AND ' . $dateEnd . ' OR
							(e.date_start < ' . $dateStart . ' AND e.date_end > ' . $dateEnd . ')
					)';

		$group	= '';
		$order	= 'e.date_start, duration DESC';
		$limit	= '';

			// DayEvents: null = both, true = only, false = without
		if( $dayEvents === true ) {
			$where .= ' AND e.is_dayevent = 1';
		} elseif( $dayEvents === false ) {
			$where .= ' AND e.is_dayevent = 0';
		}

			// Persons
		if( sizeof($persons) > 0 ) {
			$where	.= ' AND mmeu.id_person IN(' . implode(',', $persons) . ',0)';
		}


			// Event types
		if( sizeof($eventTypes) > 0 ) {
			$where .= ' AND e.eventtype IN(\'' . implode("','", $eventTypes) . '\')';
		}


			// Assigned persons
		if( ! allowed('calendar', 'event:seeAll') ) {
			$where .= ' AND mmeu.id_person IN(' . personid() . ',0)';
		}

		$res	= Todoyu::db()->getArray($fields, $tables, $where, $group, $order, $limit, $indexField);
//		TodoyuDebug::printHtml($res);
		return $res;
	}



	/**
	 * Group the events in subarray. The key for each subarray is a datekey (YYYYMMDD)
	 * An event appears in each subarray, the event is running on
	 *
	 * @param	Array		$events			Array of event records
	 * @param	Integer		$dateStart		Date of first day group
	 * @param	Integer		$dateEnd		Date of last day group
	 * @return	Array		Events grouped by datekey
	 */
	public static function groupEventsByDay(array $events, $dateStart, $dateEnd) {
		$dateStart		= TodoyuTime::getStartOfDay($dateStart);
		$dateEnd		= intval($dateEnd);
		$groupedEvents	= array();

		for($date = $dateStart; $date <= $dateEnd; $date += TodoyuTime::SECONDS_DAY ) {
			$dayKey		= date('Ymd', $date);
			$dayRange	= TodoyuTime::getDayRange($date);

			$groupedEvents[$dayKey]	= array();

			foreach($events as $event) {
				if( TodoyuTime::rangeOverlaps($dayRange['start'], $dayRange['end'], $event['date_start'], $event['date_end']) ) {
					$groupedEvents[$dayKey][] = $event;
				}
			}
		}

		return $groupedEvents;
	}



	/**
	 * Calculate events' intersections / proximation (have events with overlapping time be arranged hor. parallel)
	 *
	 * @param	Array	$events
	 * @param	String	$dateKey	date of currently rendered day (YYYYMMDD)
	 */
	public static function addOverlapInformationToEvents(array $eventsByDay) {
		foreach($eventsByDay as $dayKey => $eventsOfDay)	{
			$leftPositionArray = array();
			$currentPosition   = 0;
			$index			   = 0;

				//1st step: get left position of each event
			foreach($eventsOfDay as $idEvent => $eventArray)	{
				if(sizeof($leftPositionArray) == 0)	{
					$leftPositionArray[$currentPosition][] 				= $idEvent;
					$eventsByDay[$dayKey][$idEvent]['_overlapIndex']	= $currentPosition;
				} else {
					$eventOK = false;
					while($eventOK == false)	{
						if(!isset($leftPositionArray[$currentPosition][$index])){
							$leftPositionArray[$currentPosition][$index] = $idEvent;
							$eventsByDay[$dayKey][$idEvent]['_overlapIndex'] = $currentPosition;
							$currentPosition = 0;
							$index = 0;
							$eventOK = true;
						} elseif(self::areEventsOverlaping($eventsByDay[$dayKey][$leftPositionArray[$currentPosition][$index]], $eventArray)){
							$currentPosition++;
							$index = 0;
						} else {
							$index++;
						}
					}
				}
			}

				//2nd step: get width of each event
			foreach($eventsOfDay as $idEvent => $eventArray)	{

				foreach($eventsOfDay as $idEventCompare => $eventArrayCompare)	{
					if(!isset($eventsByDay[$dayKey][$idEvent]['_overlapNum']))	{
						$eventsByDay[$dayKey][$idEvent]['_overlapNum'] = 0;
						$eventsByDay[$dayKey][$idEvent]['_maxPosition'] = sizeof($leftPositionArray);
					}

					if(self::areEventsOverlaping($eventArrayCompare, $eventArray) && $idEvent != $idEventCompare)	{
						$eventsByDay[$dayKey][$idEvent]['_overlapNum']++;
					}
				}

				if($eventsByDay[$dayKey][$idEvent]['_overlapNum'] >= sizeof($leftPositionArray))	{
					$eventsByDay[$dayKey][$idEvent]['_overlapNum'] = sizeof($leftPositionArray) - sizeof($leftPositionArray) + 1;
				} else if($eventsByDay[$dayKey][$idEvent]['_overlapNum'] < sizeof($leftPositionArray)) {
					$eventsByDay[$dayKey][$idEvent]['_overlapNum'] = sizeof($leftPositionArray) - $eventsByDay[$dayKey][$idEvent]['_overlapNum'];
				}
			}
		}

		return $eventsByDay;
	}



	/**
	 * Check if two events are overlapping, compare date_start and date_end keys in both arrays
	 *
	 * @param	Array		$event1
	 * @param	Array		$event2
	 * @return	Boolean
	 */
	public static function areEventsOverlaping(array $event1, array $event2) {
		return TodoyuTime::rangeOverlaps($event1['date_start'], $event1['date_end'], $event2['date_start'], $event2['date_end']);
	}



	/**
	 * Get all persons assigned to an event
	 *
	 * @param	Integer 	$idEvent
	 * @param	Boolean 	$getPersonData	get also person data (not only the ID)?
	 * @return	Array
	 */
	public static function getAssignedPersonsOfEvent($idEvent, $getPersonData = false) {
		$idEvent	= intval($idEvent);

		$fields	= '	mm.id_person,
					mm.is_acknowledged';
		$tables	= '	ext_calendar_mm_event_person mm';
		$where	= '	mm.id_event = ' . $idEvent;
		$group	= ' mm.id_person';

		if( $getPersonData ) {
			$fields .= ',p.*';
			$tables	.= ', ext_contact_person p';
			$where	.= ' AND mm.id_person = p.id';
		}

		return Todoyu::db()->getArray($fields, $tables, $where, $group);
	}



	/**
	 * Get all persons assigned to given array of events
	 *
	 * @param	Array $eventIDs
	 * @return	Array
	 */
	public static function getAssignedPersonsOfEvents(array $eventIDs) {
		$eventIDs	= array_unique(TodoyuArray::intval($eventIDs));

		$fields	= 'id_event, id_person';
		$tables	= 'ext_calendar_mm_event_person';
		$where	= 'id_event IN (' . TodoyuArray::intImplode($eventIDs) . ') ';

		$epLinks= Todoyu::db()->getArray($fields, $tables, $where, '', 'id_event', '' );

		$eventPersons = array();
		foreach($epLinks as $epLink) {
			$eventPersons[ $epLink['id_event'] ][] = $epLink['id_person'];
		}

		return $eventPersons;
	}



	/**
	 * Check for conflicts with other events (of non-overbookable type) for the assigned persons if overbooking is not allowed
	 *
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @param	Array		$personIDs
	 * @param	Integer		$idEvent
	 * @return	Array		Infos if conflicts found, empty if no conflicts
	 */
	public static function getOverbookingInfos($dateStart, $dateEnd, array $personIDs, $idEvent = 0) {
		$dateStart	= intval($dateStart);
		$dateEnd	= intval($dateEnd);
		$personIDs	= TodoyuArray::intval($personIDs, true, true);
		$idEvent	= intval($idEvent);

			// Make empty overbooking data
		$overbooked	= array();

			// Get all (not-overbookable / conflicting) events in the duration of the event
		$eventTypes	= TodoyuEventTypeManager::getNotOverbookableTypeIndexes();
		$otherEvents= TodoyuEventManager::getEventsInTimespan($dateStart, $dateEnd, $personIDs, $eventTypes);
			// Remove current event
		unset($otherEvents[$idEvent]);

		foreach($otherEvents as $otherEvent) {
			$assignedPersonIDs	= TodoyuArray::getColumn(TodoyuEventManager::getAssignedPersonsOfEvent($otherEvent['id']), 'id_person');
			$conflicPersonIDs	= array_intersect($personIDs, $assignedPersonIDs);

			foreach($conflicPersonIDs as $idPerson) {
				if( ! isset($overbooked[$idPerson]['person']) ) {
					$overbooked[$idPerson]['person'] = $idPerson;
				}
				$overbooked[$idPerson]['events'][] = $otherEvent;
			}
		}

		return $overbooked;
	}



	/**
	 * Delete event
	 *
	 * @param	Integer		$idEvent
	 */
	public static function deleteEvent($idEvent) {
		$idEvent	= intval($idEvent);

			// Delete event
		Todoyu::db()->deleteRecord(self::TABLE , $idEvent);

			// Remove perso-assignments
		self::removeAllPersonAssignments($idEvent);
	}



	/**
	 * Save a new event
	 *
	 * @param	Array	$data	Eventdata
	 * @return	Integer			ID of event
	 */
	public static function saveEvent(array $data) {
		$xmlPath= 'ext/calendar/config/form/event.xml';

		$idEvent= intval($data['id']);

			// Add empty event
		if( $idEvent === 0 )	{
			$idEvent = self::addEvent(array());
		}

			// Extract person IDs from foreign data array (easier to handle)
		$data['persons'] = TodoyuArray::getColumn(TodoyuArray::assure($data['persons']), 'id');

			// Call save data hooks
		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idEvent);

			// Remove already assigned person
		self::removeAllPersonAssignments($idEvent);

			// If no persons assigned, assign to person "0"
		if( sizeof($data['persons']) === 0 ) {
			$data['persons'][] = 0;
		}

			// Add persons
		self::assignPersonsToEvent($idEvent, $data['persons']);

			// Remove not needed fields
		unset($data['person']);
		unset($data['persons']);

			// Update the event with the definitive data
		self::updateEvent($idEvent, $data);

			// Remove record and query from cache
		self::removeEventFromCache($idEvent);

		return $idEvent;
	}



	/**
	 * Save quick event
	 *
	 * @param	Array		$data
	 * @return	Integer		Event ID
	 */
	public static function saveQuickEvent(array $data) {
		$xmlPath	= 'ext/calendar/config/form/quickevent.xml';
		$idEvent	= self::addEvent(array());

			// Add person
		$data['persons'] = TodoyuArray::getColumn(TodoyuArray::assure($data['person']), 'id');

		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idEvent);

			// If no persons assigned, assign to person "0"
		if( sizeof($data['persons']) === 0 ) {
			$data['persons'][] = 0;
		}

		self::assignPersonsToEvent($idEvent, $data['persons']);

		unset($data['persons']);

			// Update the event with the definitive data
		self::updateEvent($idEvent, $data);

			// Remove record and query from cache
		self::removeEventFromCache($idEvent);

		return $idEvent;
	}



	/**
	 * Add an event to database. Add date_create and id_person_create values
	 *
	 * @param	Array		$data
	 * @return	Integer
	 */
	public static function addEvent(array $data) {
		return TodoyuRecordManager::addRecord(self::TABLE, $data);
	}



	/**
	 * Update an event in the database
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$data
	 * @return	Boolean
	 */
	public static function updateEvent($idEvent, array $data) {
		return TodoyuRecordManager::updateRecord(self::TABLE, $idEvent, $data);
	}



	/**
	 * Assign multiple persons to an event
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$personIDs
	 */
	public static function assignPersonsToEvent($idEvent, array $personIDs) {
		$idEvent	= intval($idEvent);
		$personIDs	= TodoyuArray::intval($personIDs, true, false);

		foreach($personIDs as $idPerson) {
			self::assignPersonToEvent($idEvent, $idPerson);
		}
	}



	/**
	 * Assign a single person to an event
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 */
	public static function assignPersonToEvent($idEvent, $idPerson) {
		$idEvent	= intval($idEvent);
		$idPerson	= intval($idPerson);

		$table	= 'ext_calendar_mm_event_person';
		$data	= array(
			'id_event'			=> $idEvent,
			'id_person'			=> $idPerson,
			'is_acknowledged'	=> personid() == $idPerson ? 1 : 0
		);

		Todoyu::db()->addRecord($table, $data);
	}



	/**
	 * Remove all person-assignments from an event
	 *
	 * @param	Integer		$idEvent
	 */
	public static function removeAllPersonAssignments($idEvent) {
		$idEvent= intval($idEvent);

		$table	= 'ext_calendar_mm_event_person';
		$where	= 'id_event = ' . $idEvent;

		Todoyu::db()->doDelete($table, $where);
	}



	/**
	 * Remove a person assignement for an event
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 */
	public static function removePersonAssignment($idEvent, $idPerson) {
		$idEvent	= intval($idEvent);
		$idPerson	= intval($idPerson);

		$table		= self::TABLE;
		$where		= '	id_event	= ' . $idEvent . ' AND
						id_person	= ' . $idPerson;

		Todoyu::db()->doDelete($table, $where);
	}



	/**
	 * Create new event record in DB
	 *
	 * @return	Integer		Autogenerated ID
	 */
	protected static function createNewEvent()	{
		$insertArray	= array(
			'date_create'	=> NOW,
			'id_person_create'=> personid(),
			'deleted'		=> 0
		);

		return Todoyu::db()->doInsert( self::TABLE , $insertArray );
	}



	/**
	 * Calculate number of day the event starts, relative to shown days of current week-view
	 *
	 * @param	Integer		$start					UNIX timestamp event start
	 * @param	Integer		$end					UNIX timestamp event end
	 * @param	Integer		$tstampFirstShownDay	UNIX timestamp first shown day
	 * @param	Integer		$tstampLastShownDay		UNIX timestamp last shown day
	 * @return	Integer
	 */
	public static function calcEventStartingDayNumInWeek($tstampStart, $tstampFirstShownDay) {
		if ($tstampStart < $tstampFirstShownDay) {
			$dayNum = 0;
		} else {
			$dayNum = TodoyuTime::getWeekdayNum($tstampStart, true);
		}

		return $dayNum;
	}



	/**
	 * Calculate number of day the event ends, relative to shown days of current week-view
	 *
	 * @param	Integer		$start					UNIX timestamp event start
	 * @param	Integer		$end					UNIX timestamp event end
	 * @param	Integer		$tstampFirstShownDay	UNIX timestamp first shown day
	 * @param	Integer		$tstampLastShownDay		UNIX timestamp last shown day
	 * @return	Integer
	 */
	public static function calcEventEndingDayNumInWeek($tstampEnd, $tstampLastShownDay) {
		if ($tstampEnd > $tstampLastShownDay) {
			$dayNum = 7;
		} else {
			$dayNum	= TodoyuTime::getWeekdayNum($tstampEnd, true);
		}

		return $dayNum;
	}



	/**
	 * Remove event from cache
	 *
	 * @param	Integer	$idEvent
	 */
	public static function removeEventFromCache($idEvent) {
		$idEvent = intval($idEvent);

		TodoyuRecordManager::removeRecordCache('TodoyuEvent', $idEvent);
		TodoyuRecordManager::removeRecordQueryCache(self::TABLE, $idEvent);
	}



	/**
	 * Assign persons to an event
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$formData
	 */
	public static function addAssignedEventPersonsAndSendMail($idEvent, array $formData) {
		$idEvent	= intval($idEvent);

		if(array_key_exists('persons', $formData))	{
			$table = 'ext_calendar_mm_event_person';

			foreach($formData['persons'] as $person)	{
				$idPerson	= $person['id'];
				$fields	=	array(
					'id_event'			=> $idEvent,
					'id_person'			=> $idPerson,
					'is_acknowledged'	=> $idPerson == personid() ? 1 : 0,
				);

				Todoyu::db()->doInsert($table, $fields);

				if( $formData['send_notification'] === 1 )	{
					TodoyuCalendarMailer::sendEventNotification($idEvent, $idPerson);
				}
			}

			unset($formData['persons']);
			unset($formData['send_notification']);
		}

		return $formData;
	}



	/**
	 * Set given event acknowledged
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 */
	public static function acknowledgeEvent($idEvent, $idPerson)	{
		$idEvent	= intval($idEvent);
		$idPerson	= intval($idPerson);

		$where 	= '	id_event	= ' . $idEvent . ' AND
					id_person	= ' . $idPerson;

		$update	= array(
			'is_acknowledged' => 1
		);

		Todoyu::db()->doUpdate('ext_calendar_mm_event_person', $where, $update);
	}



	/**
	 * Create new event object with default data
	 *
	 * @param	Integer	$timestamp
	 */
	public static function createNewEventWithDefaultsInCache($timestamp)	{
		$timestamp	= intval($timestamp);
		$defaultData= self::getEventDefaultData($timestamp);

		$idCache	= TodoyuRecordManager::makeClassKey('TodoyuEvent', 0);
		$event		= self::getEvent(0);
		$event->injectData($defaultData);
		TodoyuCache::set($idCache, $event);
	}



	/**
	 * Create event default data
	 *
	 * @param	Integer	$timeStamp
	 * @return	Array
	 */
	protected static function getEventDefaultData($timestamp)	{
		$timestamp	= $timestamp == 0 ? NOW : intval($timestamp);

		if( date('Hi', $timestamp) === '0000' ) {
			$dateStart	= $timestamp + intval(Todoyu::$CONFIG['EXT']['calendar']['default']['timeStart']);
		} else {
			$dateStart	= $timestamp;
		}

		$dateEnd = $dateStart + intval(Todoyu::$CONFIG['EXT']['calendar']['default']['eventDuration']);

		$defaultData = array(
			'id'			=>	0,
			'date_start'	=>	$dateStart,
			'date_end'		=>	$dateEnd,
			'persons' 		=> array(
				TodoyuAuth::getPerson()->getTemplateData()
			)
		);

		return $defaultData;
	}



	/**
	 * Add default context menu item for event
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$items
	 * @return	Array
	 */
	public static function getContextMenuItems($idEvent, array $items) {
		$idEvent= intval($idEvent);

		$allowed= array();
		$own	= Todoyu::$CONFIG['EXT']['calendar']['ContextMenu']['Event'];

			// Option: show event
		if( TodoyuEventRights::isSeeAllowed($idEvent) ) {
			$allowed['show'] = $own['show'];
		}

			// Options: edit event, delete event
			// Edit event: right:editAll OR is assigned and right editAssigned OR is creater
		if( TodoyuEventRights::isEditAllowed($idEvent) ) {
			$allowed['edit']	= $own['edit'];
			$allowed['delete']	= $own['remove'];
		}

			// Option: add event
		if( TodoyuEventRights::isAddAllowed() ) {
			$allowed['add'] = $own['add'];
		}

		$items = array_merge_recursive($items, $allowed);

		return $items;
	}



	/**
	 * Get event context menu items for display in portal
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$items
	 * @return	Array
	 */
	public static function getContextMenuItemsPortal($idEvent, array $items)	{
		$idEvent = intval($idEvent);

		$own = Todoyu::$CONFIG['EXT']['calendar']['ContextMenu']['EventPortal'];

		foreach($own['show']['submenu'] as $key => $config)	{
			$eventStart	= TodoyuEventManager::getEvent($idEvent)->get('date_start');
			$own['show']['submenu'][$key]['jsAction'] = str_replace('#DATE#', $eventStart, $config['jsAction']);
		}

		$items = array_merge_recursive($items, $own);

		return $items;
	}



	/**
	 * Hook when saving event data. Modify data looking at the event type
	 *
	 * @param	Array		$data
	 * @param	Integer		$idEvent
	 * @return	Array
	 */
	public static function hookSaveEvent(array $data, $idEvent) {
			// Birthday
		if( $data['eventtype'] == EVENTTYPE_BIRTHDAY ) {
			$data['date_start']	= TodoyuTime::getStartOfDay($data['date_start']);
			$data['date_end']	= $data['date_start'] + TodoyuTime::SECONDS_HOUR; // Fix, so event is in day period
			$data['person']		= array();
			$data['is_dayevent']= 1;
		}

			// Holiday
		if ( $data['eventtype'] == EVENTTYPE_VACATION ) {
			$data['is_dayevent']= 1;
		}

		return $data;
	}

}

?>