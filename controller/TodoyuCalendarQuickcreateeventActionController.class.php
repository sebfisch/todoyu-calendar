<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2011, snowflake productions GmbH, Switzerland
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
 * Quickevent action controller
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
		restrict('calendar', 'general:use');
		restrictInternal();
	}



	/**
	 * Render quick event creation form in popUp
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function popupAction(array $params) {
		TodoyuEventRights::restrictAdd();

		return TodoyuEventRenderer::renderCreateQuickEvent();
	}



	/**
	 * Save quickEvent (quickEvent popUp)
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function saveAction(array $params) {
		TodoyuEventRights::restrictAdd();

		$formData	= $params['event'];

			// Get form object, call save hooks, set data
		$form		= TodoyuEventManager::getQuickCreateForm();
		$xmlPath	= 'ext/calendar/config/form/event.xml';
		$formData	= TodoyuFormHook::callSaveData($xmlPath, $formData, 0);
		$form->setFormData($formData);

		if( $form->isValid() ) {
			$storageData	= $form->getStorageData();

				// Save or update event
			$idEvent	= TodoyuEventManager::saveQuickEvent($storageData);
			$event		= TodoyuEventManager::getEvent($idEvent);
			$startDate	= $event->getStartDate();

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