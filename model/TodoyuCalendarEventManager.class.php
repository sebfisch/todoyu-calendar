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
	 * @return	TodoyuForm
	 */
	public static function getQuickCreateForm($idEvent = 0) {
		$idEvent	= intval($idEvent);

			// Create form object
		$xmlPath= 'ext/calendar/config/form/event.xml';
		$form	= TodoyuFormManager::getForm($xmlPath, $idEvent);

		TodoyuCalendarEventManager::createNewEventWithDefaultsInCache(NOW);
		$event	= TodoyuCalendarEventManager::getEvent(0);
		$data	= $event->getTemplateData(true, false, true);

			// Call hooked load functions
		$data	= TodoyuFormHook::callLoadData($xmlPath, $data, $idEvent);
		$form->setFormData($data);

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
		if( ! Todoyu::allowed('calendar', 'event:seeAll') ) {
			$where .= ' AND mmep.id_person IN(' . Todoyu::personid() . ')';
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
	 * @param	Boolean 	$getPersonData		Get also person data?
	 * @param	Boolean 	$getRemindersData	Get also persons reminders data?
	 * @return	Array
	 */
	public static function getAssignedPersonsOfEvent($idEvent, $getPersonData = false, $getRemindersData = false) {
		$idEvent	= intval($idEvent);

		$fields	= '	 mm.id_person
					,mm.is_acknowledged';
		$tables		= '	ext_calendar_mm_event_person mm';
		$where		= '	mm.id_event = ' . $idEvent;
		$group		= ' mm.id_person';
		$indexField	= 'id_person';

		if( $getPersonData ) {
			$fields .= ', p.*';
			$tables	.= ', ext_contact_person p';
			$where	.= ' AND mm.id_person = p.id';
		}

		if( $getRemindersData ) {
			$fields	.= ', mm.date_remindemail
						, mm.date_remindpopup';
		}

		return Todoyu::db()->getArray($fields, $tables, $where, $group, '', '', $indexField);
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

			$epLinks= Todoyu::db()->getArray($fields, $tables, $where, '', 'id_event', '');

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
		$idEvent	= intval($idEvent);

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
					// Don't check for conflicts if is all-day event as long its not an absence
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

		TodoyuRecordManager::deleteRecord(self::TABLE, $idEvent);

		TodoyuHookManager::callHook('calendar', 'event.delete', array($idEvent));
	}



	/**
	 * Save a new event
	 *
	 * @param	Array	$data		event data
	 * @return	Integer				ID of event
	 */
	public static function saveEvent(array $data) {
		$xmlPath= 'ext/calendar/config/form/event.xml';

		$idEvent			= intval($data['id']);
		$isNewEvent			= $idEvent === 0;
		$advanceTimeEmail	= intval($data['reminder_email']);
		$advanceTimePopup	= intval($data['reminder_popup']);

			// Extract person IDs from foreign data array (easier to handle)
		$personIDs 	= TodoyuArray::getColumn(TodoyuArray::assure($data['persons']), 'id');
		$personIDs	= TodoyuArray::intval($personIDs, true, true);

			// Add empty event
		if( $idEvent === 0 ) {
			$idEvent 		= self::addEvent();
			$dateStartOld	= 0;
		} else {
			$event			= self::getEvent($idEvent);
			$dateStartOld	= $event->getStartDate();
		}

			// Adjust date end for events of type reminder
		if( $data['eventtype'] == EVENTTYPE_REMINDER ) {
			$data['date_end'] = intval($data['date_start']);
		}

			// Call hooked save data functions
		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idEvent, array('newEvent'	=> $isNewEvent));

			// Remove not needed fields
		unset($data['persons']);
		unset($data['reminder_email']);
		unset($data['reminder_popup']);
		unset($data['sendasemail']);
		unset($data['emailreceivers']);

			// Update the event with the definitive data
		self::updateEvent($idEvent, $data);
			// Remove record and query from cache
		self::removeEventFromCache($idEvent);
			// Save person assignments
		self::saveAssignments($idEvent, $personIDs, $dateStartOld);
			// Set reminder for all users
		self::updateAssignmentRemindersForCurrentPerson($idEvent, $advanceTimeEmail, $advanceTimePopup);

		return $idEvent;
	}



	/**
	 * Event save hook. Send emails
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$receiverIDs
	 * @param	Boolean		$isNewEvent
	 * @return	Boolean
	 */
	public static function sendEventAsEmail($idEvent, $receiverIDs, $isNewEvent = false) {
		$mailReceiverPersonIDs	= array_unique(TodoyuArray::intval($receiverIDs, true, true));

		$sent	= false;

		if( sizeof($mailReceiverPersonIDs) > 0 ) {
			$operationID	= $isNewEvent ? OPERATIONTYPE_RECORD_CREATE : OPERATIONTYPE_RECORD_UPDATE;

			$sent	= TodoyuCalendarEventMailer::sendEmails($idEvent, $mailReceiverPersonIDs, $operationID);
			if( $sent ) {
				TodoyuCalendarEventMailManager::saveMailsSent($idEvent, $mailReceiverPersonIDs);
			}
		}

		return $sent;
	}



	/**
	 * Add an event to database. Add date_create and id_person_create values
	 *
	 * @param	Array		$data
	 * @return	Integer
	 */
	public static function addEvent(array $data = array()) {
		$idEvent = TodoyuRecordManager::addRecord(self::TABLE, $data);

		TodoyuHookManager::callHook('calendar', 'event.add', array($idEvent));

		return $idEvent;
	}



	/**
	 * Update an event in the database
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$data
	 */
	public static function updateEvent($idEvent, array $data) {
		TodoyuRecordManager::updateRecord(self::TABLE, $idEvent, $data);

		self::removeEventFromCache($idEvent);

		TodoyuHookManager::callHook('calendar', 'event.update', array($idEvent, $data));
	}



	/**
	 * Move an event to a new start date
	 *
	 * @param	Integer				$idEvent
	 * @param	Integer				$newStartDate
	 * @param	String				$mode
	 * @param	Boolean|Array		$overbookingConfirmed	True or array of overbooking infos
	 * @return	Array|Boolean
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

		if( ! $overbookingConfirmed || ! TodoyuCalendarManager::isOverbookingAllowed() ) {
				// Collect overbookings of assigned persons (to request confirmation or resetting event)
			$overbookingPersonsErrors	= self::getOverbookedPersonsErrors($idEvent, $dateStart, $dateEnd);
			if( $overbookingPersonsErrors !== false ) {
				return $overbookingPersonsErrors;
			}
		}

			// Update event record data
		$data	= array(
			'date_start'=> $dateStart,
			'date_end'	=> $dateEnd
		);

		TodoyuHookManager::callHook('calendar', 'event.move', array($idEvent, $dateStart, $dateEnd));

		self::updateEvent($idEvent, $data);

			// Update scheduled reminders relative to shifted time of event
		TodoyuCalendarReminderManager::shiftReminderDates($idEvent, $offset);

		return true;
	}



	/**
	 * Collect overbooking errors of affected persons
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @return	Array|Boolean
	 */
	public static function getOverbookedPersonsErrors($idEvent, $dateStart, $dateEnd) {
		$idEvent	= intval($idEvent);

		$event			= TodoyuCalendarEventManager::getEvent($idEvent);
		$assignedPersons= $event->getAssignedPersonIDs();
		$overbookedInfos= self::getOverbookingInfos($dateStart, $dateEnd, $assignedPersons, $idEvent);

		if( sizeof($overbookedInfos) > 0 ) {
			$errorMessages = array();
			foreach($overbookedInfos as $idPerson => $infos) {
				$errorMessages[] = Todoyu::Label('calendar.event.error.personsOverbooked') . ' ' . TodoyuContactPersonManager::getPerson($idPerson)->getFullName();
			}

			return array_unique($errorMessages);
		}

		return false;
	}



	/**
	 * Assign multiple persons to an event
	 *
	 * @param	Integer		$idEvent
	 * @param	Array		$personIDs
	 * @param	Integer		$dateStartOld
	 */
	public static function saveAssignments($idEvent, array $personIDs, $dateStartOld) {
		$idEvent			= intval($idEvent);
		$personIDs			= TodoyuArray::intval($personIDs, true, true);
		$assignedPersonIDs	= TodoyuCalendarEventAssignmentManager::getAssignedPersonIDs($idEvent);
		$newAssignments		= TodoyuArray::diffLeft($personIDs, $assignedPersonIDs);
		$removedAssignments	= TodoyuArray::diffLeft($assignedPersonIDs, $personIDs);
		$keptAssignments	= array_intersect($personIDs, $assignedPersonIDs);

			// Add new assignments
		foreach($newAssignments as $idPerson) {
			self::addAssignment($idEvent, $idPerson);
		}

			// Remove deleted assignments
		foreach($removedAssignments as $idPerson) {
			TodoyuCalendarEventAssignmentManager::removeAssignment($idEvent, $idPerson);
		}

			// Update untouched assignments
		foreach($keptAssignments as $idPerson) {
			self::updateAssignment($idEvent, $idPerson, $dateStartOld);
		}
	}



	/**
	 * Assign person to the event
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 */
	private static function addAssignment($idEvent, $idPerson) {
		$idEvent		= intval($idEvent);
		$event			= self::getEvent($idEvent);
		$idPerson		= intval($idPerson);
		$acknowledged	= Todoyu::personid() == $idPerson ? 1 : 0;

		$dateStart			= $event->getStartDate();
		$advanceTimeEmail	= TodoyuCalendarReminderManager::getAdvanceTimeEmail($idPerson);
		$advanceTimePopup	= TodoyuCalendarReminderManager::getAdvanceTimePopup($idPerson);
		$dateRemindEmail	= $advanceTimeEmail === 0 ? 0 : $dateStart - $advanceTimeEmail;
		$dateRemindPopup	= $advanceTimePopup === 0 ? 0 : $dateStart - $advanceTimePopup;

		$table	= 'ext_calendar_mm_event_person';
		$data	= array(
			'id_event'			=> $idEvent,
			'id_person'			=> $idPerson,
			'is_acknowledged'	=> $acknowledged,
			'is_updated'		=> 0,
			'date_remindemail'	=> $dateRemindEmail,
			'date_remindpopup'	=> $dateRemindPopup
		);

		Todoyu::db()->addRecord($table, $data);

		TodoyuHookManager::callHook('calendar', 'event.assign', array($idEvent, $idPerson));
	}



	/**
	 * Update assignment for a person
	 * Update reminders if set
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @param	Integer		$oldDateStart
	 */
	public static function updateAssignment($idEvent, $idPerson, $oldDateStart) {
		$event		= self::getEvent($idEvent);
		$reminder	= $event->getReminder($idPerson);
		$diff		= $event->getStartDate() - $oldDateStart;

		$dateEmail	= false;
		$datePopup	= false;

		if( $diff > 0 ) {
			if( $reminder->hasEmailReminder() ) {
				$dateEmail	= $reminder->getDateRemindEmail() + $diff;
			}
			if( $reminder->hasPopupReminder() ) {
				$datePopup	= $reminder->getDateRemindPopup() + $diff;
			}
		}

		TodoyuCalendarEventAssignmentManager::updateReminderDates($idEvent, $idPerson, $dateEmail, $datePopup);
	}



	/**
	 * Update reminders in assignment for current person
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$advanceTimeEmail		Reminder time before event start for email
	 * @param	Integer		$advanceTimePopup		Reminder time before event start for popup
	 */
	private static function updateAssignmentRemindersForCurrentPerson($idEvent, $advanceTimeEmail, $advanceTimePopup) {
		$idEvent			= intval($idEvent);
		$idPerson			= TodoyuAuth::getPersonID();
		$advanceTimeEmail	= intval($advanceTimeEmail);
		$advanceTimePopup	= intval($advanceTimePopup);
		$event				= self::getEvent($idEvent);
		$dateStart			= $event->getStartDate();
		$data				= array(
			'is_remindpopupdismissed'	=> 0
		);

		if( $advanceTimeEmail === 0 ) {
			$data['date_remindemail']	= 0;
		} else {
			$data['date_remindemail']	= $dateStart - $advanceTimeEmail;
		}

		if( $advanceTimePopup === 0 ) {
			$data['date_remindpopup']	= 0;
		} else {
			$data['date_remindpopup']	= $dateStart - $advanceTimePopup;
		}

		TodoyuCalendarReminderManager::updateReminderByAssignment($idEvent, $idPerson, $data);
	}



	/**
	 * Check whether person is assigned
	 *
	 * @param	Integer		$idEvent
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function isPersonAssigned($idEvent, $idPerson) {
		$idEvent	= intval($idEvent);
		$idPerson	= intval($idPerson);
		$personIDs	= TodoyuCalendarEventAssignmentManager::getAssignedPersonIDs($idEvent);

		return in_array($idPerson, $personIDs);
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
	 * @return	Array
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
					'is_acknowledged'	=> $idPerson == Todoyu::personid() ? 1 : 0,
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
		$idPerson	= Todoyu::personid($idPerson);

		$where 	= '		id_event	= ' . $idEvent .
				  ' AND	id_person	= ' . $idPerson;

			// Store also timestamp to be able to detect unacknowledged modifications of events
		$update	= array(
			'is_acknowledged'	=> 1
		);

		Todoyu::db()->doUpdate('ext_calendar_mm_event_person', $where, $update);

		TodoyuHookManager::callHook('calendar', 'event.acknowledge', array($idEvent, $idPerson));
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
	 * @param	Integer		$timestamp
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
		}

		if( TodoyuCalendarEventRights::isDeleteAllowed($idEvent) ) {
			$allowed['delete']	= $own['remove'];
		}

			// Option: add event
		if( TodoyuCalendarEventRights::isAddAllowed() ) {
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
	public static function getContextMenuItemsPortal($idEvent, array $items) {
		$idEvent	= intval($idEvent);
		$event		= TodoyuCalendarEventManager::getEvent($idEvent);
		$dateStart	= $event->getStartDate();

		$ownItems			= Todoyu::$CONFIG['EXT']['calendar']['ContextMenu']['Event'];
		$ownItems['show']	= Todoyu::$CONFIG['EXT']['calendar']['ContextMenu']['EventPortal']['show'];

		unset($ownItems['add']);

		if( ! TodoyuCalendarEventRights::isEditAllowed($idEvent) ) {
			unset($ownItems['edit']);
		}

		if( ! TodoyuCalendarEventRights::isDeleteAllowed($idEvent) ) {
			unset($ownItems['delete']);
		}

		$ownItems['show']['jsAction'] = str_replace('#DATE#', $dateStart, $ownItems['show']['jsAction']);

		foreach($ownItems['show']['submenu'] as $key => $config) {
			$ownItems['show']['submenu'][$key]['jsAction'] = str_replace('#DATE#', $dateStart, $config['jsAction']);
		}

		return array_merge_recursive($items, $ownItems);
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

			$warning	= Todoyu::render($tmpl, $formData);
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



	/**
	 * Get colors for event type
	 *
	 * @return	Array
	 */
	public static function getEventTypeColors() {
		return array(
			0						=> '',
			EVENTTYPE_GENERAL		=> "#7f007f",
			EVENTTYPE_AWAY			=> "#FF0000",
			EVENTTYPE_BIRTHDAY		=> "#FFAC00",
			EVENTTYPE_VACATION		=> "#FFFC00",
			EVENTTYPE_EDUCATION		=> "#77DC00",
			EVENTTYPE_MEETING		=> "green",
			EVENTTYPE_AWAYOFFICIAL	=> "#A60000",
			EVENTTYPE_HOMEOFFICE	=> "grey",
			9						=> "#2335e0",
			10						=> "pink"
		);
	}

}

?>