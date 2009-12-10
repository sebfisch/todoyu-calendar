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

	/**
	 * Preference value
	 *
	 * @var	String
	 */
	protected $value	= '';

	/**
	 * Preference item
	 *
	 * @var	Integer
	 */
	protected $item		= 0;



	/**
	 *	Init
	 *
	 *	@param	Array	$params
	 */
	public function init(array $params) {
		restrict('calendar', 'use');

		$this->value	= $params['value'];
		$this->item		= intval($params['item']);
	}



	/**
	 * Save current active tab
	 *
	 * @param	Array		$params
	 */
	public function tabAction(array $params) {
		$tabKey	= trim($params['tab']);

		TodoyuCalendarPreferences::saveActiveTab($tabKey);
	}



	/**
	 * Save current date
	 *
	 * @param	Array		$params
	 */
	public function dateAction(array $params) {
		$date	= intval($this->value);

		TodoyuCalendarPreferences::saveDate($date, AREA);
	}



	/**
	 *	Saves eventTypeSelector widget preferences (selected event types)
	 *
	 *	@param	Array	$params
	 */
	public function panelwidgeteventtypeselectorAction(array $params) {
		$eventTypes	= TodoyuDiv::intExplode(',', $this->value, true, true);

		TodoyuCalendarPreferences::saveEventTypes($eventTypes);
	}



	/**
	 *	Saves HolidaySetSelector widget preferences (selected holidaySets)
	 *
	 *	@param	Array	$params
	 */
	public function panelwidgetholidaysetselectorAction(array $params) {
		$holidaySets	= TodoyuDiv::intExplode(',', $this->value, true, false);

		TodoyuCalendarPreferences::saveHolidaySets($holidaySets);
	}



	/**
	 *	'fulldayview' action method, saves viewing mode (full / half) day
	 *
	 *	@param	Array	$params
	 */
	public function fulldayviewAction(array $params) {
		$fullDay	= intval($this->value) === 1;

		TodoyuCalendarPreferences::saveFullDayView($fullDay);
	}



	/**
	 *	General panelWidget action, saves collapse status
	 *
	 *	@param	Array	$params
	 */
	public function pwidgetAction(array $params) {
		$idWidget	= $params['item'];
		$value		= $params['value'];

		TodoyuPanelWidgetManager::saveCollapsedStatus(EXTID_CALENDAR, $idWidget, $value);
	}

}

?>