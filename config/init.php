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

/* ------------------------
	Schedule Cronjobs
   ------------------------ */
TodoyuScheduler::addJob('TodoyuCalendarJobReminderEmail', 0);



/* ----------------------------
	Context Menu Callbacks
   ---------------------------- */
TodoyuContextMenuManager::addFunction('Event', 'TodoyuCalendarEventManager::getContextMenuItems', 10);
TodoyuContextMenuManager::addFunction('EventPortal', 'TodoyuCalendarEventManager::getContextMenuItemsPortal', 10);
TodoyuContextMenuManager::addFunction('CalendarBody', 'TodoyuCalendarManager::getContextMenuItems', 10);

if( allowed('calendar', 'reminders:email') ) {
	TodoyuContextMenuManager::addFunction('Event', 'TodoyuCalendarReminderEmailManager::getContextMenuItems', 10);
}
if( allowed('calendar', 'reminders:popup') ) {
	TodoyuContextMenuManager::addFunction('Event', 'TodoyuCalendarReminderPopupManager::getContextMenuItems', 10);
}


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
	'day'	=> array(
		'key'		=> 'day',
		'id'		=> 'day',
		'label'		=> 'LLL:core.date.day',
		'require'	=> 'calendar.general:area',
		'position'	=> 62
	),
	'week'	=> array(
		'key'		=> 'week',
		'id'		=> 'week',
		'label'		=> 'LLL:core.date.week',
		'require'	=> 'calendar.general:area',
		'position'	=> 63
	),
	'month'	=> array(
		'key'		=> 'month',
		'id'		=> 'month',
		'label'		=> 'LLL:core.date.month',
		'require'	=> 'calendar.general:area',
		'position'	=> 64
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
TodoyuCalendarEventTypeManager::addEventType(EVENTTYPE_GENERAL, 'general', 'calendar.event.type.general');
TodoyuCalendarEventTypeManager::addEventType(EVENTTYPE_AWAY, 'away', 'calendar.event.type.away');
TodoyuCalendarEventTypeManager::addEventType(EVENTTYPE_AWAYOFFICIAL, 'awayofficial', 'calendar.event.type.awayofficial');
TodoyuCalendarEventTypeManager::addEventType(EVENTTYPE_BIRTHDAY, 'birthday', 'calendar.event.type.birthday');
TodoyuCalendarEventTypeManager::addEventType(EVENTTYPE_VACATION, 'vacation', 'calendar.event.type.vacation');
TodoyuCalendarEventTypeManager::addEventType(EVENTTYPE_EDUCATION, 'education', 'calendar.event.type.education');
TodoyuCalendarEventTypeManager::addEventType(EVENTTYPE_MEETING, 'meeting', 'calendar.event.type.meeting');
TodoyuCalendarEventTypeManager::addEventType(EVENTTYPE_HOMEOFFICE, 'homeoffice', 'calendar.event.type.homeoffice');
TodoyuCalendarEventTypeManager::addEventType(EVENTTYPE_COMPENSATION, 'compensation', 'calendar.event.type.compensation');
TodoyuCalendarEventTypeManager::addEventType(EVENTTYPE_MILESTONE, 'milestone', 'calendar.event.type.milestone');
TodoyuCalendarEventTypeManager::addEventType(EVENTTYPE_REMINDER, 'reminder', 'calendar.event.type.reminder');

	// Which event types have no relevance to overbooking prevention?
Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_OVERBOOKABLE'] = array(
	EVENTTYPE_BIRTHDAY,
	EVENTTYPE_MILESTONE,
	EVENTTYPE_REMINDER
);
	// How many conflicting appointments per person to be shown in overbooking warning?
Todoyu::$CONFIG['EXT']['calendar']['maxShownOverbookingsPerPerson']	= 5;

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
Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_LOOKBACK'] = 0;
	// Time bofore event for event reminders to occur
Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_MINUTESBEFOREEVENTOPTIONS'] = array(0, 5, 15, 30, 60, 120, 720, 1440, 2880, 10080);



/* -------------------------------------
	Add calendar module to profile
   ------------------------------------- */
if( TodoyuExtensions::isInstalled('profile') ) {
	TodoyuProfileManager::addModule('calendar', array(
		'position'	=> 5,
		'tabs'		=> 'TodoyuCalendarProfileRenderer::renderTabs',
		'content'	=> 'TodoyuCalendarProfileRenderer::renderContent',
		'label'		=> 'calendar.ext.profile.module',
		'class'		=> 'calendar'
	));

		// Tabs for calendar section in profile
	Todoyu::$CONFIG['EXT']['profile']['calendarTabs'] = array();

	if( allowed('calendar', 'mailing:sendAsEmail') ) {
		Todoyu::$CONFIG['EXT']['profile']['calendarTabs'][]= array(
			'id'			=> 'main',
			'label'			=> 'LLL:calendar.ext.profile.module.main.tab',
//			'require'		=> 'calendar.settings:editbookmarks'
		);
	}

	if( allowed('calendar', 'reminders:popup') ||  allowed('calendar', 'reminders:email') ) {
		Todoyu::$CONFIG['EXT']['profile']['calendarTabs'][]= array(
			'id'			=> 'reminders',
			'label'			=> 'LLL:calendar.ext.profile.module.reminders.tab',
//			'require'		=> 'calendar.settings:editbookmarks'
		);
	}

	if( allowed('calendar', 'share:personal') ||  allowed('calendar', 'share:availability') ) {
		Todoyu::$CONFIG['EXT']['profile']['calendarTabs'][]= array(
			'id'			=> 'share',
			'label'			=> 'LLL:calendar.ext.profile.module.share.tab',
//			'require'		=> 'calendar.settings:editbookmarks'
		);
	}
}

?>