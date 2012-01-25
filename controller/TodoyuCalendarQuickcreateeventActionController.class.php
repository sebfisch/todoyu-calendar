<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2012, snowflake productions GmbH, Switzerland
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSD License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/

/**
 * Quick-Event action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarQuickCreateEventActionController extends TodoyuActionController {

	/**
	 * Initialize calendar default action: check permission
	 *
	 * @param	Array	$params
	 */
	public function init(array $params) {
		Todoyu::restrict('calendar', 'general:use');
		Todoyu::restrictInternal();
	}



	/**
	 * Render quick event creation form in popUp
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function popupAction(array $params) {
		TodoyuCalendarEventRights::restrictAdd();

		return TodoyuCalendarEventRenderer::renderCreateQuickEvent();
	}



	/**
	 * Save quick-event
	 *
	 * @param	Array			$params
	 * @return	Void|String		Failure returns re-rendered form with error messages
	 */
	public function saveAction(array $params) {
		TodoyuCalendarEventRights::restrictAdd();

		$isNewEvent	= true;
		$data		= $params['event'];

		$emailReceiverIDs	= $data['emailreceivers'];
		$sendAsMail			= $data['sendasemail'];

			// Get form object, call save hooks, set data
		$form	= TodoyuCalendarEventStaticManager::getQuickCreateForm();
		$form->setFormData($data);

		if( $form->isValid() ) {
			$storageData	= $form->getStorageData();

				// Save or update event
			$idEvent	= TodoyuCalendarEventStaticManager::saveEvent($storageData);

				// Send event auto-email to preset receivers: those get emails concerning all new/changed events they participate in
				// Watch out: persons only have their ID as key when editing a pre-existing event!
			$participantIDs		= TodoyuArray::intval( TodoyuArray::flatten($data['persons']) );
			$autoMailPersonIDs	= TodoyuCalendarEventMailManager::getAutoNotifiedPersonIDs($participantIDs);

			if( ! empty($autoMailPersonIDs) ) {
				if( TodoyuCalendarEventMailManager::sendEvent($idEvent, $autoMailPersonIDs, $isNewEvent) ) {
					TodoyuHeader::sendTodoyuHeader('sentAutoEmail', true);

						// Don't double-send: remove auto-mail receivers from manual receivers list
					if( is_array($emailReceiverIDs) ) {
						$emailReceiverIDs	= array_diff($emailReceiverIDs, $autoMailPersonIDs);
					}
				}
			}

				// Send event email to selected receivers
			if( $sendAsMail && sizeof($emailReceiverIDs) > 0 ) {
				if( TodoyuCalendarEventMailManager::sendEvent($idEvent, $emailReceiverIDs, $isNewEvent) ) {
					TodoyuHeader::sendTodoyuHeader('sentEmail', true);
				}
			}

			$event		= TodoyuCalendarEventStaticManager::getEvent($idEvent);
			$startDate	= $event->getDateStart();

			TodoyuHeader::sendTodoyuHeader('idEvent', $idEvent);

				// Send back start date to enable jumping in the calendar view
			TodoyuHeader::sendTodoyuHeader('startDate', $startDate);
		} else {
			TodoyuHeader::sendTodoyuErrorHeader();

			$form->setUseRecordID(false);

			return $form->render();
		}
	}

}

?>