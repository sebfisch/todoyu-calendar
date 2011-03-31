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
 * Event Manager
 *
 * @package			Todoyu
 * @subpackage		Calendar
 */
class TodoyuCalendarEventManager {

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
		$form->getFieldset('buttons')->getField('cancel')->setAttribute('onclick', 'Todoyu.Popups.close(\'quickcreate\')');

		return $form;
	}



	/**
	 * Get event object
	 *
	 * @param	Integer		$idEvent
	 * @return	TodoyuCalendarEvent
	 */
	public static function getEvent($idEvent) {
		$idEvent	= intval($idEvent);

		return TodoyuRecordManager::getRecord('TodoyuCalendarEvent', $idEvent);
	}



	/**
	 * Get full label of event
	 *
	 * @param	Integer		$idEvent
	 * @param	Boolean		$withType
	 * @return	String
	 */
	public static function getEventFullLabel($idEvent, $withType = true) {
		return self::getEvent($idEvent)->getFullLabel($withType);
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
	 * @param	Mixed		$dayEvents				null = both types, true = only full-day events, false = only non full-day events
	 * @param	String		$indexField
	 * @return	Array
	 */
	public static function getEventsInTimespan($dateStart, $dateEnd, array $persons = array(), array $eventTypes = array(), $dayEvents = null, $indexField = 'id') {
		$dateStart	= intval($dateStart);
		$dateEnd	= intval($dateEnd);
		$persons	= TodoyuArray::intval($persons, true, true);

		$tables	= 	self::TABLE  . ' e,
					ext_calendar_mm_event_person mmep';

		$fields	= '	e.*,
					mmep.id_person,
					mmep.is_acknowledged,
					mmep.is_updated,
					e.date_end - e.date_start as duration';

			// We add or subtract 1 second to prevent direct overlapping collision
			// Ex: event1: 10-11, event2: 11-12 - BETWEEN would find both event at 11:00:00
		$where	= '		e.id		= mmep.id_event
					AND e.deleted	= 0
					AND (
							e.date_start 	BETWEEN ' . ($dateStart + 1) . ' AND ' . ($dateEnd - 1) . '
						OR	e.date_end 		BETWEEN ' . ($dateStart + 1) . ' AND ' . ($dateEnd - 1) . '
						OR (e.date_start < ' . ($dateStart + 1) . ' AND e.date_end > ' . ($dateEnd - 1) . ')
					)';

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

			// Not allowed to see all events? Limit to own events!
		if( ! allowed('calendar', 'event:seeAll') ) {
			$where .= ' AND mmep.id_person IN(' . personid() . ')';
		} elseif( sizeof($persons) > 0 ) {
				// Limit to given assigned persons
			$where	.= ' AND mmep.id_person IN(' . implode(',', $persons) . ')';
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
							if( self::areEventsOverlapping($eventsByDay[$dayKey][$columnEventIndex], $event) ) {
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
	public static function areEventsOverlapping(array $event1, array $event2) {
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
		$eventIDs= array_unique(TodoyuArray::intval($eventIDs));
		$persons = array();

		if( sizeof($eventIDs) > 0 ) {
			$fields	= 'id_event, id_person';
			$tables	= 'ext_calendar_mm_event_person';
			$where	= 'id_event IN (' . TodoyuArray::intImplode($eventIDs) . ') ';

			$epLinks= Todoyu::db()->getArray($fields, $tables, $where, '', 'id_event', '' );

			foreach($epLinks as $epLink) {
				$persons[ $epLink['id_event'] ][] = $epLink['id_person'];
			}
		}

		return $persons;
	}



	/**
	 * Get details of persons which could receive an event email
	 *
	 * @param	Integer		$idEvent
	 * @param	Boolean		$getPersonsDetails		(false: get only ID and email)
	 * @return	Array
	 */
	public static function getEmailReceivers($idEvent, $getPersonsDetails = true) {
		$idEvent		= intval($idEvent);

		$persons	= self::getAssignedPersonsOfEvent($idEvent, true);

			// Reduce persons data to contain only ID and email, use id_person as new key
		$reformConfig	= array(
			'id_person'	=> 'id_person',
			'email'		=> 'email'
		);
		$persons	= TodoyuArray::reformWithFieldAsIndex($persons, $reformConfig, $getPersonsDetails, 'id_person');

			// Remove all persons w/o email address
		foreach($persons as $idPerson => $personData) {
			if( empty($personData['email']) ) {
				unset($persons[$idPerson]);
			}
		}

		return $persons;
	}



	/**
	 * Check whether any of the participants (but the given to be excluded) of the given event has an email address stored
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$excludedPersonIDs
	 * @return	Boolean
	 */
	public static function hasAnyEventPersonAnEmailAddress($idEvent, array $excludedPersonIDs = array()) {
		$idEvent			= intval($idEvent);
		$excludedPersonIDs	= count($excludedPersonIDs) > 0 ? TodoyuArray::intval($excludedPersonIDs) : false;

		$persons			= self::getEmailReceivers($idEvent, false);

			// Remove all persons who are excluded
		foreach($persons as $idPerson => $personData) {
			if( in_array($idPerson, $excludedPersonIDs) ) {
				unset($persons[$idPerson]);
			}
		}

		return count($persons) > 0;
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

		if( $dateEnd >= $dateStart ) {
				// Get all (not-overbookable / conflicting) events in the duration of the event
			$eventTypes	= TodoyuCalendarEventTypeManager::getNotOverbookableTypeIndexes();
			$otherEvents= TodoyuCalendarEventManager::getEventsInTimespan($dateStart, $dateEnd, $personIDs, $eventTypes);
				// Remove current event
			unset($otherEvents[$idEvent]);

			foreach($otherEvents as $otherEvent) {
					// Don't check for conflicts if is day-event as long its not an absence
				$absenceEventTypes	= TodoyuArray::assure(Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_ABSENCE']);

				if( $otherEvent['is_dayevent'] == 1 && ! in_array($otherEvent['eventtype'], $absenceEventTypes)) {
					continue;
				}

				$assignedPersons	= TodoyuCalendarEventManager::getAssignedPersonsOfEvent($otherEvent['id']);
				$assignedPersonIDs	= TodoyuArray::getColumn($assignedPersons, 'id_person');
				$conflictedPersonIDs= array_intersect($personIDs, $assignedPersonIDs);

				foreach($conflictedPersonIDs as $idPerson) {
					if( ! isset($overbooked[$idPerson]['person']) ) {
						$overbooked[$idPerson]['person'] = $idPerson;
					}

					if( count($overbooked[$idPerson]['events']) < Todoyu::$CONFIG['EXT']['calendar']['maxShownOverbookingsPerPerson'] ) {
						$overbooked[$idPerson]['events'][] = $otherEvent;
					}
				}
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

		$where	= 'id = ' . $idEvent;

		Todoyu::db()->setDeleted(self::TABLE, $where);

//			// Delete event
//		Todoyu::db()->deleteRecord(self::TABLE , $idEvent);
//
//			// Remove person-assignments
//		self::removeAllPersonAssignments($idEvent);
	}



	/**
	 * Save a new event
	 *
	 * @param	Array	$data		event data
	 * @return	Integer				ID of event
	 */
	public static function saveEvent(array $data) {
		$xmlPath= 'ext/calendar/config/form/event.xml';

		$idEvent	= intval($data['id']);
		$isNewEvent	= $idEvent === 0;

			// Add empty event
		if( $idEvent === 0 ) {
			$idEvent = self::addEvent(array());
		}

			// Remove mail fields (not stored directly in event record)
		unset($data['sendasemail']);
		unset($data['emailreceivers']);

			// Extract person IDs from foreign data array (easier to handle)
		$data['persons'] = TodoyuArray::getColumn(TodoyuArray::assure($data['persons']), 'id');

			// Adjust date end for events of type reminder
		if( $data['eventtype'] == EVENTTYPE_REMINDER ) {
			$data['date_end'] = intval($data['date_start']);
		}

			// Call hooked save data functions
		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idEvent, array('newEvent'=>$isNewEvent));

			// Remove already assigned person
		self::removeAllPersonAssignments($idEvent);

			// If no persons assigned, assign to person "0"
		if( sizeof($data['persons']) === 0 ) {
			$data['persons'][] = 0;
		}

			// Add persons
		self::assignPersonsToEvent($idEvent, $data['persons'], ! $isNewEvent);

			// Remove not needed fields
		unset($data['person']);
		unset($data['persons']);

			// Changes dates if is day-event
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
	 * Reset acknowledge flag of event assignment. Event will be show as "new"
	 * By default, the current user will not be reset, he should already now
	 *
	 * @param	Integer		$idEvent
	 * @param	Boolean		$resetForCurrentUser		Reset also for current user
	 */
	public static function resetAcknowledgment($idEvent, $resetForCurrentUser = false) {
		$idEvent= intval($idEvent);
		$table	= 'ext_calendar_mm_event_person';
		$update	= array(
			'is_acknowledged'	=> 0
		);
		$where	= '	id_event = ' . $idEvent;

		if( ! $resetForCurrentUser ) {
			$where .= ' AND	id_person != ' . personid();
		}

		Todoyu::db()->doUpdate($table, $where, $update);
	}



	/**
	 * Save quick event
	 *
	 * @param	Array		$data
	 * @return	Integer		event ID
	 */
	public static function saveQuickEvent(array $data) {
		$xmlPath	= 'ext/calendar/config/form/event.xml';

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
		self::resetAcknowledgment($idEvent);

			// Remove record and query from cache
		self::removeEventFromCache($idEvent);

		return $idEvent;
	}



	/**
	 * Event save hook. Send emails
	 *
	 * @param	Array		$data
	 * @param	Integer		$idEvent
	 * @param	Array		$params
	 * @return	Array
	 */
	public static function hookSaveEventSendEmail(array $data, $idEvent, array $params = array()) {
		$sendAsEmail	= intval($data['sendasemail']) === 1;

		if( $sendAsEmail ) {
			$mailReceiverPersonIDs	= array_unique(TodoyuArray::intExplode(',', $data['emailreceivers'], true, true));

			if( sizeof($mailReceiverPersonIDs) > 0 ) {
				$operationID	= $params['newEvent'] ? OPERATIONTYPE_RECORD_UPDATE : OPERATIONTYPE_RECORD_UPDATE;

				$sent	= TodoyuCalendarEventMailer::sendEmails($idEvent, $mailReceiverPersonIDs, $operationID);
				if( $sent ) {
					TodoyuCalendarEventMailManager::saveMailsSent($idEvent, $mailReceiverPersonIDs);
					/**
					 * @todo	Sending headers here is bad!
					 */
					TodoyuHeader::sendTodoyuHeader('sentEmail', true);
				}
			}
		}

		unset($data['sendasemail']);
		unset($data['emailreceivers']);

		return $data;
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
	 * Move an event to a new start date
	 *
	 * @param	Integer				$idEvent
	 * @param	Integer				$newStartDate
	 * @param	String				$mode
	 * @param	Boolean|Array		True or array of overbooking infos
	 */
	public static function moveEvent($idEvent, $newStartDate, $mode, $overbookingConfirmed = false) {
		$event	= self::getEvent($idEvent);

		if( $mode === 'month' ) {
			$newStart	= TodoyuTime::getStartOfDay($newStartDate);
			$startDay	= TodoyuTime::getStartOfDay($event->getStartDate());
			$offset		= $newStart - $startDay;
			$dateStart	= $event->getStartDate() + $offset;
			$dateEnd	= $event->getEndDate() + $offset;
		} else {
			$offset		= $newStartDate - $event->getStartDate();
			$dateStart	= $newStartDate;
			$dateEnd	= $event->getEndDate() + $offset;
		}

		$data	= array(
			'date_start'=> $dateStart,
			'date_end'	=> $dateEnd
		);

		if( ! $overbookingConfirmed || ! TodoyuCalendarManager::isOverbookingAllowed() ) {
				// Check for overbookings and request confirmation or reset event
			$overbookedInfos= self::getOverbookingInfos($dateStart, $dateEnd, $event->getAssignedPersonIDs(), $idEvent);

			if( sizeof($overbookedInfos) > 0 ) {
				$errorMessages = array();
				foreach($overbookedInfos as $idPerson => $infos) {
					foreach($infos['events'] as $event) {
						$errorMessages[] = Label('calendar.event.error.personsOverbooked') . ' ' . TodoyuContactPersonManager::getPerson($idPerson)->getFullName();
					}
				}

				return array_unique($errorMessages);
			}
		}

		self::updateEvent($idEvent, $data);

		return true;
	}



	/**
	 * Assign multiple persons to an event
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$personIDs
	 * @param	Boolean		$isUpdate
	 */
	public static function assignPersonsToEvent($idEvent, array $personIDs, $isUpdate = false) {
		$idEvent	= intval($idEvent);
		$personIDs	= TodoyuArray::intval($personIDs, true, true);

		foreach($personIDs as $idPerson) {
			self::assignPersonToEvent($idEvent, $idPerson, $isUpdate);
		}
	}



	/**
	 * Assign a single person to given event
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @param	Boolean		$isUpdate
	 */
	public static function assignPersonToEvent($idEvent, $idPerson, $isUpdate = false) {
		$idEvent	= intval($idEvent);
		$idPerson	= intval($idPerson);

		$table	= 'ext_calendar_mm_event_person';
		$data	= array(
			'id_event'			=> $idEvent,
			'id_person'			=> $idPerson,
			'is_acknowledged'	=> personid() == $idPerson ? 1 : 0,
			'is_updated'		=> $isUpdate ? 1 : 0
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
	protected static function createNewEvent() {
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

		TodoyuRecordManager::removeRecordCache('TodoyuCalendarEvent', $idEvent);
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

		if( array_key_exists('persons', $formData) ) {
			$table = 'ext_calendar_mm_event_person';

			foreach($formData['persons'] as $person) {
				$idPerson	= $person['id'];
				$fields	= array(
					'id_event'			=> $idEvent,
					'id_person'			=> $idPerson,
					'is_acknowledged'	=> $idPerson == personid() ? 1 : 0,
				);

				Todoyu::db()->doInsert($table, $fields);

				if( $formData['send_notification'] === 1 ) {
					$operationID	= OPERATIONTYPE_RECORD_CREATE;
					TodoyuCalendarEventMailer::sendEmails($idEvent, array($idPerson), $operationID);
				}
			}

			unset($formData['persons']);
			unset($formData['send_notification']);
		}

		return $formData;
	}



	/**
	 * Set given event acknowledged by given person
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 */
	public static function acknowledgeEvent($idEvent, $idPerson = 0) {
		$idEvent	= intval($idEvent);
		$idPerson	= personid($idPerson);

		$where 	= '		id_event	= ' . $idEvent .
				  ' AND	id_person	= ' . $idPerson;

			// Store also timestamp to be able to detect unacknowledged modifications of events
		$update	= array(
			'is_acknowledged'	=> 1
		);

		Todoyu::db()->doUpdate('ext_calendar_mm_event_person', $where, $update);
	}



	/**
	 * Create new event object with default data
	 *
	 * @param	Integer	$timestamp
	 */
	public static function createNewEventWithDefaultsInCache($timestamp) {
		$timestamp	= intval($timestamp);
		$defaultData= self::getEventDefaultData($timestamp);

		$idCache	= TodoyuRecordManager::makeClassKey('TodoyuCalendarEvent', 0);
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
	protected static function getEventDefaultData($timestamp) {
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
			'eventtype'		=> EVENTTYPE_GENERAL,
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
		if( TodoyuCalendarEventRights::isSeeDetailsAllowed($idEvent) ) {
			$allowed['show'] = $own['show'];
		}

			// Options: edit event, delete event
			// Edit event: right:editAll OR is assigned and right editAssigned OR is creator
		if( TodoyuCalendarEventRights::isEditAllowed($idEvent) ) {
			$allowed['edit']	= $own['edit'];
			$allowed['delete']	= $own['remove'];
		}

			// Option: add event
		if( TodoyuCalendarEventRights::isAddAllowed() ) {
			$allowed['add'] = $own['add'];
		}

			// Option: popup reminder
		if( 1 ) {
			$allowed['reminderemail']	= $own['reminderemail'];
		}

			// Option: email reminder
		if( 1 ) {
			$allowed['reminderpopup']	= $own['reminderpopup'];
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
	public static function getContextMenuItemsPortal($idEvent, array $items) {
		$idEvent	= intval($idEvent);
		$event		= TodoyuCalendarEventManager::getEvent($idEvent);
		$dateStart	= $event->getStartDate();

		$ownItems			= Todoyu::$CONFIG['EXT']['calendar']['ContextMenu']['Event'];
		$ownItems['show']	= Todoyu::$CONFIG['EXT']['calendar']['ContextMenu']['EventPortal']['show'];

		unset($ownItems['add']);

		if( ! TodoyuCalendarEventRights::isEditAllowed($idEvent) ) {
			unset($ownItems['edit']);
			unset($ownItems['delete']);
		}

		foreach($ownItems['show']['submenu'] as $key => $config) {
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



	/**
	 * Check if user has access to view or edit tab
	 * If not, change tab to "day"
	 *
	 * @param	String		$tab
	 * @param	Integer		$idEvent
	 * @return	String		Allowed tab
	 */
	public static function checkTabAccess($tab, $idEvent) {
		$tab	= trim($tab);
		$idEvent= intval($idEvent);

			// Check for edit rights
		if( $tab === 'edit' ) {
			if( ! TodoyuCalendarEventRights::isEditAllowed($idEvent) ) {
				$tab = 'day';
			}
		}

			// Check for view rights
		if( $tab === 'view' ) {
			if( ! TodoyuCalendarEventRights::isSeeDetailsAllowed($idEvent) ) {
				$tab = 'day';
			}
		}

		return $tab;
	}



	/**
	 * Get amount of days which intersect the event duration
	 *
	 * @param	Integer		$idEvent
	 * @return	Integer
	 */
	public static function getAmountDaysIntersected($idEvent) {
		$event	= self::getEvent($idEvent);
		$days	= TodoyuTime::getDayTimestampsInRange($event->getStartDate(), $event->getEndDate());

		return count($days);
	}



	/**
	 * Check event for overbookings (regardless whether allowed) and render warning message content if any found
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$formData
	 * @param	Boolean		$forPopup			For popup or annotation inside the form?
	 * @param	Boolean		$convertDates		Dates (start/end) needed to be parsed from string, or are timestamps already?
	 * @param	Boolean		$isDragAndDrop
	 * @return	String
	 */
	public static function getOverbookingWarning($idEvent, array $formData, $forPopup = true, $convertDates = true, $isDragAndDrop = false) {
		$idEvent	= intval($idEvent);
		$dateStart	= ( $convertDates ) ? TodoyuTime::parseDate($formData['date_start']) : $formData['date_start'];
		$dateEnd	= ( $convertDates ) ? TodoyuTime::parseDate($formData['date_end']) : $formData['date_end'];
		$personIDs	= TodoyuArray::getColumn($formData['persons'], 'id');

		$warning		= '';
		$overbookedInfos= TodoyuCalendarEventManager::getOverbookingInfos($dateStart, $dateEnd, $personIDs, $idEvent);

		if( sizeof($overbookedInfos) > 0 ) {
			$tmpl	= 'ext/calendar/view/overbooking-info.tmpl';
			$formData	= array(
				'idEvent'		=> $idEvent,
				'overbooked'	=> $overbookedInfos
			);

			if( $forPopup === true ) {
					// Render for display in popup
				if( ! $isDragAndDrop ) {
						// Regular edit of event
					$xmlPath= 'ext/calendar/config/form/overbooking-warning.xml';
					$form	= TodoyuFormManager::getForm($xmlPath);
				} else {
						// Modification via drag and drop
					$xmlPath= 'ext/calendar/config/form/overbooking-warning-drop.xml';
					$form	= TodoyuFormManager::getForm($xmlPath);
				}
				$buttonsForm	= $form->render();

				$tmpl	= 'ext/calendar/view/overbooking-warning.tmpl';
				$formData['buttonsFieldset']	= $buttonsForm;
			}

			$warning	= render($tmpl, $formData);
		}

		return $warning;
	}



	/**
	 * Check overbooking warning for dragged & dropped event
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$dateStart
	 * @return	String
	 */
	public static function getOverbookingWarningAfterDrop($idEvent, $dateStart) {
		$idEvent	= intval($idEvent);
		$dateStart	= intval($dateStart);

			// Fetch original event data
		$event 	= TodoyuCalendarEventManager::getEvent($idEvent);

		$eventData				= $event->getData();
		$eventData['persons']	= $event->getAssignedPersonsData();
			// Set modified time data
		$eventData['date_start']= $dateStart;
		$eventData['date_end']	= $dateStart + $event->getDuration();

		return self::getOverbookingWarning($idEvent, $eventData, true, false, true);
	}

}

?>