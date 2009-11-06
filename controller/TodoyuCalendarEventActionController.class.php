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
 * Event action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

class TodoyuCalendarEventActionController extends TodoyuActionController {



	/**
	 *	'add' action method
	 *
	 *	@param	Array	$params
	 *	@return	String
	 */
	public function addAction(array $params) {
		$time	= TodoyuCalendarPreferences::getDate(AREA);

		return TodoyuEventEditRenderer::renderCreateEventMainContent($time);
	}



	/**
	 *	'edit' action method
	 *
	 *	@param	Array	$params
	 *	@return	String
	 */
	public function editAction(array $params) {
		$idEvent	= intval($params['event']);

		return TodoyuEventEditRenderer::renderEditView($idEvent);
	}



	/**
	 *	'save' action method
	 *
	 *	@param	Array	$params
	 *	@return	String
	 */
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



	/**
	 *	'delete' action method
	 *
	 *	@param	Array	$params
	 */
	public function deleteAction(array $params) {
		$idEvent = intval($params['event']);

		TodoyuEventManager::deleteEvent($idEvent);
	}



	/**
	 *	'detail' action method
	 *
	 *	@param	Array	$params
	 *	@return	String
	 */
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



	/**
	 *	'acknowledge' action method
	 *
	 *	@param	Array	$params
	 *	@todo	remove echo / finish implementation
	 */
	public function acknowledgeAction(array $params) {
		$idEvent= intval($params['eventID']);
		$idUser	= intval($params['idUser']);

		TodoyuEventManager::acknowledgeEvent($idEvent, $idUser);

		TodoyuHeader::sendTodoyuHeader('idEvent', $idEvent);

		echo 'ok';
	}



	/**
	 *	'show' action method
	 *
	 *	@param	Array $params
	 *	@return	String
	 */
	public function showAction(array $params) {
		$idEvent	= intval($params['event']);

		return TodoyuEventRenderer::renderEventView($idEvent);
	}

}

?>