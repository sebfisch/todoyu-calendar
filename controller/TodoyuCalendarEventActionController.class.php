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
	 * Initialize (restrict rights)
	 */
	public function init() {
		restrict('calendar', 'use');
	}



	/**
	 * Edit an event. If event ID is 0, a empty form is rendered to create a new event
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function editAction(array $params) {
		$idEvent	= intval($params['event']);
		$time		= intval($params['time']);
		$event		= TodoyuEventManager::getEvent($idEvent);

			// Check rights
		if( $idEvent === 0 ) {
			restrict('calendar', 'event:add');
		} else {
			if( $event->isCurrentUserAssigned() ) {
				restrict('calendar', 'event:editAssigned');
			} else {
				restrict('calendar', 'event:editAll');
			}
		}

			// Send tab label
		if( $idEvent === 0 ) {
			$tabLabel	= Label('event.newevent');
		} else {
			$tabLabel	= Label('event.edit') . ': ' . TodoyuDiv::cropText($event->getTitle(), 20, '...', false);
		}

		TodoyuHeader::sendTodoyuHeader('tabLabel', $tabLabel);

		return TodoyuEventEditRenderer::renderEventForm($idEvent, $time);
	}



	/**
	 * Save event action: validate data and save or return failure feedback
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function saveAction(array $params) {
		$data	= $params['event'];
		$idEvent= intval($data['id']);

			// Check rights
		if( $idEvent === 0 ) {
				// New task
			restrict('calendar', 'event:add');
		} else {
				// Edit task
			$event	= TodoyuEventManager::getEvent($idEvent);

			if( $event->isCurrentUserAssigned() ) {
				restrict('calendar', 'event:editAssigned');
			} else {
				restrict('calendar', 'event:editAll');
			}
		}

			// Set form data
		$xmlPath	= 'ext/calendar/config/form/event.xml';
		$form		= TodoyuFormManager::getForm($xmlPath, $idEvent);

		$form->setFormData($data);

			// Send idTask header for javascript
		TodoyuHeader::sendTodoyuHeader('idEvent', $idEvent);

		if( $form->isValid() ) {
			$data	= $form->getStorageData();

				// Save or update event
			$idEvent= TodoyuEventManager::saveEvent($data);
			$event	= TodoyuEventManager::getEvent($idEvent);

			TodoyuHeader::sendTodoyuHeader('time', $event->get('date_start'));
			TodoyuHeader::sendTodoyuHeader('idEvent', $idEvent);
		} else {
			TodoyuHeader::sendTodoyuHeader('error', true);

			$form->setUseRecordID(false);

			return $form->render();
		}
	}



	/**
	 * 'delete' action method
	 *
	 * @param	Array	$params
	 */
	public function deleteAction(array $params) {
		$idEvent= intval($params['event']);

		$event	= TodoyuEventManager::getEvent($idEvent);

			// Check right
		if( $event->isCurrentUserAssigned() ) {
			restrict('calendar', 'deleteAssigned');
		} else {
			restrict('calendar', 'deleteAll');
		}

		TodoyuEventManager::deleteEvent($idEvent);
	}



	/**
	 * 'detail' action method
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function detailAction(array $params) {
		$idEvent= intval($params['event']);
		$event	= TodoyuEventManager::getEvent($idEvent);

		if( ! $event->isCurrentUserAssigned() ) {
			restrict('calendar', 'seeAll');
		}

		$data		= array(
			'event'			=> $event->getTemplateData(),
			'attendees'		=> TodoyuEventManager::getAssignedUsersOfEvent($idEvent, true),
			'user_create'	=> TodoyuUserManager::getUserArray($event['id_user_create']),
		);

		return TodoyuEventRenderer::renderEvent($data, 'list');
	}



	/**
	 * Acknowledge an (not seen) event
	 *
	 * @param	Array	$params
	 */
	public function acknowledgeAction(array $params) {
		$idEvent= intval($params['event']);
		$idUser	= intval($params['idUser']);

		TodoyuEventManager::acknowledgeEvent($idEvent, $idUser);
	}



	/**
	 * 'show' action method
	 *
	 * @param	Array $params
	 * @return	String
	 */
	public function showAction(array $params) {
		$idEvent	= intval($params['event']);
		$event		= TodoyuEventManager::getEvent($idEvent);

		if( ! $event->isCurrentUserAssigned() ) {
			restrict('calendar', 'seeAll');
		}

			// Send tab label
		$tabLabel	= TodoyuDiv::cropText($event->getTitle(), 20, '...', false);
		TodoyuHeader::sendTodoyuHeader('tabLabel', $tabLabel, true);

		return TodoyuEventRenderer::renderEventView($idEvent);
	}

}

?>