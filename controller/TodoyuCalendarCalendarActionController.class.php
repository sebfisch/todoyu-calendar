<?php

class TodoyuCalendarCalendarActionController extends TodoyuActionController {
	
	public function updateAction(array $params) {
		$time	= intval($params['time']);
		$tab	= $params['tab'];
		
		TodoyuPanelWidgetCalendar::saveDate($time);
		TodoyuCalendarPreferences::saveActiveTab($tab);
		
		return TodoyuCalendarRenderer::renderCalendar($time, $tab);	
	}
		
}

?>