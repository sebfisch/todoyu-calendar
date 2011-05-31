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
 * Event view helper
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarEventViewHelper {

	/**
	 * Get event types (sorted by label) in a form-readable format
	 *
	 * @param	TodoyuFormElement 	$field
	 * @return	Array
	 */
	public static function getEventTypeOptions(TodoyuFormElement $field) {
		$eventTypes		= TodoyuCalendarEventTypeManager::getEventTypes(true);
		$reformConfig	= array(
			'index'	=> 'value',
			'label'	=> 'label'
		);
		$eventTypes	= TodoyuArray::reform($eventTypes, $reformConfig, false);

		return TodoyuArray::sortByLabel($eventTypes, 'label');
	}



	/**
	 * Build pre-formatted date info for event quickinfo tooltip
	 *
	 * @param	TodoyuCalendarEvent		$event
	 * @param	Boolean					$withDuration
	 * @return	String
	 */
	public static function getQuickinfoDateInfo($event, $withDuration = false) {
		if( $event->isMultiDay() ) {
				// Define format for day-events and multi-day events
			if( $event->isDayevent() ) {
				$break	= ' - ';
				$format	= 'MlongD2';
			} else {
				$break	= "\n";
				$format	= 'D2MshortTime';
			}
			$dateInfo  = TodoyuTime::format($event->getStartDate(), $format);
			$dateInfo .= $break;
			$dateInfo .= TodoyuTime::format($event->getEndDate(), $format);
		} else {
				// Normal in-day event
			$dateInfo  = TodoyuTime::format($event->getStartDate(), 'D2MshortTime');
			$dateInfo .= ' - ';
			$dateInfo .= TodoyuTime::format($event->getEndDate(), 'time');
		}

		if( $withDuration ) {
			$dateInfo .= ' (' . TodoyuTime::formatDuration($event->getDuration()) . ')';
		}

		return $dateInfo;
	}



	/**
	 * Build pre-formatted person(s) info for event quickinfo tooltip
	 *
	 * @param	TodoyuCalendarEvent		$event
	 * @return	String
	 */
	public static function getQuickinfoPersonInfo($event) {
		$persons	= $event->getAssignedPersonsData();
		$personInfo	= array();

		foreach($persons as $person) {
			$label	= TodoyuContactPersonManager::getLabel($person['id']);
			$label	= TodoyuString::crop($label, 25, '...', false);

				// Add person label, linked to contacts detail view if allowed to be seen
			if( Todoyu::allowed('contact', 'general:area') ) {
				$linkParams	= array(
					'ext'		=> 'contact',
					'controller'=> 'person',
					'action'	=> 'detail',
					'person'	=> $person['id'],
				);
				$linkedLabel		= TodoyuString::wrapTodoyuLink($label, 'contact', $linkParams);
				$personInfo[]	= $linkedLabel;
			} else {
				$personInfo[]	= $label;
			}
		}

		return implode("\n", $personInfo);
	}



	/**
	 * Build pre formatted type info for event quickinfo tooltip
	 *
	 * @param	TodoyuCalendarEvent		$event
	 * @return	String
	 */
	public static function getQuickinfoTypeInfo($event) {
		$typeInfo	= $event->getTypeLabel();

		if( $event->isPrivate() ) {
			$typeInfo	.= ', ' . Todoyu::Label('calendar.event.attr.is_private');
		}

		return $typeInfo;
	}



	/**
	 * Get option array of persons which can receive the event email (participant with an email address)
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getEmailReceiverOptions(TodoyuFormElement $field) {
		$idEvent	= intval($field->getForm()->getHiddenField('id_event'));
		$options	= array();
		$persons	= TodoyuCalendarEventManager::getEmailReceivers($idEvent, true);

		foreach($persons as $person) {
			$options[] 	= array(
				'value'		=> $person['id'],
				'label'		=> TodoyuContactPersonManager::getLabel($person['id'], true, true),
			);
		}

		return $options;
	}



	/**
	 * Get option array of persons which can receive the event email (participant with an email address)
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getEmailReceiverGroupedOptions(TodoyuFormElement $field) {
		$options	= array();

			// Event attending persons
		$groupLabel	= Todoyu::Label('calendar.event.group.attendees');
		$options[$groupLabel]	= self::getEmailReceiverOptions($field);

			// Get staff persons (employees of internal company)
		$groupLabel	= Todoyu::Label('comment.ext.group.employees');
		$options[$groupLabel]	= TodoyuContactViewHelper::getInternalPersonOptions($field, true);

		return $options;
	}

}

?>