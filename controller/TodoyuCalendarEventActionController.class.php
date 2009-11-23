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

	public function init() {
		restrict('calendar', 'use');
	}



	/**
	 *	'add' action method
	 *
	 *	@param	Array	$params
	 *	@return	String
	 */
	public function addAction(array $params) {
		restrict('calendar', 'event:add');

		$time	= TodoyuCalendarPreferences::getDate(AREA);

		return TodoyuEventEditRenderer::renderAddView($time);
	}



	/**
	 *	'edit' action method
	 *
	 *	@param	Array	$params
	 *	@return	String
	 */
	public function editAction(array $params) {


		$idEvent	= intval($params['event']);
		$time		= intval($params['time']);

		if( $idEvent === 0 ) {
			restrict('calendar', 'event:add');
		} else {
			restrict('calendar', 'event:edit');
		}



			// Send tab label
		if( $idEvent === 0 ) {
			$tabLabel	= 'New Event';
		} else {
			$event		= TodoyuEventManager::getEvent($idEvent);
			$tabLabel	= TodoyuDiv::cropText($event->getTitle(), 20, '...', false);
		}
		TodoyuHeader::sendTodoyuHeader('tabLabel', 'Edit: ' . $tabLabel);

		return TodoyuEventEditRenderer::renderEventForm($idEvent, $time);
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
		$form		= TodoyuFormManager::getForm($xmlPath, $idEvent);

		$form->setUseRecordID(false);
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

			// Send tab label
		$event		= TodoyuEventManager::getEvent($idEvent);
		$tabLabel	= TodoyuDiv::cropText($event->getTitle(), 20, '...', false);

		TodoyuHeader::sendTodoyuHeader('tabLabel', $tabLabel, true);


		return TodoyuEventRenderer::renderEventView($idEvent);
	}

}

?>