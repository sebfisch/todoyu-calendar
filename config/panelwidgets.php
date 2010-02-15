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

	// Add default (for all users) panel widgets
TodoyuPanelWidgetManager::addDefaultPanelWidget('calendar', 'TodoyuPanelWidgetCalendar', 			10, $panelWidgetConfigCalendar);
TodoyuPanelWidgetManager::addDefaultPanelWidget('calendar', 'TodoyuPanelWidgetStaffSelector',		30,	$panelWidgetConfigStaffSelector );
TodoyuPanelWidgetManager::addDefaultPanelWidget('calendar', 'TodoyuPanelWidgetEventTypeSelector',	40,	$panelWidgetConfigEventTypeSelector );
TodoyuPanelWidgetManager::addDefaultPanelWidget('calendar', 'TodoyuPanelWidgetHolidaySetSelector',	50, $panelWidgetConfigHolidaySetSelector);

?>