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
 * Quick info action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarQuickinfoActionController extends TodoyuActionController {

	/**
	 * Get quickinfo for an event
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function eventAction(array $params) {
		$idEvent	= intval($params['key']);
		$event		= TodoyuEventManager::getEvent($idEvent);

		$quickInfo	= new TodoyuQuickinfo();

			// Build event infos: date, persons
		$dateInfo	= TodoyuEventViewHelper::getQuickinfoDateInfo($event);
		$personInfo	= TodoyuEventViewHelper::getQuickinfoPersonInfo($event);

		$quickInfo->addInfo('title', $event->getTitle(), 10);
		$quickInfo->addInfo('type', $event->getTypeLabel(), 20);
		$quickInfo->addInfo('date', $dateInfo, 30);

			// Add conditionally displayed (only if set) infos
		if ( $event->getPlace() !== '' ) {
			$quickInfo->addInfo('place', $event->getPlace(), 40);
		}

		$amountAssignedPersons	= count( $event->getAssignedPersonsData() );
		if ( $amountAssignedPersons > 0 ) {
			$quickInfo->addInfo('persons', $personInfo, 50);
		}

		$quickInfo->printInfoJSON();
	}



	/**
	 * Get quickinfo for a holiday
	 *
	 * @param	Array		$params
	 */
	public function holidayAction(array $params) {
		$timestamp	= intval($params['key']);
		$holidays	= TodoyuCalendarManager::getHolidaysForDay($timestamp);

		$holiday	= array_shift($holidays);

		$quickInfo	= new TodoyuQuickinfo();

		$quickInfo->addInfo('title', $holiday['title']);
		$quickInfo->addInfo('date', TodoyuTime::format($holiday['date'], 'date'));
		$quickInfo->addInfo('work', ($holiday['workingtime'] / 3600) . ' ' . Label('date.time.hours'));


		$quickInfo->printInfoJSON();
	}



	/**
	 * Get quickinfo for birthdays
	 *
	 * @param	Array		$params
	 */
	public function birthdayAction(array $params) {
		$idPerson	= intval($params['key']);
		$person		= TodoyuPersonManager::getPerson($idPerson);
		$viewDate	= TodoyuCalendarPreferences::getCalendarDate(AREA);

		$quickInfo	= new TodoyuQuickinfo();

		$birthday		= $person->getBirthday();
		$viewBirthday	= mktime(0, 0, 0, date('n', $birthday), date('j', $birthday), date('Y', $viewDate));
		$age			= date('Y', $viewDate) - date('Y', $birthday);

		$quickInfo->addInfo('name',		$person->getFullName());
		$quickInfo->addInfo('date',		TodoyuTime::format($viewBirthday, 'date'));
		$quickInfo->addInfo('birthday',	$age . ' ' . Label('calendar.yearsold'));

		$quickInfo->printInfoJSON();
	}

}

?>