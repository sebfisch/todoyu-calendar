<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
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



	// Set panelwidgets' configurations
$panelWidgetConfigCalendar				= array('class'	=> 'todoyuskin');
$panelWidgetConfigHolidaySetSelector	= array();
$panelWidgetConfigStaffSelector			= array('colorizePersonOptions' => true);
$panelWidgetConfigEventTypeSelector		= array('selectAllOnFirstRun'	=> true);

	// Add default (for all persons) panel widgets
TodoyuPanelWidgetManager::addDefaultPanelWidget('calendar', 'TodoyuPanelWidgetCalendar', 			10, $panelWidgetConfigCalendar);
TodoyuPanelWidgetManager::addDefaultPanelWidget('calendar', 'TodoyuPanelWidgetStaffSelector',		30,	$panelWidgetConfigStaffSelector );
TodoyuPanelWidgetManager::addDefaultPanelWidget('calendar', 'TodoyuPanelWidgetEventTypeSelector',	40,	$panelWidgetConfigEventTypeSelector );
TodoyuPanelWidgetManager::addDefaultPanelWidget('calendar', 'TodoyuPanelWidgetHolidaySetSelector',	50, $panelWidgetConfigHolidaySetSelector);

?>