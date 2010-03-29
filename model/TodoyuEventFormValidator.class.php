<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
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
 * Event Form Validator
 *
 * @package			Todoyu
 * @subpackage		Calendar
 */


class TodoyuEventFormValidator {

	/**
	 * Check if the event is only assigned to the current person if the event is private
	 * defined in the $config array
	 *
	 * @param	String		$value			Assigned persons
	 * @param	Array		$config			Field config array
	 * @return	Bool
	 */
	public static function eventIsAssignableToCurrentPersonOnly($value, array $config = array ()) {
			// If the flag is_private is set, the event is only allowed to be assigned to the current person
		if ( $config['formdata']['is_private'] == 1 &&
			(count($config['formdata']['persons']) > 1 || $config['formdata']['persons']['0']['id'] != personid() ) ){

			return false;
		}
	}



	/**
	 * Check if the event starttime is befor endtime
	 *
	 * @param	String		$value			Assigned persons
	 * @param	Array		$config			Field config array
	 * @return	Bool
	 */
	public static function starttimeAfterEndtime($value, array $config = array ()) {
			// Only check this if it is not a fulldayevent
		if ( $config['formdata']['is_dayevent'] == 0) {
				// Convert dates and times to timestamps
			$starttime	= strtotime( $config['formdata']['startdate'] . ' ' . $config['formdata']['starttime'] );
			$endtime	= strtotime( $config['formdata']['enddate'] . ' ' . $config['formdata']['endtime'] );

				// Starttime must be before the endtime
			if ( $endtime <= $starttime ) {
				return false;
			}
		} else {
				// Convert dates to timestamps
			$startdate	= strtotime( $config['formdata']['startdate'] );
			$enddate	= strtotime( $config['formdata']['enddate'] );

			if ( $startdate > $enddate ) {
				return false;
			}
		}
	}



	 /**
	 * Check wheter the time format is correct
	 *
	 * @param	String		$value			Assigned persons
	 * @param	Array		$config			Field config array
	 * @return	Bool
	 */
	 public static function checkTimeFormat($value, array $config = array ()) {
			// Build regular expression
		$regexp	= '(\d{1,2}(\:|\s)\d{1,2})';

			// Check starttime format with regular expression
		if ( ! preg_match( $regexp, $config['formdata']['starttime'] ) ) {
			return false;
		} elseif ( ! preg_match( $regexp, $config['formdata']['endtime'] ) ) {
			return false;
		}
	}



	/**
	 * Check given persons of event being assignable, call hooked validators
	 *
	 * @param	String				$value
	 * @param	Array				$config
	 * @param	TodoyuFormElement	$formElement
	 * @param	Array				$formData
	 * @return	Boolean
	 */
	public static function personsAreBookable($value, array $config = array (), $formElement, $formData) {
		$bookable	= true;

			// Check if calendar is configured to prevent overbooking
		if ( ! TodoyuCalendarManager::isOverbookingAllowed() ) {
				// Check which (any?) event persons are overbooked
			$idEvent	= intval($formData['id']);
			$event		= $formData;

			if ( ! in_array($event['eventtype'], Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_OVERBOOKABLE']) ) {
				$personIDs	= TodoyuArray::getColumn($value, 'id');
				$personIDs	= TodoyuArray::intval($personIDs, true, true);

				$overbookedInfos	= TodoyuEventManager::getOverbookingInfos($event['date_start'], $event['date_end'], $personIDs, $idEvent);

				if( sizeof($overbookedInfos) > 0 ) {
					$bookable	= false;

					$tmpl	= 'ext/calendar/view/overbooking-info.tmpl';
					$data	= array(
						'overbooked'	=> $overbookedInfos
					);

					$error	= render($tmpl, $data);

					$formElement->setErrorMessage($error);
				}
			}
		}

		return $bookable;
	}

}

?>