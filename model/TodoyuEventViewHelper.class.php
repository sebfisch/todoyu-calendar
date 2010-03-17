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
	 * @param	TodoyuEvent	$event
	 * @return	String
	 */
	public static function getQuickinfoDateInfo($event) {
		if ( $event->isMultiDay() ) {
			$dateInfo  = TodoyuTime::format($event->getStartDate(), 'D2MshortTime');
			$dateInfo .= '<br />';
			$dateInfo .= TodoyuTime::format($event->getEndDate(), 'D2MshortTime');
		} else {
			$dateInfo  = TodoyuTime::format($event->getStartDate(), 'D2MshortTime');
			$dateInfo .= ' - ';
			$dateInfo .= TodoyuTime::format($event->getEndDate(), 'time');
		}

		return $dateInfo;
	}



	/**
	 * Build preformated person(s) info for event quickinfo tooltip
	 *
	 * @param	TodoyuEvent	$event
	 * @return	String
	 */
	public static function getQuickinfoPersonInfo($event) {
		$persons	= $event->getAssignedPersonsData();
		$personInfo	= array();

		foreach($persons as $person) {
			$personInfo[] = TodoyuPersonManager::getLabel($person['id']);
		}

		return implode('<br />', $personInfo);
	}

}

?>