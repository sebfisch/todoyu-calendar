<?php

class TodoyuCalendarPreferenceActionController extends TodoyuActionController {
	
	protected $value	= '';
	
	protected $item		= 0;
	
	
	public function init(array $params) {
		$this->value	= $params['value'];
		$this->item		= intval($params['item']);
	}
	
	public function tabAction(array $params) {
		$tabKey	= trim($params['tab']);
		
		TodoyuCalendarPreferences::saveActiveTab($tabKey);
	}
	
	public function dateAction(array $params) {
		$date	= intval($this->value);
		
		TodoyuCalendarPreferences::saveDate($date, AREA);
	}
	
	
	public function panelwidgeteventtypeselectorAction(array $params) {
		$eventTypes	= TodoyuDiv::intExplode(',', $this->value, true, true);
		
		TodoyuCalendarPreferences::saveEventtypes($eventTypes);
	}
	
	public function panelwidgetholidaysetselectorAction(array $params) {
		$holidaySets	= TodoyuDiv::intExplode(',', $this->value, true, true);
		
		TodoyuCalendarPreferences::saveHolidaysets($holidaySets);
	}
	
	public function fulldayviewAction(array $params) {
		$fullDay	= intval($this->value) === 1;
		
		TodoyuCalendarPreferences::saveFullDayView($fullDay);
	}
	
	public function pwidgetAction(array $params) {
		TodoyuPanelWidgetManager::saveCollapsedStatus(EXTID_CALENDAR, $this->item, $this->value);
	}
	
	
}

?>