<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2012, snowflake productions GmbH, Switzerland
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
TodoyuScheduler::addJob('TodoyuCalendarJobReminderEmail', 5);


// Add holiday set selector to company address form
TodoyuFormHook::registerBuildForm('ext/contact/config/form/address.xml', 'TodoyuCalendarManager::hookAddHolidaysetToCompanyAddress');

TodoyuFormHook::registerSaveData('ext/calendar/config/form/event.xml', 'TodoyuCalendarEventStaticManager::hookSaveEvent');


/* ----------------------------
	Context Menu Callbacks
   ---------------------------- */
TodoyuContextMenuManager::addFunction('Event', 'TodoyuCalendarEventStaticManager::getContextMenuItems', 10);
TodoyuContextMenuManager::addFunction('EventPortal', 'TodoyuCalendarEventStaticManager::getContextMenuItemsPortal', 10);
TodoyuContextMenuManager::addFunction('CalendarBody', 'TodoyuCalendarManager::getContextMenuItems', 10);

if( Todoyu::allowed('calendar', 'reminders:email') ) {
	TodoyuContextMenuManager::addFunction('Event', 'TodoyuCalendarReminderEmailManager::getContextMenuItems', 10);
	TodoyuContextMenuManager::addFunction('EventPortal', 'TodoyuCalendarReminderEmailManager::getContextMenuItems', 10);
}
if( Todoyu::allowed('calendar', 'reminders:popup') ) {
	TodoyuContextMenuManager::addFunction('Event', 'TodoyuCalendarReminderPopupManager::getContextMenuItems', 10);
	TodoyuContextMenuManager::addFunction('EventPortal', 'TodoyuCalendarReminderPopupManager::getContextMenuItems', 10);
}



/* ----------------------------
	Quickinfo Callbacks
   ---------------------------- */
TodoyuQuickinfoManager::addFunction('event', 'TodoyuCalendarQuickinfoManager::addQuickinfoEvent');


/* ----------------------------
	Autocompleter Callbacks
   ---------------------------- */
TodoyuAutocompleter::addAutocompleter('eventperson', 'TodoyuCalendarEventViewHelper::autocompleteEventPersons', array('calendar', 'general:use'));


/* ----------------------------
	Form Hooks
   ---------------------------- */
TodoyuFormHook::registerBuildForm('ext/calendar/config/form/event.xml', 'TodoyuCalendarReminderManager::hookAddReminderFieldsToEvent');
TodoyuFormHook::registerBuildForm('ext/calendar/config/form/event.xml', 'TodoyuCalendarEventSeriesManager::hookSetSeriesFields');
TodoyuFormHook::registerLoadData('ext/calendar/config/form/event.xml', 'TodoyuCalendarEventSeriesManager::hookLoadSeriesData');
TodoyuFormHook::registerBuildForm('ext/calendar/config/form/update-mailinfo.xml', 'TodoyuCalendarEventMailManager::hookToggleAutoMailField');


TodoyuHookManager::registerHook('calendar', 'event.move.data', 'TodoyuCalendarEventSeriesManager::hookEventMovedDataUpdate');
TodoyuHookManager::registerHook('calendar', 'event.move', 'TodoyuCalendarEventMailManager::hookEventMoved');
TodoyuHookManager::registerHook('calendar', 'event.save', 'TodoyuCalendarEventMailManager::hookEventSaved');
TodoyuHookManager::registerHook('calendar', 'event.delete', 'TodoyuCalendarEventMailManager::hookEventDeleted');


/* -----------------------
	Tabs Configurations
   ----------------------- */
	// Setup tabs in calendar area
Todoyu::$CONFIG['EXT']['calendar']['config']	= array(
	'defaultTab'	=> 'week'
);

	// Tabs used in calendar
Todoyu::$CONFIG['EXT']['calendar']['tabs']	= array(
	'day'	=> array(
		'key'		=> 'day',
		'id'		=> 'day',
		'label'		=> 'core.date.day',
		'require'	=> 'calendar.general:area',
		'position'	=> 62
	),
	'week'	=> array(
		'key'		=> 'week',
		'id'		=> 'week',
		'label'		=> 'core.date.week',
		'require'	=> 'calendar.general:area',
		'position'	=> 63
	),
	'month'	=> array(
		'key'		=> 'month',
		'id'		=> 'month',
		'label'		=> 'core.date.month',
		'require'	=> 'calendar.general:area',
		'position'	=> 64
	)
);


	// Additional portal tab events listing specific config
Todoyu::$CONFIG['EXT']['calendar']['appointmentTabConfig']	= array(
		// How many weeks to look ahead for coming-up holidays to be listed in events tab of portal?
	'weeksHoliday'	=> 4,
	'weeksBirthday'	=> 8,
	'weeksStatic'	=> 52 // 1 year
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

	// Which event types can be overbooked?
Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_OVERBOOKABLE']	= array(
	EVENTTYPE_AWAYOFFICIAL,
	EVENTTYPE_HOMEOFFICE,
	EVENTTYPE_REMINDER,
	EVENTTYPE_MILESTONE,
	EVENTTYPE_BIRTHDAY

);
	// How many conflicting appointments per person to be shown in overbooking warning?
Todoyu::$CONFIG['EXT']['calendar']['maxShownOverbookingsPerPerson']	= 5;

	// Which event types define absences?
Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_ABSENCE']	= array(
	EVENTTYPE_AWAY,
	EVENTTYPE_VACATION,
	EVENTTYPE_COMPENSATION
);
	// Which event types should be reminded of via popup?
Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_REMIND_POPUP']	= array(
	EVENTTYPE_GENERAL,
	EVENTTYPE_MEETING,
	EVENTTYPE_MILESTONE,
	EVENTTYPE_REMINDER
);

	// Default color preset for events being assigned to several persons / none
Todoyu::$CONFIG['EXT']['calendar']['defaultEventColors']	= array(
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
Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_LOOKAHEAD']	= 57600;	// 16 hours
	// How long to remind of events in the past?
Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_LOOKBACK']	= 60; // 1 minute
	// Time (in minutes) before event for event reminders to occur
Todoyu::$CONFIG['EXT']['calendar']['EVENT_REMINDER_MINUTESBEFOREEVENTOPTIONS']	= array(1, 5, 15, 30, 45, 60, 120, 720, 1440, 2880, 10080);



/* -------------------------------------
	Add calendar module to profile
   ------------------------------------- */
if( TodoyuExtensions::isInstalled('profile') && TodoyuAuth::isInternal() ) {
	TodoyuProfileManager::addModule('calendar', array(
		'position'	=> 5,
		'tabs'		=> 'TodoyuCalendarProfileRenderer::renderTabs',
		'content'	=> 'TodoyuCalendarProfileRenderer::renderContent',
		'label'		=> 'calendar.ext.profile.module',
		'class'		=> 'calendar'
	));

		// Tabs for calendar section in profile
	Todoyu::$CONFIG['EXT']['profile']['calendarTabs']	= array();

	if( Todoyu::allowed('calendar', 'mailing:sendAsEmail') ) {
		Todoyu::$CONFIG['EXT']['profile']['calendarTabs'][]= array(
			'id'			=> 'main',
			'label'			=> 'calendar.ext.profile.module.main.tab',
		);
	}

	if( Todoyu::allowed('calendar', 'reminders:popup') ||  Todoyu::allowed('calendar', 'reminders:email') ) {
		Todoyu::$CONFIG['EXT']['profile']['calendarTabs'][]= array(
			'id'			=> 'reminders',
			'label'			=> 'calendar.ext.profile.module.reminders.tab',
		);
	}
}

Todoyu::$CONFIG['EXT']['calendar']['weekDays'] = array(
	'long'	=> array(
		'mo'	=> 'monday',
		'tu'	=> 'tuesday',
		'we'	=> 'wednesday',
		'th'	=> 'thursday',
		'fr'	=> 'friday',
		'sa'	=> 'saturday',
		'so'	=> 'sunday'
	),
	'short'	=> array(
		'mo'	=> 'mon',
		'tu'	=> 'tue',
		'we'	=> 'wed',
		'th'	=> 'thu',
		'fr'	=> 'fri',
		'sa'	=> 'sat',
		'so'	=> 'sun'
	),
	'index' => array(
		'mo'	=> 1,
		'tu'	=> 2,
		'we'	=> 3,
		'th'	=> 4,
		'fr'	=> 5,
		'sa'	=> 6,
		'so'	=> 0
	)
);

Todoyu::$CONFIG['EXT']['calendar']['series'] = array(
	'maxCreate'	=> 1000
);

	// Event data sources
TodoyuCalendarDataSourceManager::addDataSource('static', 'TodoyuCalendarDataSourceStatic');
TodoyuCalendarDataSourceManager::addDataSource('birthday', 'TodoyuCalendarDataSourceBirthday');
TodoyuCalendarDataSourceManager::addDataSource('holiday', 'TodoyuCalendarDataSourceHoliday');

	// Event filters
TodoyuHookManager::registerHook('calendar', 'event.filter', 'TodoyuCalendarManager::hookEventFilterPersons');
TodoyuHookManager::registerHook('calendar', 'event.filter', 'TodoyuCalendarManager::hookEventFilterEventTypes');
TodoyuHookManager::registerHook('calendar', 'event.filter', 'TodoyuCalendarManager::hookEventFilterHolidaySets');

?>