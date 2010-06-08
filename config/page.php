<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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

	// Add main menu planning area entry
if( allowed('calendar', 'general:area') ) {
	TodoyuFrontend::addMenuEntry('planning', 'LLL:calendar.maintab.label', '?ext=calendar', 30);

		// Add sub entries: day, week and month
	TodoyuFrontend::addSubmenuEntry('planning', 'calendarDay', 'LLL:calendar.subMenuEntry.day', '?ext=calendar&tab=day', 62);
	TodoyuFrontend::addSubmenuEntry('planning', 'calendarWeek', 'LLL:calendar.subMenuEntry.week', '?ext=calendar&tab=week', 63);
	TodoyuFrontend::addSubmenuEntry('planning', 'calendarMonth', 'LLL:calendar.subMenuEntry.month', '?ext=calendar&tab=month', 64);
}

if( TodoyuExtensions::isInstalled('portal') && allowed('calendar', 'general:use') ) {
	TodoyuPortalManager::addTab('appointment', 'TodoyuCalendarPortalRenderer::getAppointmentTabLabel', 'TodoyuCalendarPortalRenderer::getAppointmentTabContent', 50);
}

?>