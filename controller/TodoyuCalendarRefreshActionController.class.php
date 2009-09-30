<?php

class TodoyuCalendarRefreshActionController extends TodoyuActionController {
		
	public function defaultAction(array $params) {
		$timestamp	= intval($params['date']);
		$activeTab	= $params['tab']; // Tabs are named 'day', 'week' or 'month'
		
			// Save pref
		TodoyuCalendarPreferences::saveDate($timestamp, EXTID_CALENDAR);
		
			// Display output
		return TodoyuCalendarRenderer::renderCalendar($timestamp, $activeTab);
	}
			
}

?>