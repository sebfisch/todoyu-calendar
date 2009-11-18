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
 * Event Manager
 *
 * @package			Todoyu
 * @subpackage		Calendar
 */

class TodoyuEventManager {

	const TABLE = 'ext_calendar_event';


	/**
	 *	Get event object
	 *
	 *	@param	Integer		$idEvent
	 *	@return	TodoyuEvent
	 */
	public static function getEvent($idEvent) {
		$idEvent = intval($idEvent);

		return TodoyuCache::getRecord('TodoyuEvent', $idEvent);
	}



	/**
	 *	Get event record from database
	 *
	 *	@param	Integer		$idEvent
	 *	@return	Array
	 */
	public static function getEventRecord($idEvent) {
		$idEvent = intval($idEvent);

		return Todoyu::db()->getRecord(self::TABLE, $idEvent);
	}



	/**
	 *	Get all events within given timestamps
	 *
	 *	@param	Integer		$dateStart		timestamp at beginning of timespan
	 *	@param	Integer		$dateEnd		timestamp at end of timespan	(optionally 0, will be set to 5 years after today than)
	 *	@param	Array		$users
	 *	@param	Array		$eventTypes
	 *	@param	Mixed		$dayEvents		null = both types, true = only dayevents, false = only non-dayevents
	 *	@param	String		$indexField
	 *	@return	Array
	 */
	public static function getEventsInTimespan($dateStart, $dateEnd, array $users = array(), array $eventTypes = array(), $dayEvents = null, $indexField = 'id') {
		$dateStart	= intval($dateStart);
		$dateEnd	= intval($dateEnd);
		$users		= TodoyuArray::intval($users, true, true);
		$eventTypes	= TodoyuArray::intval($eventTypes, true, true);

		if( sizeof($users) === 0 ) {
			$users	= array(userid());
		}

		$fields	= '	e.*,
					mmeu.id_user,
					mmeu.is_acknowledged';
		$tables	= 	self::TABLE  . ' e,
					ext_calendar_mm_event_user mmeu';
		$where	= '	e.id		= mmeu.id_event AND
					e.deleted	= 0 AND
					(	e.date_start BETWEEN ' . $dateStart . ' AND ' . $dateEnd . ' OR
						e.date_end BETWEEN ' . $dateStart . ' AND ' . $dateEnd . '
					)';
		$group	= '';
		$order	= 'e.date_start';
		$limit	= '';

			// DayEvents: null = both, true = only, false = without
		if( $dayEvents === true ) {
			$where .= ' AND e.is_dayevent = 1';
		} elseif( $dayEvents === false ) {
			$where .= ' AND e.is_dayevent = 0';
		}

			// Users
		$where	.= ' AND mmeu.id_user IN(' . implode(',', $users) . ')';

			// Event types
		if( sizeof($eventTypes) ) {
			$where .= ' AND e.eventtype IN(' . implode(',', $eventTypes) . ')';
		}

		return Todoyu::db()->getArray($fields, $tables, $where, $group, $order, $limit, $indexField);
	}



	/**
	 *	Group the events in subarray. The key for each subarray is a datekey (YYYYMMDD)
	 *	An event appears in each subarray, the event is running on
	 *
	 *	@param	Array		$events			Array of event records
	 *	@param	Integer		$dateStart		Date of first day group
	 *	@param	Integer		$dateEnd		Date of last day group
	 *	@return	Array		Events grouped by datekey
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
	 *	Calculate events' intersections / proximation (have events with overlapping time be arranged hor. parallel)
	 *
	 *	@param	Array	$events
	 *	@param	String	$dateKey	date of currently rendered day (YYYYMMDD)
	 */
	public static function addOverlapInformationToEvents(array $eventsByDay) {
			// Add overlap key to each event
		foreach($eventsByDay as $dayKey => $eventsOfDay) {
			foreach($eventsOfDay as $index => $event) {
				$eventsByDay[$dayKey][$index]['_overlap'] = array();
			}
		}

			// Loop through all days to check the events of the day
		foreach($eventsByDay as $dayKey => $eventsOfDay) {
				// Find overlapings between events
			foreach($eventsOfDay as $index => $event) {
					// Compare each to all other events
				foreach($eventsOfDay as $compareIndex => $compareEvent) {
						// Don't check overlaping with itself
					if( $compareIndex != $index ) {
						if( self::areEventsOverlaping($event, $compareEvent) ) {
								// Set own in own _overlap
							if( ! in_array($index, $eventsByDay[$dayKey][$index]['_overlap']) ) {
								$eventsByDay[$dayKey][$index]['_overlap'][]	= $index;
							}
								// Set compare in own _overlap
							if( ! in_array($compareIndex, $eventsByDay[$dayKey][$index]['_overlap']) ) {
								$eventsByDay[$dayKey][$index]['_overlap'][]	= $compareIndex;
							}
								// Set own in compare _overlap
							if( ! in_array($index, $eventsByDay[$dayKey][$compareIndex]['_overlap']) ) {
								$eventsByDay[$dayKey][$compareIndex]['_overlap'][]	= $index;
							}
								// Set compare in compare _overlap
							if( ! in_array($compareIndex, $eventsByDay[$dayKey][$compareIndex]['_overlap']) ) {
								$eventsByDay[$dayKey][$compareIndex]['_overlap'][]	= $compareIndex;
							}
						}
					}
				}
			}
		}

			// Find intersection(s amount and) index (basis for later left-positioning) of each event
		foreach($eventsByDay as $dayKey => $eventsOfDay) {
			foreach($eventsOfDay as $index => $event) {
				$eventsByDay[$dayKey][$index]['_overlapNum']	= sizeof($event['_overlap']);

				if( $eventsByDay[$dayKey][$index]['_overlapNum'] > 0 ) {
					$overlapIndexes	= array_flip($event['_overlap']);
					$eventsByDay[$dayKey][$index]['_overlapIndex'] = $overlapIndexes[$index];
				}
			}
		}

		return $eventsByDay;
	}



	/**
	 *	Check if two events are overlapping, compare date_start and date_end keys in both arrays
	 *
	 *	@param	Array		$event1
	 *	@param	Array		$event2
	 *	@return	Bool
	 */
	public static function areEventsOverlaping(array $event1, array $event2) {
		return TodoyuTime::rangeOverlaps($event1['date_start'], $event1['date_end'], $event2['date_start'], $event2['date_end']);
	}



	/**
	 *	Fix event overlap
	 *
	 *	@param	Array	$events
	 *	@return	Array
	 */
	public static function fixEventOverlap(array $events) {

		return $events;
	}



	/**
	 *	Get all users assigned to an event
	 *
	 *	@param	Integer $idEvent
	 *	@param	Boolean $getUserData	get also user data (not only the ID)?
	 *	@return	Array
	 */
	public static function getAssignedUsersOfEvent( $idEvent, $getUsersData = false ) {
		$users = array();

		$idEvent	= intval( $idEvent );

		$fields	= 'id_user, is_acknowledged';
		$tables	= 'ext_calendar_mm_event_user';
		$where	= 'id_event = ' . intval( $idEvent );
		$group	= 'id_user';

		$result	= Todoyu::db()->doSelect( $fields, $tables, $where, $group, '', '', 'id_user');

		while($row = Todoyu::db()->fetchAssoc($result))	{
			$user = $row;

			if ($getUsersData) {
				$userArray = TodoyuUserManager::getUserArray($user['id_user']);
				if (is_array($userArray)) {
					$user = array_merge($user, $userArray);
				}
			}

			$users[] 	= $user;
		}

		return $users;
	}



	/**
	 *	Get all users assigned to given array of events
	 *
	 *	@param	Array $eventIDs
	 *	@return	Array
	 */
	public static function getAssignedUsersOfEvents(array $eventIDs ) {
		$eventIDs	= array_unique( TodoyuArray::intval($eventIDs) );

		$fields	= 'id_event, id_user';
		$tables	= 'ext_calendar_mm_event_user';
		$where	= 'id_event IN (' . TodoyuArray::intImplode( $eventIDs ) . ') ';

		$res	= Todoyu::db()->getArray( $fields, $tables, $where, '', 'id_event', '' );

		$eventUsers = array();
		foreach($res as $vals) {
			$eventUsers[ $vals['id_event'] ][] = $vals['id_user'];
		}

		return $eventUsers;
	}



	/**
	 *	Delete event
	 *
	 *	@param	Integer		$idEvent
	 */
	public static function deleteEvent($idEvent) {
		$idEvent	= intval($idEvent);

			// Delete event
		Todoyu::db()->deleteRecord(self::TABLE , $idEvent);

			// Remove assigned users
		self::removeAssignedEventUsers($idEvent);
	}



	/**
	 *	Get all defined event types
	 *	@see		ext/calendar/config/extension.php
	 *
	 *	@param	Bool	$parseLabels
	 *	@return	Array	Event types
	 */
	 public static function getEventTypes($parseLabels = false) {
	 	$types		= $GLOBALS['CONFIG']['EXT']['calendar']['EVENTTYPE'];
	 	$eventTypes	= array();

	 	foreach($types as $index => $key) {
	 		$eventType = array(
	 			'index'	=> $index,
	 			'key'	=> $key
	 		);

	 		if( $parseLabels ) {
	 			$eventType['label'] = Label('event.eventtypes.' . $key);
	 		}

	 		$eventTypes[$index] = $eventType;
	 	}

	 	return $eventTypes;
	}



	/**
	 *	Save a new event
	 *
	 *	@param	Array	$formData	Eventdata
	 *	@param	Integer	$seriesID	If a series is updated, use the same seriesID again
	 *	@return	Date
	 */
	public static function saveEvent(array $data) {
		$xmlPath= 'ext/calendar/config/form/event.xml';

		$idEvent= intval($data['id']);
		$users	= $data['user'];

		unset($data['id']);
		unset($data['user']);

			// Add empty event
		if( $idEvent === 0 )	{
			$idEvent = self::addEvent(array());
		}

			// Call save data hooks
		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idEvent);

			// Update the event with the definitive data
		self::updateEvent($idEvent, $data);
			// Remove already assigned users
		self::removeAssignedEventUsers($idEvent);

			// Add users
		if( is_array($users) ) {
			$users	= TodoyuArray::getColumn($users, 'id');
			self::assignUsersToEvent($idEvent, $users);
		}

			// Remove record and query from cache
		self::removeEventFromCache($idEvent);

		return $idEvent;
	}


	/**
	 *	Save quick event
	 *
	 *	@param	Array		$data
	 *	@return	Integer		Event ID
	 */
	public static function saveQuickEvent(array $data) {
		$xmlPath	= 'ext/calendar/config/form/quickevent.xml';

		$idEvent	= self::addEvent(array());

			// Add users
		if( is_array($data['user']) ) {
			$users	= TodoyuArray::getColumn($data['user'], 'id');
			self::assignUsersToEvent($idEvent, $users);
			unset($data['user']);
		}

		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idEvent);

			// Update the event with the definitive data
		self::updateEvent($idEvent, $data);

			// Remove record and query from cache
		self::removeEventFromCache($idEvent);

		return $idEvent;
	}



	/**
	 *	Add an event to database. Add date_create and id_user_create values
	 *
	 *	@param	Array		$data
	 *	@return	Integer
	 */
	public static function addEvent(array $data) {
		unset($data['id']);

		$data['date_create']	= NOW;
		$data['id_user_create']	= TodoyuAuth::getUserID();

		return Todoyu::db()->addRecord('ext_calendar_event', $data);
	}



	/**
	 *	Update an event in the database
	 *
	 *	@param	Integer		$idEvent
	 *	@param	Array		$data
	 *	@return	Boolean
	 */
	public static function updateEvent($idEvent, array $data) {
		$idEvent	= intval($idEvent);
		unset($data['id']);

		$data['date_update']	= NOW;

		return Todoyu::db()->updateRecord('ext_calendar_event', $idEvent, $data) === 1;
	}



	/**
	 *	Assign multiple users to an event
	 *
	 *	@param	Integer		$idEvent
	 *	@param	Array		$userIDs
	 */
	public static function assignUsersToEvent($idEvent, array $userIDs) {
		$idEvent	= intval($idEvent);
		$userIDs	= TodoyuArray::intval($userIDs, true, true);

		foreach($userIDs as $idUser) {
			self::assignUserToEvent($idEvent, $idUser);
		}
	}



	/**
	 *	Assign a single user to an event
	 *
	 *	@param	Integer		$idEvent
	 *	@param	Integer		$idUser
	 */
	public static function assignUserToEvent($idEvent, $idUser) {
		$idEvent= intval($idEvent);
		$idUser	= intval($idUser);

		$table	= 'ext_calendar_mm_event_user';
		$data	= array(
			'id_event'	=> $idEvent,
			'id_user'	=> $idUser
		);

		Todoyu::db()->addRecord($table, $data);
	}



	/**
	 *	Remove all assigned users of an event
	 *
	 *	@param	Integer		$idEvent
	 */
	public static function removeAssignedEventUsers($idEvent) {
		$idEvent= intval($idEvent);
		$table	= 'ext_calendar_mm_event_user';
		$where	= 'id_event = ' . $idEvent;

		Todoyu::db()->doDelete($table, $where);
	}



	/**
	 *	Remove a user assignement for an event
	 *
	 *	@param	Integer		$idEvent
	 *	@param	Integer		$idUser
	 */
	public static function removeAssignedEventUser($idEvent, $idUser) {
		$idEvent	= intval($idEvent);
		$idUser		= intval($idUser);

		$table		= self::TABLE;
		$where		= '	id_event	= ' . $idEvent . ' AND
						id_user		= ' . $idUser;

		Todoyu::db()->doDelete($table, $where);
	}



	/**
	 *	Create new event record in DB
	 *
	 *	@return	Integer		Autogenerated ID
	 */
	protected static function createNewEvent()	{
		$insertArray	= array(
			'date_create'		=> NOW,
			'id_user_create'	=> userid(),
			'deleted'			=> 0
		);

		return Todoyu::db()->doInsert( self::TABLE , $insertArray );
	}



	/**
	 *	Calculate number of day the event starts, relative to shown days of current week-view
	 *
	 *	@param	Integer		$start					UNIX timestamp event start
	 *	@param	Integer		$end					UNIX timestamp event end
	 *	@param	Integer		$tstampFirstShownDay	UNIX timestamp first shown day
	 *	@param	Integer		$tstampLastShownDay		UNIX timestamp last shown day
	 *	@return	Integer
	 */
	public function calcEventStartingDayNumInWeek($tstampStart, $tstampFirstShownDay) {
		if ($tstampStart < $tstampFirstShownDay) {
			$dayNum = 0;
		} else {
			$dayNum = TodoyuTime::getWeekdayNum($tstampStart, true);
		}

		return $dayNum;
	}



	/**
	 *	Calculate number of day the event ends, relative to shown days of current week-view
	 *
	 *	@param	Integer		$start					UNIX timestamp event start
	 *	@param	Integer		$end					UNIX timestamp event end
	 *	@param	Integer		$tstampFirstShownDay	UNIX timestamp first shown day
	 *	@param	Integer		$tstampLastShownDay		UNIX timestamp last shown day
	 *	@return	Integer
	 */
	public function calcEventEndingDayNumInWeek($tstampEnd, $tstampLastShownDay) {
		if ($tstampEnd > $tstampLastShownDay) {
			$dayNum = 7;
		} else {
			$dayNum	= TodoyuTime::getWeekdayNum($tstampEnd, true);
		}

		return $dayNum;
	}



	/**
	 *	Manipulate (precalculate adv. values) event form data
	 *
	 *	@param	Array	$formData
	 *	@return	Array
	 */
	protected static function manipulateFormData(array $formData)	{
		$formData['title']			= trim($formData['title']);
		$formData['date_start']		= TodoyuTime::parseDateString($formData['date_start']);
		$formData['date_end']		= TodoyuTime::parseDateString($formData['date_end']);
		$formData['is_private']		= intval($formData['is_private']);
		$formData['is_public']		= intval($formData['is_public']);
		$formData['is_dayevent']	= intval($formData['is_dayevent']);
		$formData['eventtype']		= intval($formData['eventtype']);

		if($formData['starttime'])	{
			$formData['date_start']	=	mktime(0, 0, 0, date('n', $formData['date_start']), date('j', $formData['date_start']), date('Y', $formData['date_start'])) + TodoyuTime::parseTime($formData['starttime']);
			unset($formData['starttime']);
		}

		if($formData['endtime'])	{
			$formData['date_end']	=	mktime(0, 0, 0, date('n', $formData['date_end']), date('j', $formData['date_end']), date('Y', $formData['date_end'])) + TodoyuTime::parseTime($formData['endtime']);
			unset($formData['endtime']);
		}

		return $formData;
	}



	/**
	 *	Remove event from cache
	 *
	 *	@param	Integer	$idEvent
	 */
	public static function removeEventFromCache($idEvent) {
		$idEvent = intval($idEvent);

		TodoyuCache::removeRecord('Event', $idEvent);
		TodoyuCache::removeRecordQuery(self::TABLE, $idEvent);
	}



	/**
	 *	Assign users to an event
	 *
	 *	@param	Integer		$idEvent
	 *	@param	Array		$userIDs
	 */
	public static function addAssignedEventUsersAndSendMail($idEvent, array $formData) {
		$idEvent	= intval($idEvent);

		if(array_key_exists('user', $formData))	{
			$table = 'ext_calendar_mm_event_user';

			foreach($formData['user'] as $userArray)	{
				$idUser	= $userArray['id'];
				$fields	=	array(
					'id_event'			=> $idEvent,
					'id_user'			=> $idUser,
					'is_acknowledged'	=> 0,
				);

				Todoyu::db()->doInsert($table, $fields);

				if($formData['send_notification'] === 1)	{
					TodoyuCalendarMailer::sendEventNotification($idEvent, $idUser);
				}
			}

			unset($formData['user']);
			unset($formData['send_notification']);
		}

		return $formData;
	}



	/**
	 *	Remove fields based on the selected
	 *
	 *	@param	TodoyuForm	$form
	 *	@param	Integer	$idEvent
	 *	@return	TodoyuForm
	 */
	public static function removeFieldByType(TodoyuForm $form, $idEvent) {
		$formData	= $form->getFormData();
		$type		= intval($formData['event_type']);

		switch($type) {

			case EVENTTYPE_BIRTHDAY:
				$form->removeField('is_dayevent', true);
				$form->removeField('is_public', true);
				$form->removeField('date_end', true);
				$form->removeField('place', true);
				$form->removeField('user', true);
				$form->removeField('id_project', true);
				$form->removeField('id_task', true);
			break;

			case EVENTTYPE_VACATION:
				$form->removeField('is_dayevent', true);
				$form->removeField('is_private', true);
				$form->removeField('id_project', true);
				$form->removeField('id_task', true);
			break;

			case EVENTTYPE_REMINDER:
				$form->removeField('is_dayevent', true);
				$form->removeField('date_end', true);
			break;

		}

		return $form;
	}



	/**
	 *	Set given event acknowledged
	 *
	 *	@param	Integer	$idEvent
	 *	@param	Integer	$idUser
	 */
	public static function acknowledgeEvent($idEvent, $idUser)	{
		$updateArray = array('is_acknowledged' => 1);

		$where = 'id_event = '.intval($idEvent). ' AND id_user = '.intval($idUser);

		Todoyu::db()->doUpdate('ext_calendar_mm_event_user', $where, $updateArray);
	}



	/**
	 *	Create new event object with default data
	 *
	 *	@param	Integer	$timeStamp
	 */
	public static function createNewEventWithDefaultsInCache($date)	{
		$date		= intval($date);

		$defaultData= self::getEventDefaultData($date);

		$idCache	= TodoyuCache::makeClassKey('TodoyuEvent', 0);
		$event		= self::getEvent(0);
		$event->injectData($defaultData);
		TodoyuCache::set($idCache, $event);
	}



	/**
	 *	Creates event default data
	 *
	 *	@param	Integer	$timeStamp
	 *	@return	Array
	 */
	protected static function getEventDefaultData($timeStamp)	{
		$timeStamp	= $timeStamp == 0 ? NOW : intval($timeStamp);

		if( date('Hi', $timeStamp) === '0000' ) {
			$dateStart	= $timeStamp + intval($GLOBALS['CONFIG']['EXT']['calendar']['default']['timeStart']);
		} else {
			$dateStart	= $timeStamp;
		}

		$dateEnd = $dateStart + intval($GLOBALS['CONFIG']['EXT']['calendar']['default']['eventDuration']);

		$defaultData = array(
			'id'			=>	0,
			'date_start'	=>	$dateStart,
			'date_end'		=>	$dateEnd,
			'user' => array(
				0 => TodoyuUserManager::getUser(userid())->getTemplateData()
			)
		);

		return $defaultData;
	}



	/**
	 *	Add default context menu item for event
	 *
	 *	@param	Integer		$idEvent
	 *	@param	Array		$items
	 *	@return	Array
	 */
	public static function getContextMenuItems($idEvent, array $items) {
		$idEvent = intval($idEvent);

		$items = array_merge_recursive($items, $GLOBALS['CONFIG']['EXT']['calendar']['ContextMenu']['Event']);

		return $items;
	}

}

?>