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
 * Panel widget: calendar
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

class TodoyuPanelWidgetCalendar extends TodoyuPanelWidget implements TodoyuPanelWidgetIf {

	/**
	 * Preference name
	 */
	const PREF = 'panelwidget-calendar';



	/**
	 * Constructor of PanelWidgetCalendar (initialize widget)
	 */
	public function __construct(array $config, array $params = array(), $idArea = 0) {
		parent::__construct(
			'calendar',							// ext. key
			'calendar',							// panel widget ID
			'LLL:panelwidget-calendar.title',	// widget title text
			$config,							// widget config array
			$params,							// widget params
			$idArea								// area ID
		);

			// Have public ext. and widget specific assets added
		TodoyuPage::addExtAssets('calendar', 'public');
		TodoyuPage::addExtAssets('calendar', 'panelwidget-calendar');

		$this->addHasIconClass();

			// Init widget JS (observers)
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.calendar.PanelWidget.Calendar.init.bind(Todoyu.Ext.calendar.PanelWidget.Calendar, ' . $this->getDate($idArea) . ')', 100);
	}



	/**
	 * Add calendar inline JS (initialization)
	 *
	 */
	private function addCalendarLocalizationJS() {
		$code = "// Localize date object (for scal)\n"
				. 'Object.extend(Date.prototype, {'
				. 'monthnames:[\'' . Label('date.month.january') . '\',\'' . Label('date.month.february') . '\',\'' . Label('date.month.march') . '\',\'' . Label('date.month.april') . '\',\'' . Label('date.month.may') . "','" . Label('date.month.june') . "','" . Label('date.month.july') . '\',\'' . Label('date.month.august') . "','" . Label('date.month.september') . "','" . Label('date.month.october') . "','" . Label('date.month.november') . "','" . Label('date.month.december') . '\'],'
				. 'daynames:[\'' . Label('date.weekday.sunday') . '\',\'' . Label('date.weekday.monday') . '\', \'' . Label('date.weekday.tuesday') . '\',\'' . Label('date.weekday.wednesday') . "','" . Label('date.weekday.thursday') . "','" . Label('date.weekday.friday') . "','" . Label('date.weekday.saturday') . '\']'
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
			'id'			=> $this->getID(), // 'panel-' . TodoyuRequest::getParam('ext'),
			'class'			=> $this->config['class'],
			'date'			=> $date,
			'dateDay'		=> date('d', $date),
			'dateMonth'		=> date('n', $date),
			'dateYear'		=> date('Y', $date),
			'daysInMonth'	=> date('t', $date)
		);

		$content	= render($tmpl, $data);

		$this->setContent($content);

		return $content;
	}



	/**
	 * Render widget
	 *
	 * @return	String
	 */
	public function render() {
		self::addCalendarLocalizationJS();

		$this->renderContent();

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
	 * Check if panelwidget is allowed
	 *
	 * @return	Bool
	 */
	public static function isAllowed() {
		return allowed('calendar', 'general:use');
	}

}


?>