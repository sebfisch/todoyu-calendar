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



TodoyuColors::generateColorsCSS();

TodoyuContextMenuManager::registerFunction('Event', 'TodoyuEventManager::getContextMenuItems', 10);
TodoyuContextMenuManager::registerFunction('CalendarArea', 'TodoyuCalendarManager::getContextMenuItems', 10);



	// Setup tabs in calendar area
$CONFIG['EXT']['calendar']['config'] = array(
	'defaultTab'	=> 'week'
);

	// Tabs used in calendar
$CONFIG['EXT']['calendar']['contentTabs'] = array(
	array(
		'id'		=> 'day',
		'class'		=> 'day',
		'hasIcon'	=> 1,
		'label'		=> 'LLL:calendar.contentTab.day'
	),
	array(
		'id'		=> 'week',
		'class'		=> 'week',
		'hasIcon'	=> 1,
		'label'		=> 'LLL:calendar.contentTab.week'
	),
	array(
		'id'		=> 'month',
		'class'		=> 'month',
		'hasIcon'	=> 1,
		'label'		=> 'LLL:calendar.contentTab.month'
	)
);


	// Register event types
	// @see		referring constants are defined in constants.php
$CONFIG['EXT']['calendar']['EVENTTYPE'] = array(
	EVENTTYPE_GENERAL		=> 'general',
	EVENTTYPE_AWAY			=> 'away',
	EVENTTYPE_AWAYOFFICIAL	=> 'awayofficial',
	EVENTTYPE_BIRTHDAY		=> 'birthday',
	EVENTTYPE_VACATION		=> 'vacation',
	EVENTTYPE_EDUCATION		=> 'education',
	EVENTTYPE_MEETING		=> 'meeting',
	EVENTTYPE_HOMEOFFICE	=> 'homeoffice',
	EVENTTYPE_PAPER			=> 'paper',
	EVENTTYPE_CARTON		=> 'carton',
	EVENTTYPE_COMPENSATION	=> 'compensation',
	EVENTTYPE_MILESTONE		=> 'milestone',
	EVENTTYPE_REMINDER		=> 'reminder'
);

	// Which event types define absences?
$CONFIG['EXT']['calendar']['EVENTTYPES_ABSENCE'] = array(
	EVENTTYPE_AWAY,
	EVENTTYPE_VACATION,
	EVENTTYPE_COMPENSATION
);

	// Default color preset for events being assigned to several users / none
$CONFIG['EXT']['calendar']['defaultEventColors'] = array(
	'id'		=> -1,
	'border'	=> '#555555',
	'text'		=> '#000000',
	'faded'		=> '#555555',
);

	// Configure portal's events type tab, it's renderer, entries counter
$CONFIG['EXT']['portal']['typetab']['calendar']			= 'calendar';
$CONFIG['EXT']['portal']['typerenderer']['calendar']	= 'TodoyuCalendarManager::getPortalAppointmentList';
$CONFIG['EXT']['portal']['entriescounter']['calendar']	= 'TodoyuCalendarManager::getPortalAppointmentsAmount';


	// Additional portal tab eventslisting specific config
$CONFIG['EXT']['portal']['tabcontentconfig']['calendar'] = array(
		// Show coming-up holidays in events tab of portal?
	'showHolidays'				=> true,
	'showBirthdays'				=> true,

		// How many weeks to look ahead for coming-up holidays to be listed in events tab of portal?
	'holidaysLookAheadWeeks'	=> 4,
	'birthdaysLookAheadWeeks'	=> 4,
);

	// Default values for event editing
$CONFIG['EXT']['calendar']['default']['timeStart']		= 28800;	// 08:00
$CONFIG['EXT']['calendar']['default']['eventDuration']	= 3600;		// 1 hour

	// Register contextmenu
//TodoyuContextMenuManager::registerFunction('Calendar', 'TodoyuPanelWidgetEventtypeSelector::getContextMenuItems', 10);

	// Add holiday set selector to company address form
if (TodoyuRequest::getArea() == 'contact' /*&& INTERNAL_COMPANY_ID == TodoyuRequest::getParam('editID', true)*/) {
	TodoyuFormHook::registerBuildForm('ext/contact/config/form/address.xml',		'TodoyuCalendarManager::modifyAddressFormfields');
}


?>