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

	// Add main menu planning area entry
if( allowed('calendar', 'general:area') ) {
	TodoyuFrontend::addMenuEntry('planning', 'LLL:calendar.maintab.label', '?ext=calendar', 30);

		// Add sub entries: day, week and month
	TodoyuFrontend::addSubmenuEntry('planning', 'calendarDay', 'LLL:calendar.subMenuEntry.day', '?ext=calendar&tab=day', 62);
	TodoyuFrontend::addSubmenuEntry('planning', 'calendarWeek', 'LLL:calendar.subMenuEntry.week', '?ext=calendar&tab=week', 63);
	TodoyuFrontend::addSubmenuEntry('planning', 'calendarMonth', 'LLL:calendar.subMenuEntry.month', '?ext=calendar&tab=month', 64);
}

?>