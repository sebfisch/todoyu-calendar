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
		$quickInfo	= new TodoyuCalendarQuickinfo();

			// Build event date info
		if ( $event->isMultiDay() ) {
			$dateInfo  = TodoyuTime::format($event->getStartDate(), 'D2MshortTime');
			$dateInfo .= '<br />';
			$dateInfo .= TodoyuTime::format($event->getEndDate(), 'D2MshortTime');
		} else {
			$dateInfo  = TodoyuTime::format($event->getStartDate(), 'D2MshortTime');
			$dateInfo .= ' - ';
			$dateInfo .= TodoyuTime::format($event->getEndDate(), 'time');
		}

			// Build users info
		$usersInfo	= array();
		$users		= $event->getAssignedUserData();

		foreach($users as $user) {
			$usersInfo[] = '- ' . TodoyuUserManager::getLabel($user['id']);
		}

		$quickInfo->addInfo('title', $event->getTitle(), 10);
		$quickInfo->addInfo('type', $event->getTypeLabel(), 20);
		$quickInfo->addInfo('date', $dateInfo, 30);

		if ( $event->getPlace() !== '' ) {
			$quickInfo->addInfo('place', $event->getPlace(), 40);
		}

			// Only add users info when assigned to at least one user
		if ( sizeof($usersInfo) > 0 ) {
			$quickInfo->addInfo('users', implode('<br />', $usersInfo), 50);
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

		$quickInfo	= new TodoyuCalendarQuickinfo();

		$quickInfo->addInfo('title', $holiday['title']);
		$quickInfo->addInfo('date', TodoyuTime::format($holiday['date'], 'date'));
		$quickInfo->addInfo('work', $holiday['workingtime'] . ' ' . Label('date.time.hours'));


		$quickInfo->printInfoJSON();
	}



	/**
	 * Get quickinfo for birthdays
	 *
	 * @param	Array		$params
	 */
	public function birthdayAction(array $params) {
		$idUser		= intval($params['key']);
		$user		= TodoyuUserManager::getUser($idUser);
		$viewDate	= TodoyuCalendarPreferences::getCalendarDate(AREA);

		$quickInfo	= new TodoyuCalendarQuickinfo();

		$birthday		= $user->getBirthday();
		$viewBirthday	= mktime(0, 0, 0, date('n', $birthday), date('j', $birthday), date('Y', $viewDate));
		$age			= date('Y', $viewDate) - date('Y', $birthday);

		$quickInfo->addInfo('name', $user->getFullName());
		$quickInfo->addInfo('date', TodoyuTime::format($viewBirthday, 'date'));
		$quickInfo->addInfo('age', $age . ' ' . Label('calendar.yearsOld'));

		$quickInfo->printInfoJSON();
	}

}

?>