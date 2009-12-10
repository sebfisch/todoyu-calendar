<?php

	// Add menu entries
if( allowed('calendar', 'use') ) {
	TodoyuFrontend::addMenuEntry('planning', 'LLL:calendar.maintab.label', '?ext=calendar', 50);
	TodoyuFrontend::addSubmenuEntry('planning', 'calendarDay', 'LLL:calendar.subMenuEntry.day', '?ext=calendar&tab=day', 62);
	TodoyuFrontend::addSubmenuEntry('planning', 'calendarWeek', 'LLL:calendar.subMenuEntry.week', '?ext=calendar&tab=week', 63);
	TodoyuFrontend::addSubmenuEntry('planning', 'calendarMonth', 'LLL:calendar.subMenuEntry.month', '?ext=calendar&tab=month', 64);
}

?>