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

/* ----------------------------
	Context Menu Callbacks
   ---------------------------- */
TodoyuContextMenuManager::addFunction('Event', 'TodoyuEventManager::getContextMenuItems', 10);
TodoyuContextMenuManager::addFunction('EventPortal', 'TodoyuEventManager::getContextMenuItemsPortal', 10);
TodoyuContextMenuManager::addFunction('CalendarBody', 'TodoyuCalendarManager::getContextMenuItems', 10);

TodoyuQuickinfoManager::addFunction('event', 'TodoyuCalendarQuickinfoManager::addQuickinfoEvent');
TodoyuQuickinfoManager::addFunction('holiday', 'TodoyuCalendarQuickinfoManager::addQuickinfoHoliday');
TodoyuQuickinfoManager::addFunction('birthday', 'TodoyuCalendarQuickinfoManager::addQuickinfoBirthday');

TodoyuAutocompleter::addAutocompleter('eventperson', 'TodoyuCalendarManager::autocompleteEventPersons', array('calendar', 'general:use'));



/* -----------------------
	Tabs Configurations
   ----------------------- */

	// Setup tabs in calendar area
Todoyu::$CONFIG['EXT']['calendar']['config'] = array(
	'defaultTab'	=> 'week'
);

	// Tabs used in calendar
Todoyu::$CONFIG['EXT']['calendar']['tabs'] = array(
	array(
		'id'		=> 'day',
		'label'		=> 'LLL:date.day',
		'require'	=> 'calendar.general:area'
	),
	array(
		'id'		=> 'week',
		'label'		=> 'LLL:date.week',
		'require'	=> 'calendar.general:area'
	),
	array(
		'id'		=> 'month',
		'label'		=> 'LLL:date.month',
		'require'	=> 'calendar.general:area'
	)
);


	// Additional portal tab events listing specific config
Todoyu::$CONFIG['EXT']['calendar']['appointmentTabConfig'] = array(
		// Show coming-up holidays in events tab of portal?
	'showHoliday'	=> true,
	'showBirthday'	=> true,
		// How many weeks to look ahead for coming-up holidays to be listed in events tab of portal?
	'weeksHoliday'	=> 4,
	'weeksBirthday'	=> 8,
	'weeksEvents'	=> 52 // 1 year
);



/* -------------------
	Event Types
   ------------------- */
TodoyuEventTypeManager::addEventType(EVENTTYPE_GENERAL, 'general', 'event.type.general');
TodoyuEventTypeManager::addEventType(EVENTTYPE_AWAY, 'away', 'event.type.away');
TodoyuEventTypeManager::addEventType(EVENTTYPE_AWAYOFFICIAL, 'awayofficial', 'event.type.awayofficial');
TodoyuEventTypeManager::addEventType(EVENTTYPE_BIRTHDAY, 'birthday', 'event.type.birthday');
TodoyuEventTypeManager::addEventType(EVENTTYPE_VACATION, 'vacation', 'event.type.vacation');
TodoyuEventTypeManager::addEventType(EVENTTYPE_EDUCATION, 'education', 'event.type.education');
TodoyuEventTypeManager::addEventType(EVENTTYPE_MEETING, 'meeting', 'event.type.meeting');
TodoyuEventTypeManager::addEventType(EVENTTYPE_HOMEOFFICE, 'homeoffice', 'event.type.homeoffice');
TodoyuEventTypeManager::addEventType(EVENTTYPE_COMPENSATION, 'compensation', 'event.type.compensation');
TodoyuEventTypeManager::addEventType(EVENTTYPE_MILESTONE, 'milestone', 'event.type.milestone');
TodoyuEventTypeManager::addEventType(EVENTTYPE_REMINDER, 'reminder', 'event.type.reminder');

	// Which event types have no relevance to overbooking prevention?
Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_OVERBOOKABLE'] = array(
	EVENTTYPE_BIRTHDAY,
	EVENTTYPE_MILESTONE,
	EVENTTYPE_REMINDER
);
	// Which event types define absences?
Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_ABSENCE'] = array(
	EVENTTYPE_AWAY,
	EVENTTYPE_VACATION,
	EVENTTYPE_COMPENSATION
);
	// Which event types should be reminded of via popup?
Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_REMIND_POPUP'] = array(
	EVENTTYPE_GENERAL,
	EVENTTYPE_MEETING,
	EVENTTYPE_MILESTONE,
	EVENTTYPE_REMINDER
);

	// Default color preset for events being assigned to several persons / none
Todoyu::$CONFIG['EXT']['calendar']['defaultEventColors'] = array(
	'id'		=> -1,
	'border'	=> '#555',
	'text'		=> '#000',
	'faded'		=> '#555',
);



	// Default values for event editing
Todoyu::$CONFIG['EXT']['calendar']['default']['timeStart']		= 28800;	// 08:00
Todoyu::$CONFIG['EXT']['calendar']['default']['eventDuration']	= 3600;		// 1 hour



/* ---------------------------
	Event Reminder Settings
   --------------------------- */
	// How long to look ahead for events?
Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_LOOKAHEAD'] = 57600;	// 16 hours
	// How long to remind of events in the past?
Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_LOOKBACK'] = 1800;	// 30 minutes
	// Intervals for rescheduled event reminders
Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_RESCHEDULEINTERVALS'] = array(10, 20, 30, 45, 60);

?>