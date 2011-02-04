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
		restrict('calendar', 'general:use');
		restrictInternal();
	}



	/**
	 * Edit an event. If event ID is 0, a empty form is rendered to create a new event
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function editAction(array $params) {
		$idEvent	= intval($params['event']);
		$time		= strtotime($params['date']);
		$event		= TodoyuEventManager::getEvent($idEvent);

			// Check rights
		if( $idEvent === 0 ) {
			restrict('calendar', 'event:add');
			$tabLabel	= Label('event.new');
		} else {
			TodoyuEventRights::restrictEdit($idEvent);
			$tabLabel	= Label('event.edit') . ': ' . TodoyuString::crop($event->getTitle(), 20, '...', false);
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
				// New event
			restrict('calendar', 'event:add');
		} else {
				// Edit event
			TodoyuEventRights::restrictEdit($idEvent);
		}

			// Set form data
		$xmlPath	= 'ext/calendar/config/form/event.xml';
		$form		= TodoyuFormManager::getForm($xmlPath, $idEvent);

		$form->setFormData($data);

			// Send idTask header for JavaScript
		TodoyuHeader::sendTodoyuHeader('idEvent', $idEvent);

		if( $form->isValid() ) {
			$needToShowWarning	= false;

				// Check for (allowed) entered overbooking, ask user to confirm saving if any occurs
			$isOverbookingConfirmed	= intval($params['isOverbookingConfirmed']);
			if( TodoyuCalendarManager::isOverbookingAllowed() && ! $isOverbookingConfirmed ) {
				$overbookedWarning	= TodoyuEventManager::getOverbookingWarning($idEvent, $data);
				if( ! empty($overbookedWarning) ) {
					$needToShowWarning	= true;
					TodoyuHeader::sendTodoyuHeader('overbookingwarning', $overbookedWarning);

					$overboookingWarningInline	= TodoyuEventManager::getOverbookingWarning($idEvent, $data, false);
					TodoyuHeader::sendTodoyuHeader('overbookingwarningInline', $overboookingWarningInline);
				}
			}

				// Save or update event
			if( $needToShowWarning === false ) {
				$data	= $form->getStorageData();

				$idEvent= TodoyuEventManager::saveEvent($data);
				$event	= TodoyuEventManager::getEvent($idEvent);

				TodoyuHeader::sendTodoyuHeader('time', $event->get('date_start'));
				TodoyuHeader::sendTodoyuHeader('idEvent', $idEvent);
			}
		} else {
				// Handle errors
			TodoyuHeader::sendTodoyuErrorHeader();

			$form->setUseRecordID(false);

			return $form->render();
		}
	}



	/**
	 * Save changed starting date when event has been dragged to a new position
	 *
	 * @param	Array		$params
	 */
	public function dragDropAction(array $params) {
		$idEvent	= intval($params['event']);
		$timeStart	= strtotime($params['date']);
		$tab		= trim($params['tab']);
		$isConfirmed= ( $params['confirmed'] == '1' ) ? true : false;

		$overbookings	= TodoyuEventManager::moveEvent($idEvent, $timeStart, $tab, $isConfirmed);

		if( is_array($overbookings) && ! $isConfirmed ) {
			if( ! TodoyuCalendarManager::isOverbookingAllowed() ) {
					// Overbooking forbidden - reset event to original time, show notification
				TodoyuHeader::sendTodoyuErrorHeader();
				return implode('<br />', $overbookings);
			} else {
					// Overbooking allowed - open popup with warning and confirmation dialog
				$overbookedWarning	= TodoyuEventManager::getOverbookingWarningAfterDrop($idEvent, $timeStart);
				TodoyuHeader::sendTodoyuHeader('overbookingwarning', $overbookedWarning);
			}
		}
	}



	/**
	 * Delete event
	 *
	 * @param	Array	$params
	 */
	public function deleteAction(array $params) {
		$idEvent= intval($params['event']);

		$event	= TodoyuEventManager::getEvent($idEvent);

			// Check right
		if( $event->isCurrentPersonAssigned() ) {
			restrict('calendar', 'event:editAndDeleteAssigned');
		} else {
			restrict('calendar', 'event:editAndDeleteAll');
		}

		TodoyuEventManager::deleteEvent($idEvent);
	}



	/**
	 * Get given event's rendered detail view for list mode
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function detailAction(array $params) {
		$idEvent= intval($params['event']);
		$event	= TodoyuEventManager::getEvent($idEvent);

		if( ! $event->isCurrentPersonAssigned() ) {
			restrict('calendar', 'event:seeAll');
		}

		return TodoyuEventRenderer::renderEventDetailsInList($idEvent);
	}



	/**
	 * Acknowledge an (not seen) event
	 *
	 * @param	Array	$params
	 */
	public function acknowledgeAction(array $params) {
		$idEvent	= intval($params['event']);
		$idPerson	= intval($params['person']);

		TodoyuEventManager::acknowledgeEvent($idEvent, $idPerson);
	}



	/**
	 * Show event details
	 *
	 * @param	Array 		$params
	 * @return	String
	 */
	public function showAction(array $params) {
		$idEvent	= intval($params['event']);
		$event		= TodoyuEventManager::getEvent($idEvent);

		if( ! $event->isCurrentPersonAssigned() ) {
			restrict('calendar', 'event:seeAll');
		}

			// Send tab label
		$tabLabel	= TodoyuString::crop($event->getTitle(), 20, '...', false);
		TodoyuHeader::sendTodoyuHeader('tabLabel', $tabLabel, true);

		return TodoyuEventRenderer::renderEventView($idEvent);
	}



	/**
	 * Render event form for use as sub form of another form
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function addSubformAction(array $params) {
		restrictIfNone('calendar', 'event:editAll,event:editAndDeleteAssigned');

		$index		= intval($params['index']);
		$fieldName	= $params['field'];
		$formName	= $params['form'];
		$idRecord	= intval($params['record']);

		$xmlPath	= 'ext/calendar/config/form/event.xml';
		$form 		= TodoyuFormManager::getForm($xmlPath, $index);

			// Load form data
		$formData	= $form->getFormData();
		$formData	= TodoyuFormHook::callLoadData($xmlPath, $formData, $idRecord);

		return TodoyuFormManager::renderSubFormRecord($xmlPath, $fieldName, $formName, $index, $idRecord, $formData);
	}

}

?>