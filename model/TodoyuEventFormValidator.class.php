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
	public static function eventIsAssignableToCurrentPersonOnly($value, array $config = array (), $formElement, $formData) {
			// If the flag is_private is set, the event is only allowed to be assigned to the current person

		if($formData['is_private'] == 1)	{
			if(count($formData['persons']) == 1)	{
				$person = array_shift($formData['persons']);
				if(intval($person['id']) !== personid())	{
					return false;
				}
			} else {
				return false;
			}
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



	/**
	 * Form validator.
	 * Check if at least one internal person is assigned to an event
	 *
	 * @param	Array				$value
	 * @param	Array				$config
	 * @param	TodoyuFormElement	$formElement
	 * @param	Array				$formData
	 * @return	Bool
	 */
	public static function hasInternalPerson($value, array $config = array (), $formElement, $formData) {
		$personIDs	= TodoyuArray::getColumn($value, 'id');

		if( sizeof($personIDs) === 0 ) {
			return false;
		}

		$fields	= '	c.id';
		$tables	= '	ext_contact_mm_company_person mmcp,
					ext_contact_company c';
		$where	= '	mmcp.id_person IN(' . implode(',', $personIDs) . ') AND
					mmcp.id_company	= c.id AND
					c.is_internal	= 1';
		$limit	= 1;

		return Todoyu::db()->hasResult($fields, $tables, $where, '', '', $limit);
	}

}

?>