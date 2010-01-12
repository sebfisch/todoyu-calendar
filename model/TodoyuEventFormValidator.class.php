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
 * Event Form Validator
 *
 * @package			Todoyu
 * @subpackage		Calendar
 */


class TodoyuEventFormValidator {

	/**
	 * Check if the event is only assigned to the current user if the event is private
	 * defined in the $config array
	 *
	 * @param	String		$value			Assigned users
	 * @param	Array		$config			Field config array
	 * @return	Bool
	 */
	public static function eventIsAssignableToCurrentUserOnly($value, array $config = array ()) {
			// If the flag is_private is set, the event is only allowed to be assigned to the current user
		if ( $config['formdata']['is_private'] == 1 &&
			(count($config['formdata']['user']) > 1 || $config['formdata']['user']['0']['id'] != userid() ) ){

			return false;
		}
	}



	/**
	 * Check if the event starttime is befor endtime
	 *
	 * @param	String		$value			Assigned users
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
	 * @param	String		$value			Assigned users
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
	 * Validate given users of event to be bookable at that time (if configured to be validated)
	 *
	 * @param	String				$value
	 * @param	Array				$config
	 * @param	TodoyuFormElement	$formElement
	 * @param	Array				$formData
	 */
	public static function usersAreBookable($value, array $config = array (), $formElement, $formData) {
		$bookable	= true;

			// Check if calendar is configured to prevent overbooking
		if ( ! TodoyuCalendarManager::isOverbookingAllowed() ) {
				// check which (any?) event users are overbooked
			$idEvent	= intval($formData['id']);
			$event		= TodoyuEventManager::getEvent($idEvent);

			$userIDs	= array();
			foreach ($value as $user) {
				$userIDs[]	= intval($user['id']);
			}

			$overbookedUsers	= TodoyuEventManager::getOverbookedEventUsers($userIDs, $event['date_start'], $event['date_end'], $idEvent);

			if ( count($overbookedUsers) > 0 ) {
				$errorMessage = Label('LLL:event.error.usersOverbooked') . '<br />';
				foreach($overbookedUsers as $idUser) {
					$errorMessage	.= TodoyuUserManager::getLabel($idUser) . '<br />';
				}
				$formElement->setErrorMessage($errorMessage);

				$bookable	= false;
			}
		}

		return $bookable;
	}

}

?>