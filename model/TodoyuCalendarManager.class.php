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
 * Calendar Manager
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarManager {

	/**
	 * Render function for portal calendar tab
	 *
	 * @return String
	 */
	public static function getPortalAppointmentList() {
		$idUser	= userid();

			// Have calendar JS and CSS registered (for correct style and context menu handling)
		TodoyuPage::addExtAssets('calendar', 'public');

		$today		=	mktime(0, 0, 0, date('n', NOW), date('j', NOW), date('Y', NOW));
		$dateStart	= TodoyuTime::getStartOfDay(NOW);
		$dateEnd	= NOW + 2 * 365 * 24 * 3600;

		$events	= TodoyuEventManager::getEventsInTimespan($dateStart, $dateEnd, array(userid()));
		// @todo	add fetch/ mergin of day-events / non-day-events

		if ($GLOBALS['CONFIG']['EXT']['portal']['tabcontentconfig']['calendar']['showHolidays']) {
				// Get holidays within the next X (see calendar/config/extension.php) weeks
			$amountWeeksToLookToForHolidays = $GLOBALS['CONFIG']['EXT']['portal']['tabcontentconfig']['calendar']['holidaysLookAheadWeeks'];
			$endTime	= $today + $amountWeeksToLookToForHolidays * 604800;	// 604800 == 24 * 60 * 60 * 7
			$holidays	= TodoyuHolidayManager::getPersonHolidaysInTimespan(array($idUser), $today, $endTime);
		} else {
			$holidays	= array();
		}

		if ($GLOBALS['CONFIG']['EXT']['portal']['tabcontentconfig']['calendar']['showBirthdays']) {
				// Get birthdays within the next X (see calendar/config/extension.php) weeks
//			$amountWeeksToLookToForBirthdays = $GLOBALS['CONFIG']['EXT']['portal']['tabcontentconfig']['calendar']['birthdaysLookAheadWeeks'];
			$endTime	= $today + $amountWeeksToLookToForHolidays * 604800;	// 604800 == 24 * 60 * 60 * 7
			$birthdays	= TodoyuUserManager::getUsersByBirthdayInTimespan($today, $endTime);
		} else {
			$birthdays	= array();
		}

		return TodoyuEventRenderer::renderPortalTabEventsList($events, $holidays, $birthdays);
	}



	/**
	 * Render function for portal calendar tabs
	 * @todo	add functionality
	 *
	 * @return Integer
	 */
	public static function getPortalAppointmentsAmount() {
		$dateStart	= TodoyuTime::getStartOfDay(NOW);
		$dateEnd	= NOW + 2 * 365 * 24 * 3600;

		$events		= TodoyuEventManager::getEventsInTimespan($dateStart, $dateEnd, array(userid()));

		return sizeof($events);
	}



	/**
	 * Get amount of days shown in current calendar mode (day / week / month)
	 */
	public function getAmountOfShownDaysToCalendarMode($mode) {
		switch($mode) {
			case 'day':
				$days = 1;
				break;
			case 'week':
				$days = 7;
				break;
			case 'month':
				$days = 35;
				break;
		}

		return $days;
	}



	/**
	 * Get holidays in a timespan for the current holiday sets
	 *
	 * @param	Integer		$dateStart
	 * @param	Integer		$dateEnd
	 * @return	Array
	 */
	public static function getHolidays($dateStart, $dateEnd) {
		$holidaySets	= self::getSelectedHolidaySets();

		if(sizeof($holidaySets) > 0) {
			$holidays	= TodoyuHolidayManager::getHolidaysInTimespan($dateStart, $dateEnd, $holidaySets);
			$grouped	= TodoyuHolidayManager::groupHolidaysByDays($holidays);
		}

		return $grouped;
	}



	/**
	 * Get amount of days between two week-day numbers (0-6)
	 *
	 * @param	Integer 	$startDay			Timestamp of the startday
	 * @param	Integer 	$endDay				Timestamp of the endday
	 * @param	Boolean		$insideTheSameWeek	If true, the two days are inside the same week
	 * @return	Integer
	 */
	public static function getAmountOfDaysInbetweenWeekdayNums($startDay, $endDay, $insideTheSameWeek = true) {
		if ($insideTheSameWeek) {
				// Both days are within the same week
			$amount = ($endDay == 0 ? 7 : $endDay) - ($startDay == 0 ? 7 : $startDay) + 1;
		} else {
				// Days are not within the same week (spanning over tow or more weeks)
			if($endDay != '') {
				$amount	= $endDay == 0 ? 7 : $endDay;
			} else {
				$amount	= $startDay != '' ? ($startDay == 0 ? 1 : 8 - $startDay) : false;
			}
		}

		return $amount;
	}



	/**
	 * Get various data related to month of given timestamp
	 *
	 * @param	Integer 	$tstamp		UNIX Timestamp of the selected date
	 * @return	Array
	 */
	public static function getMonthData($tstamp) {
		$month					= date('m', $tstamp);
		$year					= date('Y', $tstamp);
		$secondsOfMonth			= TodoyuTime::getDayRange(mktime(0, 0, 0, $month, 1, $year));

		$shownDaysOfLastMonth	= date('w', mktime(0, 0, 0, $month, 1, $year)) - 1;
		$shownDaysOfNextMonth	= 35 - (TodoyuTime::getAmountOfDaysInMonth($tstamp)) - (TodoyuTime::getAmountOfDaysInMonth($tstamp, -1));

		$eventsStart['date']	= $secondsOfMonth['start'] - $shownDaysOfLastMonth * 86400;
		$eventsStart['days']	= TodoyuTime::getAmountOfDaysInMonth($tstamp) + $shownDaysOfLastMonth + $shownDaysOfNextMonth;

		return $eventsStart;
	}



	/**
	 * Get date range for month of the timestamp
	 * (include days of the previous and next month because of the calendar layout)
	 *
	 * @param	Integer		$time
	 * @return	Array
	 */
	public static function getMonthDisplayRange($time) {
		$time		= intval($time);
		$monthRange	= TodoyuTime::getMonthRange($time);

		$start		= TodoyuTime::getWeekStart($monthRange['start']);
		$end		= TodoyuTime::getWeekStart($monthRange['end']) + 604799; //604799 = 7 * 86400 - 1;

		return array(
			'start'	=> $start,
			'end'	=> $end
		);
	}



	/**
	 * Get the period (length) of an event to be displayed as a block of one or several days (week mode)
	 *
	 * @param	Integer 	$startTime			Timestamp of the startdate
	 * @param	Integer 	$endTime			Timestamp of the enddate
	 * @param	Integer 	$tstampSelDay		Timestamp of selected day in calendar widget
	 * @return	Array		Duration infos (TD rendering infos)
	 */
	public static function getEventDurationRenderData($startTime, $endTime, $tstampSelDay) {
		$startDayOfWeek		= TodoyuTime::getWeekdayNum($startTime);
		$startWeekNumber	= TodoyuTime::getWeeknumber($startTime);
		$endDayOfWeek		= TodoyuTime::getWeekdayNum($endTime);
		$endWeekNumber		= TodoyuTime::getWeeknumber($endTime);
		$currentWeekNumber	= TodoyuTime::getWeeknumber($tstampSelDay);

			// If start and end date is in the same week
		if($startWeekNumber == $endWeekNumber) {
			$duration['duration']		= TodoyuCalendarManager::getAmountOfDaysInbetweenWeekdayNums($startDayOfWeek, $endDayOfWeek, true);
			$duration['blankTDBefore']	= $startDayOfWeek - 1;
		} else {
					// Is the current week the first one?
				if($currentWeekNumber == $startWeekNumber) {

					$duration['duration']		= TodoyuCalendarManager::getAmountOfDaysInbetweenWeekdayNums($startDayOfWeek, '', false);
					$duration['blankTDBefore']	= 7 - $duration['duration'];
				}

					// Is the current week the last one?
				if($currentWeekNumber == $endWeekNumber) {
					$duration['duration']		= TodoyuCalendarManager::getAmountOfDaysInbetweenWeekdayNums('', $endDayOfWeek, false);
					$duration['blankTDBefore']	= 0;
				}

					// Is the current week between the first and last one? Show the full week
				if($currentWeekNumber != $startWeekNumber && $currentWeekNumber != $endWeekNumber) {
					$duration['duration']		= 7;
					$duration['blankTDBefore']	= 0;
				}
		}

		return $duration;
	}



	/**
	 * Get timestamps shown in calendar month view (days of month before selected, of the selected and of the month after the selected month)
	 *
	 * Explanation:
	 * As the month view of the calendar displays 5 weeks from monday to sunday, there are always some days
	 * out of the months before and after the selected month being displayed, this function calculates their timestamps.
	 *
	 * @param	Integer	$tstamp		Unix timestamp (selected date)
	 * @return	Array				Timestamps of days to be shown in month view of calendar
	 */
	public static function getShownDaysTimestampsOfMonthView($tstamp) {
		$tstamp		= intval($tstamp);
		$monthSpecs	= self::getMonthSpecs($tstamp);

		$monthNum			= intval($monthSpecs['dateOfCurrentMonth']);
		$nextMonthNum		= intval($monthSpecs['dateOfNextMonth']);
		$yearOfLastMonth   	= $monthSpecs['dateOfCurrentMonth'] == 1 ? $monthSpecs['selectedYear'] - 1 : $monthSpecs['selectedYear'];
		$selectedYear		= $monthSpecs['selectedYear'];

			// Days in month before selected
		$row	= 0;
		for($i = $row; $i <= $monthSpecs['shownDaysOfLastMonth']; $i++) {
			$dayNum			= $monthSpecs['daysOfLastMonth'] - $monthSpecs['shownDaysOfLastMonth'] + $i;
			$tstamps[$i]	= mktime(0, 0, 0, $monthSpecs['dateOfLastMonth'], $dayNum, $yearOfLastMonth);
			$row++;
		}
			// Days in selected month
		$dayNum	= 1;
		for($i = $row; $dayNum <= $monthSpecs['daysOfMonth']; $i++) {
			$tstamps[$i]		= mktime(0, 0, 0, $monthNum, $dayNum, $selectedYear);
			$row++;
			$dayNum++;
		}
			// Days in month after selected
		$dayNum	= 1;
		$addDays = count($tstamps) > 36 ? 7 : 0;
		for($i = $row; $dayNum <= $monthSpecs['shownDaysOfNextMonth'] + $addDays; $i++) {
			$yearOfNextMonth	= $monthSpecs['dateOfCurrentMonth'] == 12 ? $monthSpecs['selectedYear'] + 1 : $monthSpecs['selectedYear'];
			$tstamps[$i]		= mktime(0, 0, 0, $nextMonthNum, $dayNum, $yearOfNextMonth);
			$row++;
			$dayNum++;
		}

		return $tstamps;
	}



	/**
	 * Precalculate various time data (from prev., selected and next month) for calendar month view
	 *
	 * @param	Integer	$tstamp		UNIX Timestamp (selected date in calendar panel widget)
	 * @return	Array	data		precalculated attributes of prev., selected and next month to be rendered
	 */
	public static function getMonthSpecs($tstamp) {
		$tstamp	= intval($tstamp);
		$month	= date('m', $tstamp);
		$year	= date('Y', $tstamp);

		$data	= array(
			'daysOfMonth'			=> TodoyuTime::getAmountOfDaysInMonth($tstamp),
			'daysOfLastMonth'		=> TodoyuTime::getAmountOfDaysInMonth($tstamp, -1),
			'numericDayOfWeek'		=> date('w', mktime(0, 0, 0, $month, 1, $year)),
			'dateOfLastMonth'		=> $month == 1 ? 12 : $month - 1,
			'dateOfCurrentMonth'	=> $month,
			'dateOfNextMonth'		=> date('m', strtotime('+1 month', $tstamp)),
			'selectedYear'			=> $year,
		);

		$data['shownDaysOfLastMonth'] = $data['numericDayOfWeek'] - 1;
		$data['shownDaysOfNextMonth'] = 35 - $data['daysOfMonth'] - $data['shownDaysOfLastMonth'];

		return $data;
	}



	/**
	 * 	Get context menu items
	 *
	 *	@param	unknown_type	$time
	 *	@param	Array	$items
	 *	@return	Array
	 */
	public static function getContextMenuItems($time, array $items) {
		$items = array_merge_recursive($items, $GLOBALS['CONFIG']['EXT']['calendar']['ContextMenu']['Area']);

		return $items;
	}



	/**
	 * Get calendar tabs configuration array
	 *
	 * @return	Array
	 */
	public static function getCalendarTabsConfig() {
		$tabs	= $GLOBALS['CONFIG']['EXT']['calendar']['contentTabs'];

		foreach($tabs as $index => $tab) {
			$tabs[$index]['htmlId'] 	= 'calendar-tabhead-' . $tab['id'];
			$tabs[$index]['key']		= $tab['id'];
			$tabs[$index]['classKey'] 	= $tab['id'];
			$tabs[$index]['hasIcon'] 	= 1;
		}

		return $tabs;
	}



	/**
	 * Get currently selected users (defined by the panel widget)
	 * If no user is selected, the current user will automaticly be selected
	 *
	 * @return	Array
	 */
	public static function getSelectedUsers() {
		$users	= TodoyuPanelWidgetStaffSelector::getSelectedUsers();

		if(sizeof($users) === 0) {
			$users = array(userid());
		}

		return $users;
	}



	/**
	 * Get currently selected event types
	 *
	 * @return	Array
	 */
	public static function getSelectedEventTypes() {
		return TodoyuPanelWidgetEventTypeSelector::getSelectedEventTypes();
	}



	/**
	 * Get currently selected holiday sets
	 *
	 * @return	Array
	 */
	public static function getSelectedHolidaySets() {
		return TodoyuPanelWidgetHolidaySetSelector::getSelectedHolidaySetIDs();
	}



	/**
	 * Extend company address form (hooked into contact's form building)
	 *
	 * @param	TodoyuForm		$form			Task edit form object
	 * @param	Integer		$idTask			Task ID
	 * @return	TodoyuForm		Moddified form object
	 */
	public static function modifyAddressFormfields(TodoyuForm $form, $addressIndex) {
		$addressIndex	= intval($addressIndex);
		$contactType	= TodoyuContactPreferences::getActiveTab();

		if ($contactType == 'company') {
				// Extend company record form with holiday set selector
			$form->addElementsFromXML('ext/calendar/config/form/addressholidayset.xml');
		}

		return $form;
	}

}

?>