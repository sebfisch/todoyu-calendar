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
 * Preference action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

class TodoyuCalendarPreferenceActionController extends TodoyuActionController {

	protected $value	= '';

	protected $item		= 0;



	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function init(array $params) {
		$this->value	= $params['value'];
		$this->item		= intval($params['item']);
	}



	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function tabAction(array $params) {
		$tabKey	= trim($params['tab']);

		TodoyuCalendarPreferences::saveActiveTab($tabKey);
	}



	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function dateAction(array $params) {
		$date	= intval($this->value);

		TodoyuCalendarPreferences::saveDate($date, AREA);
	}



	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function panelwidgeteventtypeselectorAction(array $params) {
		$eventTypes	= TodoyuDiv::intExplode(',', $this->value, true, true);

		TodoyuCalendarPreferences::saveEventTypes($eventTypes);
	}



	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function panelwidgetholidaysetselectorAction(array $params) {
		$holidaySets	= TodoyuDiv::intExplode(',', $this->value, true, false);

		TodoyuCalendarPreferences::saveHolidaysets($holidaySets);
	}



	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function fulldayviewAction(array $params) {
		$fullDay	= intval($this->value) === 1;

		TodoyuCalendarPreferences::saveFullDayView($fullDay);
	}



	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function pwidgetAction(array $params) {
		TodoyuPanelWidgetManager::saveCollapsedStatus(EXTID_CALENDAR, $this->item, $this->value);
	}


}

?>