<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
class TodoyuEventViewHelper {

	/**
	 * Get Event types in a form-readable format
	 *
	 * @param 	TodoyuFormElement 	$field
	 * @return	Array
	 */
	public static function getEventTypeOptions(TodoyuFormElement $field) {
		$eventTypes	= TodoyuEventTypeManager::getEventTypes(true);
		$reform		= array(
			'index'	=> 'value',
			'label'	=> 'label'
		);

		return TodoyuArray::reform($eventTypes, $reform, false);
	}



	/**
	 * Build preformated date info for event quickinfo tooltip
	 *
	 * @param	TodoyuEvent		$event
	 * @return	String
	 */
	public static function getQuickinfoDateInfo($event) {
		if ( $event->isMultiDay() ) {
				// Define format for dayevents and multiday events
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

		return $dateInfo;
	}



	/**
	 * Build preformated person(s) info for event quickinfo tooltip
	 *
	 * @param	TodoyuEvent		$event
	 * @param	Integer			$maxLenPersonLabel
	 * @return	String
	 */
	public static function getQuickinfoPersonInfo($event) {
		$persons	= $event->getAssignedPersonsData();
		$personInfo	= array();

		foreach($persons as $person) {
			$label	= TodoyuPersonManager::getLabel($person['id']);

			$personInfo[]	= TodoyuString::crop($label, 20, '...', false);
		}

		return implode("\n", $personInfo);
	}



	/**
	 * Build pre formatted type info for event quickinfo tooltip
	 *
	 * @param	TodoyuEvent		$event
	 * @return	String
	 */
	public static function getQuickinfoTypeInfo($event) {
		$typeInfo	= $event->getTypeLabel();

		if ( $event->data['is_private'] === '1' ) {
			$typeInfo	.= ', ' . Label('event.attr.is_private');
		}

		return $typeInfo;
	}

}

?>