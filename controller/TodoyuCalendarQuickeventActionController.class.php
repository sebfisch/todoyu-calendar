<?php

class TodoyuCalendarQuickeventActionController extends TodoyuActionController {

	public function popupAction(array $params) {
		$time	= intval($params['time']);

		return TodoyuEventRenderer::renderCreateQuickEvent($time);
	}


	/**
	 * Save quickevent (quickevent popup)
	 *
	 *
	 * @param array $params
	 * @return unknown
	 */
	public function saveAction(array $params) {
		$formData	= $params['quickevent'];
		$xmlPath	= 'ext/calendar/config/form/quickevent.xml';
		$form		= new TodoyuForm($xmlPath);
		$form		= TodoyuFormHook::callBuildForm($xmlPath, $form, 0);

		$form->setFormData($formData);

		if( $form->isValid() ) {
			$storageData	= $form->getStorageData();
//			$storageData	= TodoyuFormHook::callSaveData($xmlPath, $storageData, 0);

				// Save or update event
			$idEvent	= TodoyuEventManager::saveQuickEvent($storageData);
			$event		= TodoyuEventManager::getEvent($idEvent);

				// Send back start date to jump in the calendar view
			return $event->getStartDate();
		} else {
			TodoyuHeader::sendTodoyuErrorHeader();

			$form->setUseRecordID(false);

			return $form->render();
		}
	}

}

?>