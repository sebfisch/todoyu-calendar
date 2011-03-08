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
	 */
	public function __construct(array $config, array $params = array(), $idArea = 0) {
		parent::__construct(
			'calendar',							// ext. key
			'calendar',							// panel widget ID
			'LLL:calendar.panelwidget-calendar.title',	// widget title text
			$config,							// widget config array
			$params,							// widget parameters
			$idArea								// area ID
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
				. 'monthnames:[\'' . Label('core.date.month.january') . '\',\'' . Label('core.date.month.february') . '\',\'' . Label('core.date.month.march') . '\',\'' . Label('core.date.month.april') . '\',\'' . Label('core.date.month.may') . "','" . Label('core.date.month.june') . "','" . Label('core.date.month.july') . '\',\'' . Label('core.date.month.august') . "','" . Label('core.date.month.september') . "','" . Label('core.date.month.october') . "','" . Label('core.date.month.november') . "','" . Label('core.date.month.december') . '\'],'
				. 'daynames:[\'' . Label('core.date.weekday.sunday') . '\',\'' . Label('core.date.weekday.monday') . '\', \'' . Label('core.date.weekday.tuesday') . '\',\'' . Label('core.date.weekday.wednesday') . "','" . Label('core.date.weekday.thursday') . "','" . Label('core.date.weekday.friday') . "','" . Label('core.date.weekday.saturday') . '\']'
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

		return render($tmpl, $data);
	}



	/**
	 * Render widget
	 *
	 * @return	String
	 */
	public function render() {
		self::addCalendarLocalizationJS();

		return parent::render();
	}



	/**
	 * Get date for area
	 *
	 * @return	Integer
	 */
	public static function getDate() {
		return TodoyuCalendarPreferences::getDate(AREA);
	}



	/**
	 * Save calendar date for area
	 *
	 * @param	Integer		$time
	 * @param	Integer		$idArea
	 */
	public static function saveDate($time) {
		$time	= intval($time);

		TodoyuCalendarPreferences::saveDate($time, AREA);
	}



	/**
	 * Check panelWidget access permission
	 *
	 * @return	Boolean
	 */
	public static function isAllowed() {
		return allowed('calendar', 'general:use');
	}

}

?>