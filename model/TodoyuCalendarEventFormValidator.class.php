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
 * Event Form Validator
 *
 * @package			Todoyu
 * @subpackage		Calendar
 */


class TodoyuCalendarEventFormValidator {

	/**
	 * Check whether the event is only assigned to the current person if the event is private
	 * defined in the $config array
	 *
	 * @param	String		$value			Assigned persons
	 * @param	Array		$config			Field config array
	 * @param	String		$formElement
	 * @param	Array		$formData
	 * @return	Boolean
	 */
	public static function eventIsAssignableToCurrentPersonOnly($value, array $config = array (), $formElement, $formData) {
			// If the flag is_private is set, the event is only allowed to be assigned to the current person
		if( $formData['is_private'] == 1 ) {
			if( count($formData['persons']) == 1 ) {
				$person = array_shift($formData['persons']);
				if( intval($person['id']) !== Todoyu::personid() ) {
					return false;
				}
			} else {
				return false;
			}
		}

		return true;
	}



	/**
	 * Check that the event's starting time lays before it's ending time
	 *
	 * @param	String		$value			Assigned persons
	 * @param	Array		$config			Field config array
	 * @return	Boolean
	 */
	public static function starttimeAfterEndtime($value, array $config = array ()) {
			// Only check this if it is not a full-day event
		if( $config['formdata']['is_dayevent'] == 0) {
				// Convert dates and times to timestamps
			$timeStart	= strtotime( $config['formdata']['startdate'] . ' ' . $config['formdata']['starttime'] );
			$timeEnd	= strtotime( $config['formdata']['enddate'] . ' ' . $config['formdata']['endtime'] );

				// Start time must be before the end time
			if( $timeEnd <= $timeStart ) {
				return false;
			}
		} else {
				// Convert dates to timestamps
			$dateStart	= strtotime( $config['formdata']['startdate'] );
			$dateEnd	= strtotime( $config['formdata']['enddate'] );

			if( $dateStart > $dateEnd ) {
				return false;
			}
		}
	}



	 /**
	 * Check whether the time format is correct
	 *
	 * @param	String		$value			Assigned persons
	 * @param	Array		$config			Field config array
	 * @return	Boolean
	 */
	 public static function checkTimeFormat($value, array $config = array ()) {
			// Build regular expression
		$regExp	= '(\d{1,2}(\:|\s)\d{1,2})';

			// Check format of starting and ending time with regular expression
		if( ! preg_match( $regExp, $config['formdata']['starttime'] ) ) {
			return false;
		} elseif( ! preg_match( $regExp, $config['formdata']['endtime'] ) ) {
			return false;
		}

		return true;
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
			// Check if calendar is configured to prevent overbooking
		if( ! TodoyuCalendarManager::isOverbookingAllowed() ) {
				// Check which (any?) event persons are overbooked
			$idEvent		= intval($formData['id']);
			$idEventType	= intval($formData['eventtype'][0]);
			$overbookableEventTypes	= TodoyuArray::assure(Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_OVERBOOKABLE']);
			$isDayEvent				= intval($formData['is_dayevent']) === 1;

			if( ! $isDayEvent && ! in_array($idEventType, $overbookableEventTypes) ) {
				$personIDs		= TodoyuArray::intval(TodoyuArray::getColumn($value, 'id'), true, true);
				$overbookedInfos= TodoyuCalendarEventManager::getOverbookingInfos($formData['date_start'], $formData['date_end'], $personIDs, $idEvent);

				if( sizeof($overbookedInfos) > 0 ) {
					self::setOverbookingError($formElement, $overbookedInfos);
					return false;
				}
			}
		}

		return true;
	}



	/**
	 * Render overbooking error message and set in event form
	 *
	 * @param	TodoyuFormElement	$formElement
	 * @param	Array				$overbookedInfos
	 */
	private static function setOverbookingError(TodoyuFormElement $formElement, array $overbookedInfos) {
		$tmpl	= 'ext/calendar/view/overbooking-info.tmpl';
		$data	= array(
			'overbooked'	=> $overbookedInfos
		);

		$error	= Todoyu::render($tmpl, $data);
		$formElement->setErrorMessage($error);
	}



	/**
	 * Form validator.
	 * Check whether at least one internal person is assigned to an event
	 *
	 * @param	Array				$value
	 * @param	Array				$config
	 * @param	TodoyuFormElement	$formElement
	 * @param	Array				$formData
	 * @return	Boolean
	 */
	public static function hasInternalPerson($value, array $config = array (), $formElement, $formData) {
		$personIDs	= TodoyuArray::getColumn($value, 'id');

		if( sizeof($personIDs) === 0 ) {
			return false;
		}

		$personIDs	= TodoyuArray::intImplode($personIDs);

		$fields	= '	c.id';
		$tables	= '	ext_contact_mm_company_person mmcp,
					ext_contact_company c';
		$where	= '		mmcp.id_person IN(' . $personIDs . ') '
				. '	AND	mmcp.id_company	= c.id '
				. '	AND	c.is_internal	= 1';
		$limit	= 1;

		return Todoyu::db()->hasResult($fields, $tables, $where, '', '', $limit);
	}

}

?>