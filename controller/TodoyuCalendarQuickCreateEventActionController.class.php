<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 snowflake productions gmbh
*  All rights reserved
*
*  This script is part of the todoyu project.
*  The todoyu project is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License, version 2,
*  (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html) as published by
*  the Free Software Foundation;
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Quickevent action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarQuickCreateEventActionController extends TodoyuActionController {

	/**
	 * Render quick event creation form in popup
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function popupAction(array $params) {
		restrict('calendar', 'event:add');

		return TodoyuEventRenderer::renderCreateQuickEvent();
	}



	/**
	 * Save quickevent (quickevent popup)
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function saveAction(array $params) {
		restrict('calendar', 'event:add');

		$formData	= $params['quickcreateevent'];
		$xmlPath	= 'ext/calendar/config/form/quickcreateevent.xml';
		$form		= TodoyuFormManager::getForm($xmlPath);

		$form->setFormData($formData);

		if ( $form->isValid() ) {
			$storageData	= $form->getStorageData();

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