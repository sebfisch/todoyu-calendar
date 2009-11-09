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
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.calendar.PanelWidget.Calendar.init.bind(Todoyu.Ext.calendar.PanelWidget.Calendar, ' . $this->getDate($idArea) . ')');
	}



	/**
	 * Add calendar inline JS (initialization)
	 *
	 */
	private function addCalendarLocalizationJS() {
		$code = "// Localize date object (for scal)\n"
				. 'Object.extend(Date.prototype, {'
				. 'monthnames:[\'' . Label('core.month.january') . '\',\'' . Label('core.month.february') . '\',\'' . Label('core.month.march') . '\',\'' . Label('core.month.april') . '\',\'' . Label('core.month.may') . "','" . Label('core.month.june') . "','" . Label('core.month.july') . '\',\'' . Label('core.month.august') . "','" . Label('core.month.september') . "','" . Label('core.month.october') . "','" . Label('core.month.november') . "','" . Label('core.month.december') . '\'],'
				. 'daynames:[\'' . Label('core.weekday.monday') . '\', \'' . Label('core.weekday.tuesday') . '\',\'' . Label('core.weekday.wednesday') . "','" . Label('core.weekday.thursday') . "','" . Label('core.weekday.friday') . "','" . Label('core.weekday.saturday') . "','" . Label('core.weekday.sunday') . '\']'
				. '});';

		TodoyuPage::addJsInlines($code);
	}



	/**
	 * Render content
	 * NOTE:	the calender HTML content itself is written to the DOM via JS by the sCal library!
	 *
	 * @return String
	 */
	public function renderContent() {
		$tmpl	= 'ext/calendar/view/panelwidgets/panelwidget-calendar.tmpl';
		$data	= array(
			'id'		=> 'widgetCalendar-' . TodoyuRequest::getParam('ext'),
			'class'		=> $this->config['class'],
			'date'		=> $this->getDate()
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
	public static function getDate($idArea = AREA) {
		return TodoyuCalendarPreferences::getDate($idArea);
	}



	/**
	 * Save calendar date for area
	 *
	 * @param	Integer		$time
	 * @param	Integer		$idArea
	 */
	public static function saveDate($time, $idArea = AREA) {
		$time	= intval($time);

		TodoyuCalendarPreferences::saveDate($time, $idArea);
	}

}


?>