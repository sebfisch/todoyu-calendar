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
 * General configuration for calendar extension
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

TodoyuContextMenuManager::registerFunction('Event', 'TodoyuEventManager::getContextMenuItems', 10);
TodoyuContextMenuManager::registerFunction('EventPortal', 'TodoyuEventManager::getContextMenuItemsPortal', 10);
TodoyuContextMenuManager::registerFunction('CalendarArea', 'TodoyuCalendarManager::getContextMenuItems', 10);

if ( TodoyuExtensions::isInstalled('portal') && allowed('calendar', 'general:portaltab') ) {
	TodoyuPortalManager::addTab('appointment', 'TodoyuCalendarPortalRenderer::getAppointmentTabLabel', 'TodoyuCalendarPortalRenderer::getAppointmentTabContent', 50, array('calendar/public'));
}

	// Setup tabs in calendar area
$CONFIG['EXT']['calendar']['config'] = array(
	'defaultTab'	=> 'week'
);

	// Tabs used in calendar
$CONFIG['EXT']['calendar']['tabs'] = array(
	array(
		'id'		=> 'day',
		'label'		=> 'LLL:date.day'
	),
	array(
		'id'		=> 'week',
		'label'		=> 'LLL:date.week'
	),
	array(
		'id'		=> 'month',
		'label'		=> 'LLL:date.month'
	)
);



	// Add eventtypes
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




	// Which event types define absences?
$CONFIG['EXT']['calendar']['EVENTTYPES_ABSENCE'] = array(
	EVENTTYPE_AWAY,
	EVENTTYPE_VACATION,
	EVENTTYPE_COMPENSATION
);

	// Default color preset for events being assigned to several persons / none
$CONFIG['EXT']['calendar']['defaultEventColors'] = array(
	'id'		=> -1,
	'border'	=> '#555',
	'text'		=> '#000',
	'faded'		=> '#555',
);


	// Additional portal tab eventslisting specific config
$CONFIG['EXT']['calendar']['appointmentTabConfig'] = array(
		// Show coming-up holidays in events tab of portal?
	'showHoliday'	=> true,
	'showBirthday'	=> true,
		// How many weeks to look ahead for coming-up holidays to be listed in events tab of portal?
	'weeksHoliday'	=> 4,
	'weeksBirthday'	=> 52,
	'weeksEvents'	=> 52 // 1 year
);

	// Default values for event editing
$CONFIG['EXT']['calendar']['default']['timeStart']		= 28800;	// 08:00
$CONFIG['EXT']['calendar']['default']['eventDuration']	= 3600;		// 1 hour

	// Register contextmenu
//TodoyuContextMenuManager::registerFunction('Calendar', 'TodoyuPanelWidgetEventTypeSelector::getContextMenuItems', 10);

?>