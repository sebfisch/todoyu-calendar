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
 * Configure panel widgets to be shown in Calendar area
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

	// PanelWidgets configurations
$panelWidgetConfigCalendar			= array('class'	=> 'todoyuskin');
$panelWidgetConfigStaffSelector		= array('colorizePersonOptions' => true);
$panelWidgetConfigEventTypeSelector	= array('selectAllOnFirstRun'	=> true);

	// Add default (for all persons) panel widgets
TodoyuPanelWidgetManager::addPanelWidget('calendar', 'calendar', 'Calendar', 			10, $panelWidgetConfigCalendar);
TodoyuPanelWidgetManager::addPanelWidget('calendar', 'contact', 'StaffSelector',			20);
//TodoyuPanelWidgetManager::addDefaultPanelWidget('calendar', 'contact', 'StaffSelectorOLD',		30,	$panelWidgetConfigStaffSelector );
TodoyuPanelWidgetManager::addPanelWidget('calendar', 'calendar', 'EventTypeSelector',	40,	$panelWidgetConfigEventTypeSelector );
TodoyuPanelWidgetManager::addPanelWidget('calendar', 'calendar', 'HolidaySetSelector',	50);

?>