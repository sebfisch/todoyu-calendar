<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
	 * @var	String		Default table for database requests
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
			$where .= ' AND e.eventtype IN(' . implode(',', $eventTypes) . ')';
		}


			// Assigned persons
		if( ! allowed('calendar', 'event:seeAll') ) {
			$where .= ' AND mmeu.id_person IN(' . personid() . ',0)';
		}

		return Todoyu::db()->getArray($fields, $tables, $where, $group, $order, $limit, $indexField);
	}



	/**
	 * Group the events in sub array. The key for each sub array is a date-key (YYYYMMDD)
	 * An event appears in each sub array, the event is running on
	 *
	 * @param	Array		$events			Array of event records
	 * @param	Integer		$dateStart		Date of first day group
	 * @param	Integer		$dateEnd		Date of last day group
	 * @return	Array		Events grouped by date-key
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
	 * Add overlapping information to the event records
	 * Add:
	 *  - _indexColumn: The index of the column the event is in. The columns are filled from left to right. New columns are added if necessary
	 *  - _numColumns:  The number of columns added. This information is redundant in all events
	 *
	 * @param	Array		$eventsByDay		Events grouped by day. The array index is the date key
	 * @return	Array		The same array with the two extra keys
	 */
	public static function addOverlapInformationToEvents(array $eventsByDay) {
			// Check positions and overlapping for each day
		foreach($eventsByDay as $dayKey => $eventsOfDay) {
			$columns	= array();

				// 1st step: get left position of each event
			foreach($eventsOfDay as $eventIndex => $event) {
					// Just add the first event of the day
				if( empty($columns) ) {
					$columns[0][] = $eventIndex;
					$eventsByDay[$dayKey][$eventIndex]['_indexColumn']	= 0;
				} else {
					$fittingColumnFound	= false;

					foreach($columns as $columnIndex => $column) {
						$overlaps	= false;

						foreach($column as $columnEventIndex) {
								// Check if the event overlaps with the current column element
							if( self::areEventsOverlaping($eventsByDay[$dayKey][$columnEventIndex], $event) ) {
									// Overlapping in this column, try next
								$overlaps	= true;
								break;
							}
						}

							// Event does not overlap with another in this column
						if( $overlaps === false ) {
								// Mark as found (no overlapping)
							$fittingColumnFound = true;
								// Stop looping over the current column
							break;
						}
					}

						// No fitting column found. Increment column counter (= add to new column)
					if( $fittingColumnFound === false ) {
							// Next column = new column
						$columnIndex++;
					}

						// Add eventIndex to current column which has no overlapping
					$columns[$columnIndex][] = $eventIndex;

					$eventsByDay[$dayKey][$eventIndex]['_indexColumn']	= $columnIndex;
				}
			}

				// Set number columns for each event
			$numColumns	= sizeof($columns);
			foreach($eventsOfDay as $eventIndex => $event) {
				$eventsByDay[$dayKey][$eventIndex]['_numColumns']		= $numColumns;
			}
		}

		return $eventsByDay;
	}



	/**
	 * Check whether two events are overlapping, compare date_start and date_end keys in both arrays
	 * An event uses at least a window for 30 minutes. So if an event is shorter, extend it for comparison
	 *
	 * @param	Array		$event1
	 * @param	Array		$event2
	 * @return	Boolean
	 */
	public static function areEventsOverlaping(array $event1, array $event2) {
			// Make sure event1 lasts at least 30 min
		if( ($event1['date_end'] - $event1['date_start']) < CALENDAR_EVENT_MIN_DURATION ) {
			$event1['date_end'] = $event1['date_start'] + CALENDAR_EVENT_MIN_DURATION;
		}

			// Make sure event2 lasts at least 30 min
		if( ($event2['date_end'] - $event2['date_start']) < CALENDAR_EVENT_MIN_DURATION ) {
			$event2['date_end'] = $event2['date_start'] + CALENDAR_EVENT_MIN_DURATION;
		}

		return TodoyuTime::rangeOverlaps($event1['date_start'], $event1['date_end'], $event2['date_start'], $event2['date_end']);
	}



	/**
	 * Get all persons assigned to an event
	 *
	 * @param	Integer 	$idEvent
	 * @param	Boolean 	$getPersonData		get also person data (not only the ID)?
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
	 * @return	Array		empty if no conflicts, information if conflicted 
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
				// Don't check for conflicts if is dayevent
			if( $otherEvent['is_dayevent'] == 1 ) {
				continue;
			}

			$assignedPersons	= TodoyuEventManager::getAssignedPersonsOfEvent($otherEvent['id']);
			$assignedPersonIDs	= TodoyuArray::getColumn($assignedPersons, 'id_person');
			$conflictedPersonIDs= array_intersect($personIDs, $assignedPersonIDs);

			foreach($conflictedPersonIDs as $idPerson) {
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

			// Remove person-assignments
		self::removeAllPersonAssignments($idEvent);
	}



	/**
	 * Save a new event
	 *
	 * @param	Array	$data		event data
	 * @return	Integer				ID of event
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

			// Adjust date end for a reminder
		if( $data['eventtype'] == EVENTTYPE_REMINDER ) {
			$data['date_end'] = intval($data['date_start']);
		}

			// Call hooked save data functions
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

			// Changes dates if is dayevent
		if( intval($data['is_dayevent']) === 1 ) {
			$data['date_start']	= TodoyuTime::getStartOfDay($data['date_start']);
			$data['date_end']	= TodoyuTime::getEndOfDay($data['date_end']);
		}

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
	 * @return	Integer		event ID
	 */
	public static function saveQuickEvent(array $data) {
		$xmlPath	= 'ext/calendar/config/form/quickevent.xml';
		
			// Create an empty event
		$idEvent	= self::addEvent();

			// Add person
		$data['persons'] = TodoyuArray::getColumn(TodoyuArray::assure($data['persons']), 'id');

			// Call hooked save data functions
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
	public static function addEvent(array $data = array()) {
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
	 * Remove all person assignments from an event
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
	 * Remove the given person's assignment from the given event
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 */
	public static function removePersonAssignment($idEvent, $idPerson) {
		$idEvent	= intval($idEvent);
		$idPerson	= intval($idPerson);

		$table		= self::TABLE;
		$where		= '		id_event	= ' . $idEvent .
					  ' AND	id_person	= ' . $idPerson;

		Todoyu::db()->doDelete($table, $where);
	}



	/**
	 * Create new event record in DB
	 *
	 * @return	Integer		Auto-generated ID
	 */
	protected static function createNewEvent()	{
		$insertArray	= array(
			'date_create'		=> NOW,
			'id_person_create'	=> personid(),
			'deleted'			=> 0
		);

		return Todoyu::db()->doInsert( self::TABLE , $insertArray );
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
				$fields	= array(
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

		$where 	= '		id_event	= ' . $idEvent .
				  ' AND	id_person	= ' . $idPerson;

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
		$idEvent	= intval($idEvent);
		$event		= TodoyuEventManager::getEvent($idEvent);
		$dateStart	= $event->getStartDate();

		$ownItems			= Todoyu::$CONFIG['EXT']['calendar']['ContextMenu']['Event'];
		$ownItems['show']	= Todoyu::$CONFIG['EXT']['calendar']['ContextMenu']['EventPortal']['show'];

		unset($ownItems['add']);

		if( ! TodoyuEventRights::isEditAllowed($idEvent) ) {
			unset($ownItems['edit']);
			unset($ownItems['delete']);
		}

		foreach($ownItems['show']['submenu'] as $key => $config)	{
			$ownItems['show']['submenu'][$key]['jsAction'] = str_replace('#DATE#', $dateStart, $config['jsAction']);
		}

		$items = array_merge_recursive($items, $ownItems);

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
		switch( $data['eventtype'] ) {
				// Birthday
			case EVENTTYPE_BIRTHDAY:
				$data['date_start']	= TodoyuTime::getStartOfDay($data['date_start']);
				$data['date_end']	= $data['date_start'] + TodoyuTime::SECONDS_HOUR; // Fix, so event is in day period
				$data['person']		= array();
				$data['is_dayevent']= 1;
				break;

				// Holiday
			case EVENTTYPE_VACATION:
				$data['is_dayevent']= 1;
				break;

				// Reminder
			case EVENTTYPE_REMINDER:
				$data['date_end']	= $data['date_start'];
				break;
		}

		return $data;
	}

}

?>