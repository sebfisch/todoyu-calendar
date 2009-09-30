<?php

class TodoyuCalendarEventActionController extends TodoyuActionController {
	
	public function addAction(array $params) {
		$time	= TodoyuCalendarPreferences::getDate(AREA);
		
		return TodoyuEventEditRenderer::renderCreateEventMainContent($time);
	}
	
	public function editAction(array $params) {
		$idEvent	= intval($params['event']);
		
		return TodoyuEventEditRenderer::renderEditView($idEvent);
	}
	
	public function saveAction(array $params) {
		$eventData	= $params['event'];
		$idEvent	= intval($params['event']['id']);

		$xmlPath	= 'ext/calendar/config/form/event.xml';
		$form		= new TodoyuForm($xmlPath);
		$form->setUseRecordID(false);

		$form		= TodoyuFormHook::callBuildForm($xmlPath, $form, $idEvent);
		
		$form->setFormData($eventData);

			// Send idTask header for javascript
		TodoyuHeader::sendTodoyuHeader('idEvent', $idEvent);

		if( $form->isValid() ) {
			$eventData	= $form->getStorageData();
			$eventData	= TodoyuFormHook::callSaveData($xmlPath, $eventData, $idEvent);

				// Save or update event
			$idEvent= TodoyuEventManager::saveEvent($eventData);
			$event	= TodoyuEventManager::getEvent($idEvent);
			
			TodoyuHeader::sendTodoyuHeader('time', $event->get('date_start'));
		} else {
			TodoyuHeader::sendTodoyuHeader('error', true);
			return $form->render();
		}
	}
	
	
	public function deleteAction(array $params) {
		$idEvent = intval($params['event']);
		
		TodoyuEventManager::deleteEvent($idEvent);
	}
	
	public function detailAction(array $params) {
		$idEvent	= intval($params['eventID']);
		
		switch( $params['mode'] ) {
			case 'listing':
				$event	= TodoyuEventManager::getEvent($idEvent);
				$data	= $event->getTemplateData();
				
				return TodoyuEventRenderer::renderEvent($data, 'list');
				break;

				// Default: calendar view
			case 'calendar':
			default:
					// ...
				break;
		}
	}
	
	public function acknowledgeAction(array $params) {
		$idEvent= intval($params['eventID']);
		$idUser	= intval($params['idUser']);
		
		TodoyuEventManager::acknowledgeEvent($idEvent, $idUser);

		TodoyuHeader::sendTodoyuHeader('idEvent', $idEvent);
		
		echo 'ok';
	}
	
	
	
	
	public function showAction(array $params) {
		$idEvent	= intval($params['event']);
		
		return TodoyuEventRenderer::renderEventView($idEvent);
	}
	
}

?>