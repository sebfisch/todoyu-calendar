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

class TodoyuCalendarQuickinfoManager {

	/**
	 * Setup event quickinfo
	 *
	 * @param	TodoyuQuickinfo		$quickInfo
	 * @param	Integer				$element
	 */
	public static function addQuickinfoEvent(TodoyuQuickinfo $quickInfo, $element) {
		$idEvent	= intval($element);

		$event			= TodoyuEventManager::getEvent($idEvent);
		$canSeeDetails	= TodoyuEventRights::isSeeDetailsAllowed($idEvent);

			// Build event infos: title, type, date, place, assigned persons
		$dateInfo	= TodoyuEventViewHelper::getQuickinfoDateInfo($event);
		$personInfo	= TodoyuEventViewHelper::getQuickinfoPersonInfo($event);
		$typeInfo	= TodoyuEventViewHelper::getQuickinfoTypeInfo($event);

			// Private event or no access?
		if( $canSeeDetails ) {
			$quickInfo->addInfo('title', $event->getTitle(), 10);

				// Add conditionally displayed (only if set) infos
			if( $event->getPlace() !== '' ) {
				if( $canSeeDetails ) {
					$quickInfo->addInfo('place', $event->getPlace(), 40);
				} else {
					$quickInfo->addInfo('place', Label('event.privateEvent.info'), 40);
				}
			}
		} else {
			$quickInfo->addInfo('title', '<' . Label('event.privateEvent.info') . '>', 10);
		}

		$quickInfo->addInfo('type',	$typeInfo, 20);
		$quickInfo->addInfo('date',	$dateInfo, 30);

		$amountAssignedPersons	= count( $event->getAssignedPersonsData() );
		if( $amountAssignedPersons > 0 ) {
			$quickInfo->addInfo('persons', $personInfo, 50);
		}
	}



	/**
	 * Setup holiday quickinfo
	 *
	 * @param	TodoyuQuickinfo		$quickInfo
	 * @param	Integer				$element
	 */
	public static function addQuickinfoHoliday(TodoyuQuickinfo $quickInfo, $element) {
		$timestamp	= intval($element);
		$holidays	= TodoyuCalendarManager::getHolidaysForDay($timestamp);

		$holiday	= array_shift($holidays);

		$quickInfo->addInfo('title', $holiday['title']);
		$quickInfo->addInfo('type', Label('calendar.holidayset.attr.holiday'));
		$quickInfo->addInfo('date', TodoyuTime::format($holiday['date'], 'date'));
		$quickInfo->addInfo('work', round($holiday['workingtime'] / 3600, 1) . ' ' . Label('date.time.hours'));
	}



	/**
	 * Setup birthday quickinfo
	 *
	 * @param	TodoyuQuickinfo		$quickInfo
	 * @param	Integer				$element
	 */
	public static function addQuickinfoBirthday(TodoyuQuickinfo $quickInfo, $element) {
		$idPerson	= intval($element);
		$person		= TodoyuPersonManager::getPerson($idPerson);
		$viewDate	= TodoyuCalendarPreferences::getCalendarDate(AREA);

		$age			= date('Y', $viewDate) - date('Y', $person->getBirthday());

		$quickInfo->addInfo('name',		TodoyuString::crop($person->getFullName(), 25, '...', false));
		$quickInfo->addInfo('date',		TodoyuTime::format($person->getBirthday(), 'date'));
		$quickInfo->addInfo('birthday',	$age . ' ' . Label('calendar.yearsold'));
	}

}

?>