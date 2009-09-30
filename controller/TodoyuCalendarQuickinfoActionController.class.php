<?php

class TodoyuCalendarQuickinfoActionController extends TodoyuActionController {
	
	public function eventAction(array $params) {
		$idEvent	= intval($params['key']);
		$event		= TodoyuEventManager::getEventRecord($idEvent);
		
		$event['assignedUsers']	= TodoyuEventManager::getAssignedUsersOfEvent($idEvent);
		
		return render('ext/calendar/view/quickinfo-event.tmpl', $event);
	}
	
	public function holidayAction(array $params) {
		$dateStart	= intval($params['key']);
		$dateEnd	= $dateStart + 86400 -1;

		$holidays	= TodoyuCalendarManager::getHolidays($dateStart, $dateEnd);

		$data = array(
			'timestamp'	=> $dateStart,
			'holidays'	=> $holidays[ date('Ymd', $dateStart) ],
		);

		echo render('ext/calendar/view/quickinfo-holiday.tmpl', $data);
	}
		
}

?>