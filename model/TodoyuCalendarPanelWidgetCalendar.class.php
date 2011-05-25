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
 * Panel widget: calendar
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarPanelWidgetCalendar extends TodoyuPanelWidget {

	/**
	 * @var string		Preference name
	 */
	const PREF = 'panelwidget-calendar';



	/**
	 * Constructor of PanelWidgetCalendar (initialize widget)
	 *
	 * @param	Array	$config
	 * @param	Array	$params
	 */
	public function __construct(array $config, array $params = array()) {
		parent::__construct(
			'calendar',									// ext. key
			'calendar',									// panel widget ID
			'LLL:calendar.panelwidget-calendar.title',	// widget title text
			$config,									// widget config array
			$params										// widget parameters
		);

		$this->addHasIconClass();

			// Init widget JS (observers)
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.calendar.PanelWidget.Calendar.init.bind(Todoyu.Ext.calendar.PanelWidget.Calendar, \'' . date('Y-m-d', $this->getDate()) . '\')', 100);
	}



	/**
	 * Add calendar inline JS (initialization)
	 */
	private static function addCalendarLocalizationJS() {
		$code = "// Localize date object (for scal)\n"
				. 'Object.extend(Date.prototype, {'
				. 'monthnames:[\'' . Todoyu::Label('core.date.month.january') . '\',\'' . Todoyu::Label('core.date.month.february') . '\',\'' . Todoyu::Label('core.date.month.march') . '\',\'' . Todoyu::Label('core.date.month.april') . '\',\'' . Todoyu::Label('core.date.month.may') . "','" . Todoyu::Label('core.date.month.june') . "','" . Todoyu::Label('core.date.month.july') . '\',\'' . Todoyu::Label('core.date.month.august') . "','" . Todoyu::Label('core.date.month.september') . "','" . Todoyu::Label('core.date.month.october') . "','" . Todoyu::Label('core.date.month.november') . "','" . Todoyu::Label('core.date.month.december') . '\'],'
				. 'daynames:[\'' . Todoyu::Label('core.date.weekday.sunday') . '\',\'' . Todoyu::Label('core.date.weekday.monday') . '\', \'' . Todoyu::Label('core.date.weekday.tuesday') . '\',\'' . Todoyu::Label('core.date.weekday.wednesday') . "','" . Todoyu::Label('core.date.weekday.thursday') . "','" . Todoyu::Label('core.date.weekday.friday') . "','" . Todoyu::Label('core.date.weekday.saturday') . '\']'
				. '});';

		TodoyuPage::addJsInline($code);
	}



	/**
	 * Render content
	 * NOTE:	the calender HTML content itself is written to the DOM via JS by the sCal library!
	 *
	 * @return String
	 */
	public function renderContent() {
		$date	= $this->getDate();

		$tmpl	= 'ext/calendar/view/panelwidgets/panelwidget-calendar.tmpl';
		$data	= array(
			'id'			=> $this->getID(),
			'class'			=> $this->config['class'],
			'date'			=> $date,
			'dateDay'		=> date('d', $date),
			'dateMonth'		=> date('n', $date),
			'dateYear'		=> date('Y', $date),
			'daysInMonth'	=> date('t', $date)
		);

		return Todoyu::render($tmpl, $data);
	}



	/**
	 * Render widget
	 *
	 * @return	String
	 */
	public function render() {
		$this->addCalendarLocalizationJS();

		return parent::render();
	}



	/**
	 * Get date for area
	 *
	 * @return	Integer
	 */
	public function getDate() {
		return TodoyuCalendarPreferences::getDate(AREA);
	}



	/**
	 * Save calendar date for area
	 *
	 * @param	Integer		$time
	 */
	public function saveDate($time) {
		$time	= intval($time);

		TodoyuCalendarPreferences::saveDate($time, AREA);
	}



	/**
	 * Check panelWidget access permission
	 *
	 * @return	Boolean
	 */
	public static function isAllowed() {
		return Todoyu::allowed('calendar', 'general:use');
	}

}

?>