<?php

	// Add menu entries
if( TodoyuAuth::isLoggedIn() ) {
	TodoyuFrontend::addMenuEntry('planning', 'LLL:calendar.maintab.label', '?ext=calendar', 50);
	TodoyuFrontend::addSubmenuEntry('planning', 'calendar', 'LLL:calendar.subMenuEntry.day', '?ext=calendar&tab=day', 62, 'day');
	TodoyuFrontend::addSubmenuEntry('planning', 'calendar', 'LLL:calendar.subMenuEntry.week', '?ext=calendar&tab=week', 63, 'week');
	TodoyuFrontend::addSubmenuEntry('planning', 'calendar', 'LLL:calendar.subMenuEntry.month', '?ext=calendar&tab=month', 64, 'month');
}

?>