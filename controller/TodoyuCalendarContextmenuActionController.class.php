<?php

class TodoyuCalendarContextmenuActionController extends TodoyuActionController {
	
	public function init(array $params) {
		TodoyuHeader::sendHeaderJSON();
	}
	
	public function areaAction(array $params) {
		$time		= intval($params['time']);
		$contextMenu= new TodoyuContextMenu('CalendarArea', $time);

		return $contextMenu->getJSON();			
	}
	
	public function eventAction(array $params) {
		$idEvent	= intval($params['event']);
		$contextMenu= new TodoyuContextMenu('Event', $idEvent);
		
		return $contextMenu->getJSON();		
	}
		
}

?>